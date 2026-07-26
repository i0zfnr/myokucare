<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetPreferredLocale
{
    private const LOCALES = ['BM' => 'bm', 'EN' => 'en', 'ZH_CN' => 'zh-CN'];

    public function handle(Request $request, Closure $next): Response
    {
        $preference = $request->user()?->preferred_language ?? $request->session()->get('preferred_language', 'BM');
        $locale = self::LOCALES[$preference] ?? 'bm';
        App::setLocale($locale);
        $request->session()->put('preferred_language', array_search($locale, self::LOCALES, true) ?: 'BM');

        return $next($request);
    }
}
