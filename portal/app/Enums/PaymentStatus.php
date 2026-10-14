<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Payment lifecycle. Online: created → successful/failed (set only after server-side gateway verification). Offline: submitted → verified/cancelled.
 */
enum PaymentStatus: string
{
    use HasLabel;

    case Created = 'created';
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
            self::Created, self::Refunded, self::Cancelled => 'neutral',
            self::Pending, self::Submitted => 'warning',
            self::Successful, self::Verified => 'success',
            self::Failed => 'danger',
        };
    }
}
