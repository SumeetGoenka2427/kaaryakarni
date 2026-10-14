<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Matrimonial profile state. Only "approved" is visible, and only to eligible logged-in members.
 */
enum MatrimonialStatus: string
{
    use HasLabel;

    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Hidden = 'hidden';
    case Suspended = 'suspended';

    /** Pill colour variant used by <x-pill> (pill-success, pill-warning, ...). */
    public function pill(): string
    {
        return match ($this) {
            self::Draft, self::Hidden => 'neutral',
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected, self::Suspended => 'danger',
        };
    }
}
