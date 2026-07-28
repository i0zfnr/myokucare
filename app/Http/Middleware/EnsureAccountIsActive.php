<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['code' => 'ACCOUNT_DEACTIVATED'], 403);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Akaun ini telah dinyahaktifkan. Sila hubungi pentadbir.']);
        }

        return $next($request);
    }
}
