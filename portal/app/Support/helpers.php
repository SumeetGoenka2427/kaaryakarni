<?php

use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Route;

if (! function_exists('localized_route')) {
    /**
     * URL for a localized route in the current (or given) language.
     * localized_route('about') → /about in English, /hi/about in Hindi.
     */
    function localized_route(string $name, array $parameters = [], ?string $locale = null, bool $absolute = true): string
    {
        $locale ??= app()->getLocale();
        $default = array_key_first(config('portal.locales'));

        $routeName = $locale === $default ? $name : "{$locale}.{$name}";

        return route($routeName, $parameters, $absolute);
    }
}

if (! function_exists('base_route_name')) {
    /** Route name without its language prefix: "hi.about" → "about". */
    function base_route_name(?string $name = null): ?string
    {
        $name ??= Route::currentRouteName();

        if ($name === null) {
            return null;
        }

        foreach (array_keys(config('portal.locales')) as $locale) {
            if (str_starts_with($name, "{$locale}.")) {
                return substr($name, strlen($locale) + 1);
            }
        }

        return $name;
    }
}

if (! function_exists('switch_locale_url')) {
    /**
     * The current page in another language (for the EN | हिं toggle and
     * hreflang tags). Keeps route parameters and the query string.
     */
    function switch_locale_url(string $locale): string
    {
        $route = Route::current();
        $base = base_route_name();

        if ($route && $base && Route::has($base) && Route::has($locale === 'en' ? $base : "{$locale}.{$base}")) {
            // Only real URI parameters ({slug}, {id}); route defaults such as
            // Route::view()'s "view"/"status" must not leak into the query string.
            $parameters = array_intersect_key($route->parameters(), array_flip($route->parameterNames()));
            $url = localized_route($base, $parameters, $locale);
            $query = request()->getQueryString();

            return $query ? "{$url}?{$query}" : $url;
        }

        return localized_route('home', [], $locale);
    }
}

if (! function_exists('route_is_current')) {
    /** True when the current page is this route (in any language), for aria-current. */
    function route_is_current(string ...$names): bool
    {
        $current = base_route_name();

        foreach ($names as $name) {
            if ($current === $name || ($current && str_ends_with($name, '*') && str_starts_with($current, rtrim($name, '*')))) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('inr')) {
    /** Indian-format rupee amount: 482600 → ₹4,82,600 */
    function inr(int|float|string|null $amount, bool $symbol = true): string
    {
        $amount = (float) $amount;
        $formatter = new NumberFormatter('en_IN', NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, fmod($amount, 1.0) == 0.0 ? 0 : 2);

        return ($symbol ? '₹' : '').$formatter->format($amount);
    }
}

if (! function_exists('safe_url')) {
    /**
     * Admin-entered link: allow site paths ("/membership") and https:// URLs
     * only, so javascript:, data: or protocol-relative links can never render.
     */
    function safe_url(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        return str_starts_with(strtolower($url), 'https://') && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}

if (! function_exists('setting')) {
    /** setting('membership_fee') — admin-editable site setting (cached). */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('setting_t')) {
    /** setting_t('org_name') — bilingual setting in the current language, English fallback. */
    function setting_t(string $key): ?string
    {
        return app(SettingsService::class)->localized($key);
    }
}
