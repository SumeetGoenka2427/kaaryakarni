<?php

namespace App\Models\Concerns;

use App\Services\Content\PublicContent;

/** Any change to this model refreshes the cached public website content. */
trait BustsPublicCache
{
    public static function bootBustsPublicCache(): void
    {
        static::saved(fn () => PublicContent::flush());
        static::deleted(fn () => PublicContent::flush());
    }
}
