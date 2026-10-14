<?php

namespace Tests\Support;

use App\Models\Payment;
use App\Services\Payments\PaymentGateway;

/** Test gateway with real HMAC checks (known secrets) and no network calls. */
class FakePaymentGateway implements PaymentGateway
{
    public const SECRET = 'test_key_secret';

    public const WEBHOOK_SECRET = 'test_webhook_secret';

    public function name(): string
    {
        return 'razorpay';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createOrder(Payment $payment): array
    {
        $orderId = 'order_'.substr($payment->ulid, -10);

        return ['order_id' => $orderId, 'checkout' => ['key' => 'rzp_test', 'amount' => (int) round((float) $payment->amount * 100), 'currency' => 'INR', 'order_id' => $orderId]];
    }

    public function verifyCheckout(string $orderId, string $paymentId, string $signature): bool
    {
        return hash_equals(self::sign($orderId, $paymentId), $signature);
    }

    public function verifyWebhook(string $body, string $signature): bool
    {
        return hash_equals(hash_hmac('sha256', $body, self::WEBHOOK_SECRET), $signature);
    }

    public static function sign(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId.'|'.$paymentId, self::SECRET);
    }
}
