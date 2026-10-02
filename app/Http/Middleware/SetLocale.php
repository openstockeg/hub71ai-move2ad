<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Use Arabic for server text (validation messages) when the form posts locale=ar
 * or the page was opened with ?lang=ar.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Always set it: a long-lived worker would otherwise carry Arabic into the next request.
        $arabic = $request->input('locale') === 'ar' || $request->query('lang') === 'ar';
        // Not config('app.locale'): setLocale() overwrites that value too.
        app()->setLocale($arabic ? 'ar' : 'en');

        return $next($request);
    }
}
