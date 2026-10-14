<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

/**
 * Translated labels for backed enums.
 *
 * Labels come from lang/{en,hi}/enums.php under a key derived from the enum
 * class name (MembershipStatus → enums.membership_status.active).
 */
trait HasLabel
{
    public function label(): string
    {
        return __('enums.'.Str::snake(class_basename(static::class)).'.'.$this->value);
    }

    /** value => label pairs for <select> options. */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
