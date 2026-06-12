<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the request locale (French primary, English secondary) from an
 * explicit ?locale param, then the Accept-Language header, falling back to the
 * configured default. Keeps API responses localized without hardcoding.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['fr', 'en'];

        $locale = $request->query('locale')
            ?? $request->header('X-Locale')
            ?? $request->getPreferredLanguage($supported);

        if (is_string($locale)) {
            $locale = substr($locale, 0, 2);
        }

        app()->setLocale(in_array($locale, $supported, true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
