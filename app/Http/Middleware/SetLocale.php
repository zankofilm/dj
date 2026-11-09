<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $candidate = $request->query('lang', session('locale', config('marketplace.default_locale')));
        $locale = in_array($candidate, Locale::SUPPORTED, true) ? $candidate : config('marketplace.default_locale');

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
