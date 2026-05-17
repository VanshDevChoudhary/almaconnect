<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\VerifyDonationRequest;
use App\Jobs\SendDonationReceipt;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Services\RazorpayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function __construct(private RazorpayService $razorpay)
    {
    }

    private function campaignCard(DonationCampaign $c): array
    {
        return [
            'id' => $c->id,
            'slug' => $c->slug,
            'title' => $c->title,
            'description' => $c->description,
            'cover_image' => $c->cover_image,
            'target_amount' => (int) $c->target_amount,
            'raised_amount' => (int) $c->raised_amount,
            'donor_count' => $c->donations()->where('status', 'success')->distinct('user_id')->count('user_id'),
            'ends_at' => $c->ends_at?->toDateString(),
        ];
    }

    public function index(): Response
    {
        $campaigns = DonationCampaign::where('is_active', true)
            ->latest()
            ->get();

        $cards = $campaigns->map(fn ($c) => $this->campaignCard($c));

        return Inertia::render('Donate/Index', [
            'featured' => $cards->first(),
            'campaigns' => $cards->slice(1)->values(),
        ]);
    }

    public function show(string $slug): Response
    {
        $campaign = DonationCampaign::where('slug', $slug)->firstOrFail();

        $recent = $campaign->donations()
            ->where('status', 'success')
            ->with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn (Donation $d) => [
                'name' => $d->is_anonymous ? 'Anonymous Donor' : ($d->user?->name ?? 'Donor'),
                'amount' => (int) $d->amount,
                'date' => $d->created_at->toIso8601String(),
            ]);

        return Inertia::render('Donate/Show', [
            'campaign' => $this->campaignCard($campaign),
            'recentDonors' => $recent,
        ]);
    }

    public function createOrder(CreateOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['campaign_id'])) {
            $campaign = DonationCampaign::find($data['campaign_id']);
            if (! $campaign || ! $campaign->is_active) {
                return response()->json(['error' => 'Campaign is not accepting donations.'], 422);
            }
        }

        try {
            $donation = DB::transaction(function () use ($data, $request) {
                $donation = Donation::create([
                    'user_id' => $request->user()->id,
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => 'INR',
                    'razorpay_order_id' => 'pending_'.uniqid(),
                    'status' => 'pending',
                    'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
                ]);

                $order = $this->razorpay->createOrder(
                    (int) $data['amount'] * 100,
                    'don_'.$donation->id,
                    ['donation_id' => $donation->id, 'user_id' => $request->user()->id],
                );

                $donation->update(['razorpay_order_id' => $order['id']]);

                return $donation;
            });
        } catch (\Throwable $e) {
            Log::error('Razorpay order creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Could not start the payment. Please try again later.',
            ], 502);
        }

        return response()->json([
            'order_id' => $donation->razorpay_order_id,
            'amount' => (int) $donation->amount,
            'key_id' => config('services.razorpay.key'),
            'donation_id' => $donation->id,
        ]);
    }

    public function verify(VerifyDonationRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $this->razorpay->verifyPaymentSignature(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
        )) {
            return response()->json(['success' => false, 'error' => 'Invalid signature'], 400);
        }

        $donation = Donation::where('id', $data['donation_id'])
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->firstOrFail();

        if ($donation->status === 'success') {
            return response()->json(['success' => true, 'already_processed' => true]);
        }

        $this->markSuccessful($donation, $data['razorpay_payment_id'], $data['razorpay_signature']);

        return response()->json(['success' => true]);
    }

    /**
     * Atomic, race-safe success transition. Only the call that flips the
     * status away from non-success increments the campaign total.
     */
    public static function markSuccessful(Donation $donation, string $paymentId, ?string $signature = null): bool
    {
        $flipped = false;

        DB::transaction(function () use ($donation, $paymentId, $signature, &$flipped) {
            $affected = Donation::where('id', $donation->id)
                ->where('status', '!=', 'success')
                ->update([
                    'status' => 'success',
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                ]);

            if ($affected > 0) {
                $flipped = true;
                if ($donation->campaign_id) {
                    DonationCampaign::where('id', $donation->campaign_id)
                        ->increment('raised_amount', (int) $donation->amount);
                }
            }
        });

        if ($flipped) {
            SendDonationReceipt::dispatch($donation->fresh());
        }

        return $flipped;
    }

    public function success(Request $request, Donation $donation): Response
    {
        abort_unless(
            $donation->user_id === $request->user()->id || $request->user()->isAdmin(),
            403,
        );
        $donation->load('campaign');

        return Inertia::render('Donate/Success', [
            'donation' => [
                'id' => $donation->id,
                'amount' => (int) $donation->amount,
                'status' => $donation->status,
                'donor_name' => $request->user()->name,
                'email' => $request->user()->email,
                'campaign_title' => $donation->campaign?->title ?? 'the institute',
            ],
        ]);
    }

    public function downloadReceipt(Request $request, Donation $donation): SymfonyResponse
    {
        abort_unless(
            $donation->user_id === $request->user()->id || $request->user()->isAdmin(),
            403,
        );
        abort_unless($donation->status === 'success', 404);

        $donation->loadMissing('user', 'campaign');
        $pdf = Pdf::loadView('pdfs.donation-receipt', ['donation' => $donation]);

        return $pdf->download('donation-receipt-'.$donation->id.'.pdf');
    }
}
