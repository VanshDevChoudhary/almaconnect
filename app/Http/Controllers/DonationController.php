<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Jobs\SendDonationReceipt;
use App\Models\Donation;
use App\Models\DonationCampaign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
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
        $campaigns = DonationCampaign::where('is_active', true)->latest()->get();
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

    public function pay(CreateOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['campaign_id'])) {
            $campaign = DonationCampaign::find($data['campaign_id']);
            if (! $campaign || ! $campaign->is_active) {
                return response()->json(['error' => 'Campaign is not accepting donations.'], 422);
            }
        }

        $donation = DB::transaction(function () use ($data, $request) {
            $donation = Donation::create([
                'user_id'      => $request->user()->id,
                'campaign_id'  => $data['campaign_id'] ?? null,
                'amount'       => $data['amount'],
                'currency'     => 'INR',
                'status'       => 'success',
                'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
            ]);

            if ($donation->campaign_id) {
                DonationCampaign::where('id', $donation->campaign_id)
                    ->increment('raised_amount', (int) $donation->amount);
            }

            return $donation;
        });

        SendDonationReceipt::dispatch($donation->fresh());

        return response()->json([
            'success'     => true,
            'donation_id' => $donation->id,
        ]);
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
                'id'             => $donation->id,
                'amount'         => (int) $donation->amount,
                'status'         => $donation->status,
                'donor_name'     => $request->user()->name,
                'email'          => $request->user()->email,
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
