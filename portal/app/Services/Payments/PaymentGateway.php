<?php

namespace App\Services\Payments;

use App\Models\Payment;

/**
 * An online payment gateway. Razorpay is the first implementation; another
 * provider can be added by implementing this interface and binding it in
 * AppServiceProvider — nothing else changes (CLAUDE.md §18).
 */
interface PaymentGateway
{
    public function name(): string;

    /** False when keys are missing: online payment is then shown as unavailable (never faked). */
    public function isConfigured(): bool;

    /**
     * Create a gateway order for the payment.
     *
     * @return array{order_id: string, checkout: array<string, mixed>} data for the browser checkout widget
     */
    public function createOrder(Payment $payment): array;

    /** Verify the signature the checkout widget returned (server-side, HMAC). */
    public function verifyCheckout(string $orderId, string $paymentId, string $signature): bool;

    /** Verify a webhook request body against its signature header (server-side, HMAC). */
    public function verifyWebhook(string $body, string $signature): bool;
}
