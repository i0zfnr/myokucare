<?php

namespace App\Http\Middleware;

use App\Services\FeatureManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMyKadIsVerified
{
    public function __construct(private FeatureManager $features) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->features->identityVerificationEnabled()) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || ! $user->hasRole('oku_user') || $request->routeIs('identity-verification.*', 'language-settings.*', 'guideline.*', 'logout')) {
            return $next($request);
        }

        $roleMiddleware = collect($request->route()?->gatherMiddleware() ?? [])
            ->first(fn (string $middleware) => str_starts_with($middleware, 'role:'));
        if ($roleMiddleware && ! in_array('oku_user', explode(',', substr($roleMiddleware, 5)), true)) {
            return $next($request);
        }
        if (! $user->oku) {
            return $next($request);
        }

        if (! $user->hasVerifiedMyKad()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 'MYKAD_VERIFICATION_REQUIRED',
                    'redirectTo' => route('identity-verification.show'),
                ], 403);
            }

            return redirect()->route('identity-verification.show')
                ->with('warning', 'Pengesahan MyKad diperlukan sebelum anda boleh menggunakan sistem.');
        }

        return $next($request);
    }
}
