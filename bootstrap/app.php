<?php

use App\Contracts\OkuVerificationProvider;
use App\Contracts\TranslationProvider;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureIdentityVerificationFeatureEnabled;
use App\Http\Middleware\EnsureMyKadIsVerified;
use App\Http\Middleware\EnsureOkuProfileIsCurrent;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SetPreferredLocale;
use App\Services\HttpTranslationProvider;
use App\Services\Identity\UnavailableOkuVerificationProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'active' => EnsureAccountIsActive::class,
            'identity.feature' => EnsureIdentityVerificationFeatureEnabled::class,
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->appendToGroup('web', EnsureAccountIsActive::class);
        $middleware->appendToGroup('web', SetPreferredLocale::class);
        $middleware->appendToGroup('api', EnsureAccountIsActive::class);
        $middleware->appendToGroup('web', EnsureOkuProfileIsCurrent::class);
        $middleware->appendToGroup('web', EnsureMyKadIsVerified::class);
    })
    ->withBindings([
        OkuVerificationProvider::class => UnavailableOkuVerificationProvider::class,
        TranslationProvider::class => HttpTranslationProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'guideline/activity'),
        );
    })->create();
