<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use League\Csv\Writer;

class DonationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status');
        $campaignId = $request->input('campaign_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Donation::with(['user', 'campaign'])->latest();

        if ($status && in_array($status, ['pending', 'success', 'failed', 'refunded'], true)) {
            $query->where('status', $status);
        }
        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $paginator = $query->paginate(50)->withQueryString();
        $paginator->through(fn (Donation $d) => [
            'id' => $d->id,
            // Admins always see the real donor, even for anonymous gifts.
            'donor' => $d->user?->name ?? 'Deleted user',
            'is_anonymous' => $d->is_anonymous,
            'amount' => (int) $d->amount,
            'status' => $d->status,
            'campaign' => $d->campaign?->title ?? 'General Fund',
            'created_at' => $d->created_at->toIso8601String(),
            'has_receipt' => (bool) $d->receipt_path && $d->status === 'success',
        ]);

        return Inertia::render('Admin/Donations/Index', [
            'donations' => $paginator,
            'campaigns' => DonationCampaign::orderBy('title')->get(['id', 'title']),
            'filters' => [
                'status' => $status ?: 'all',
                'campaign_id' => $campaignId,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return response()->streamDownload(function () {
            $csv = Writer::createFromStream(fopen('php://output', 'w'));
            $csv->insertOne(['id', 'donor_name', 'donor_email', 'amount', 'currency', 'campaign', 'status', 'payment_id', 'anonymous', 'created_at']);

            Donation::with('user', 'campaign')->cursor()->each(function (Donation $d) use ($csv) {
                $csv->insertOne([
                    $d->id,
                    $d->user?->name ?? 'Deleted user',
                    $d->user?->email ?? '',
                    (int) $d->amount,
                    $d->currency,
                    $d->campaign?->title ?? 'General Fund',
                    $d->status,
                    $d->razorpay_payment_id ?? '',
                    $d->is_anonymous ? '1' : '0',
                    $d->created_at->toIso8601String(),
                ]);
            });
        }, 'donations.csv', ['Content-Type' => 'text/csv']);
    }
}
