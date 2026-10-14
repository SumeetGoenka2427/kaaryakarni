<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Business directory listing state. Only "approved" is public.
 */
enum BusinessStatus: string
{
    use HasLabel;

    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ChangesRequested = 'changes_requested';
    case Hidden = 'hidden';

    /** Pill colour variant used by <x-pill> (pill-success, pill-warning, ...). */
    public function pill(): string
    {
        return match ($this) {
            self::Draft, self::Hidden => 'neutral',
            self::Pending, self::ChangesRequested => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
