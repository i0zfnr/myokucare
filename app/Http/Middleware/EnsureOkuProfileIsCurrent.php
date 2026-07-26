<?php

namespace App\Http\Middleware;

use App\Services\FeatureManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOkuProfileIsCurrent
{
    public function __construct(private FeatureManager $features) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('oku_user')) {
            return $next($request);
        }

        if ($this->features->identityVerificationEnabled() && ! $user->hasVerifiedMyKad()) {
            return $next($request);
        }

        if ($request->routeIs('quarterly-profile.*', 'language-settings.*', 'guideline.*', 'logout')) {
            return $next($request);
        }

        // Preserve the normal 403 response on routes that explicitly exclude OKU users.
        $routeMiddleware = $request->route()?->gatherMiddleware() ?? [];
        $roleMiddleware = collect($routeMiddleware)->first(
            fn (string $middleware) => str_starts_with($middleware, 'role:')
        );
        if ($roleMiddleware && ! in_array('oku_user', explode(',', substr($roleMiddleware, 5)), true)) {
            return $next($request);
        }

        // Accounts without a linked OKU record must remain able to create their profile.
        if (! $user->oku) {
            return $next($request);
        }

        if ($user->oku->isProfileReviewDue()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pengesahan profil tiga bulanan diperlukan.',
                    'redirect' => route('quarterly-profile.show'),
                ], 423);
            }

            return redirect()->route('quarterly-profile.show')
                ->with('warning', 'Sila sahkan maklumat terkini anda sebelum menggunakan sistem.');
        }

        return $next($request);
    }
}
