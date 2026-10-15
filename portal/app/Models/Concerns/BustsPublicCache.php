<?php

namespace App\Models\Concerns;

use App\Services\Content\PublicContent;
use Illuminate\Support\Facades\Cache;

/** Any change to this model refreshes the cached public website content and homepage statistics. */
trait BustsPublicCache
{
    public static function bootBustsPublicCache(): void
    {
        $flush = function () {
            PublicContent::flush();
            Cache::forget('public_content.stats');
        };

        static::saved($flush);
        static::deleted($flush);
    }
}
