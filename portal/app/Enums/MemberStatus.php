<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Member account / profile verification state (separate from membership payment state).
 */
enum MemberStatus: string
{
    use HasLabel;

    case Pending = 'pending';
    case UnderVerification = 'under_verification';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    /** Pill colour variant used by <x-pill> (pill-success, pill-warning, ...). */
    public function pill(): string
    {
        return match ($this) {
            self::Pending => 'neutral',
            self::UnderVerification => 'warning',
            self::Verified => 'success',
            self::Rejected, self::Suspended => 'danger',
        };
    }
}
