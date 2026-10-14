<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Lifetime membership state. There is deliberately no expired/renewal state: once active, membership is lifetime unless an admin suspends it.
 */
enum MembershipStatus: string
{
    use HasLabel;

    case Pending = 'pending';
    case PaymentPending = 'payment_pending';
    case PaymentSubmitted = 'payment_submitted';
    case UnderVerification = 'under_verification';
    case Active = 'active';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    /** Pill colour variant used by <x-pill> (pill-success, pill-warning, ...). */
    public function pill(): string
    {
        return match ($this) {
            self::Pending => 'neutral',
            self::PaymentPending, self::PaymentSubmitted, self::UnderVerification => 'warning',
            self::Active => 'success',
            self::Rejected, self::Suspended => 'danger',
        };
    }
}
