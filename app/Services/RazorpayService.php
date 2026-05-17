<?php

namespace App\Services;

use Razorpay\Api\Api;

class RazorpayService
{
    private function api(): Api
    {
        return new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret'),
        );
    }

    /**
     * @param  array<string, mixed>  $notes
     * @return array{id: string, amount: int, currency: string}
     */
    public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
    {
        $order = $this->api()->order->create([
            'amount' => $amountPaise,
            'currency' => 'INR',
            'receipt' => $receipt,
            'payment_capture' => 1,
            'notes' => $notes,
        ]);

        return [
            'id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
        ];
    }

    /**
     * Timing-safe verification of the checkout callback signature.
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac(
            'sha256',
            $orderId.'|'.$paymentId,
            (string) config('services.razorpay.secret'),
        );

        return hash_equals($expected, $signature);
    }

    /**
     * Timing-safe verification of an inbound webhook payload.
     */
    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if ($signature === null) {
            return false;
        }

        $expected = hash_hmac(
            'sha256',
            $payload,
            (string) config('services.razorpay.webhook_secret'),
        );

        return hash_equals($expected, $signature);
    }
}
