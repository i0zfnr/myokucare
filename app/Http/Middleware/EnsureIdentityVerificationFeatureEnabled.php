<?php

namespace App\Http\Middleware;

use App\Services\FeatureManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdentityVerificationFeatureEnabled
{
    public function __construct(private FeatureManager $features) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->features->identityVerificationEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'code' => 'IDENTITY_VERIFICATION_DISABLED',
                'message' => 'Pengesahan identiti sedang dimatikan sementara.',
            ], 503);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Pengesahan identiti sedang dimatikan sementara oleh pentadbir.');
    }
}
