<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the website language from the URL.
 *
 * English is the default and has no prefix (/membership); Hindi pages live
 * under /hi (/hi/membership). The URL is the single source of truth so each
 * language version is a separate, indexable page for search engines.
 */
class SetLocale
{
    public const COOKIE = 'site_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locales = array_keys(config('portal.locales'));
        $default = $locales[0];
        $segment = $request->segment(1);

        $locale = in_array($segment, $locales, true) && $segment !== $default ? $segment : $default;

        app()->setLocale($locale);

        $response = $next($request);

        // Remember the visitor's choice so the language toggle stays sticky.
        if ($request->cookie(self::COOKIE) !== $locale && method_exists($response, 'withCookie')) {
            $response->withCookie(cookie()->forever(self::COOKIE, $locale, httpOnly: true, sameSite: 'lax'));
        }

        return $response;
    }
}
