<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DonationController;
use App\Models\Donation;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    public function __construct(private RazorpayService $razorpay)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (! $this->razorpay->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['ok' => false], 400);
        }

        $data = json_decode($payload, true) ?: [];
        $event = $data['event'] ?? null;
        $entity = $data['payload']['payment']['entity'] ?? [];
        $orderId = $entity['order_id'] ?? null;

        if (! $orderId) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        if ($event === 'payment.captured') {
            $donation = Donation::where('razorpay_order_id', $orderId)->first();
            if ($donation && $donation->status !== 'success') {
                DonationController::markSuccessful($donation, $entity['id'] ?? 'webhook');
            }
        } elseif ($event === 'payment.failed') {
            Donation::where('razorpay_order_id', $orderId)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        } else {
            Log::info('Razorpay webhook ignored', ['event' => $event]);
        }

        return response()->json(['ok' => true]);
    }
}
