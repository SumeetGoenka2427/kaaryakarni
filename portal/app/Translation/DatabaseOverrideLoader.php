<?php

namespace App\Translation;

use App\Models\SiteText;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Translation\FileLoader;

/**
 * Loads lang/{locale}/{group}.php as usual, then applies admin overrides from
 * the `site_texts` table (Admin → Site Texts). Overrides are cached per
 * locale + group and flushed when admin saves, so there is no DB query per
 * request.
 */
class DatabaseOverrideLoader extends FileLoader
{
    /** Translation groups admin may edit. */
    public const EDITABLE_GROUPS = ['web', 'enums'];

    public function load($locale, $group, $namespace = null)
    {
        $lines = parent::load($locale, $group, $namespace);

        if (($namespace !== null && $namespace !== '*') || ! in_array($group, self::EDITABLE_GROUPS, true)) {
            return $lines;
        }

        foreach (self::overrides($group, $locale) as $key => $value) {
            Arr::set($lines, $key, $value);
        }

        return $lines;
    }

    /** @return array<string, string> dot-key => text */
    public static function overrides(string $group, string $locale): array
    {
        return Cache::rememberForever(self::cacheKey($group, $locale), function () use ($group, $locale) {
            if (! Schema::hasTable('site_texts')) {
                return [];
            }

            return SiteText::where('group', $group)
                ->whereNotNull("value_{$locale}")
                ->where("value_{$locale}", '!=', '')
                ->pluck("value_{$locale}", 'key')
                ->all();
        });
    }

    public static function flush(string $group): void
    {
        foreach (array_keys(config('portal.locales')) as $locale) {
            Cache::forget(self::cacheKey($group, $locale));
        }
    }

    private static function cacheKey(string $group, string $locale): string
    {
        return "site_texts.{$group}.{$locale}";
    }
}
