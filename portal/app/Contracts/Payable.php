<?php

namespace App\Contracts;

use App\Models\Payment;

/**
 * Something that can be paid for (lifetime membership, donation …).
 * PaymentService calls these hooks inside the same DB transaction that
 * changes the payment's status, so both always stay consistent.
 */
interface Payable
{
    /** Payment confirmed: gateway-verified (online) or admin-verified (offline). */
    public function paymentSucceeded(Payment $payment): void;

    /** Offline payment details submitted, waiting for admin verification. */
    public function paymentSubmitted(Payment $payment): void;

    /** Payment failed, was rejected by admin, or was cancelled. */
    public function paymentFailed(Payment $payment): void;

    /** Short English description used on receipts / admin lists. */
    public function paymentDescription(): string;
}
