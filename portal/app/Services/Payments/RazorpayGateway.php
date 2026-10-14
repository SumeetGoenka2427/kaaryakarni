<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Razorpay\Api\Api;
use RuntimeException;

/** Razorpay Orders + Checkout + Webhooks (UPI, cards, net-banking, wallets). */
class RazorpayGateway implements PaymentGateway
{
    public function __construct(private readonly array $config) {}

    public function name(): string
    {
        return 'razorpay';
    }

    public function isConfigured(): bool
    {
        return filled($this->config['key_id']) && filled($this->config['key_secret']);
    }

    public function createOrder(Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Razorpay is not configured.');
        }

        $paise = (int) round((float) $payment->amount * 100);
        $order = $this->api()->order->create([
            'amount' => $paise,
            'currency' => $payment->currency,
            'receipt' => $payment->ulid,
            'notes' => ['payment_ulid' => $payment->ulid, 'purpose' => $payment->purpose->value],
        ]);

        return [
            'order_id' => $order['id'],
            'checkout' => [
                'key' => $this->config['key_id'],
                'amount' => $paise,
                'currency' => $payment->currency,
                'order_id' => $order['id'],
            ],
        ];
    }

    public function verifyCheckout(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, (string) $this->config['key_secret']);

        return $signature !== '' && hash_equals($expected, $signature);
    }

    public function verifyWebhook(string $body, string $signature): bool
    {
        $secret = (string) $this->config['webhook_secret'];
        if ($secret === '' || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $body, $secret), $signature);
    }

    private function api(): Api
    {
        return new Api($this->config['key_id'], $this->config['key_secret']);
    }
}
