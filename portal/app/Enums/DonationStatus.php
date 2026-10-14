<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Donation state, mirrors its payment.
 */
enum DonationStatus: string
{
    use HasLabel;

    case Pending = 'pending';
    case Submitted = 'submitted';
    case Successful = 'successful';
    case Verified = 'verified';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    /** Pill colour variant used by <x-pill> (pill-success, pill-warning, ...). */
    public function pill(): string
    {
        return match ($this) {
            self::Pending, self::Submitted => 'warning',
            self::Successful, self::Verified => 'success',
            self::Failed => 'danger',
            self::Refunded, self::Cancelled => 'neutral',
        };
    }
}
