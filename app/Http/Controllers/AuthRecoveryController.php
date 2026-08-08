<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthRecoveryController extends Controller
{
    public function forgot()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $user = User::query()->where('email', $data['email'])->where('is_active', true)->first();

        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
            $this->audit($request, $user, 'password_reset_requested');
        }

        return back()->with('status', __('auth_recovery.reset_link_neutral'));
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $credentials = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $user = User::query()->where('email', $credentials['email'])->where('is_active', true)->first();
        if (! $user) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => __('auth_recovery.reset_failed'),
            ]);
        }

        $status = Password::reset($credentials, function (User $user, string $password) use ($request): void {
            DB::transaction(function () use ($user, $password, $request): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => null,
                ])->save();

                DB::table('sessions')->where('user_id', $user->id)->delete();
                if (DB::getSchemaBuilder()->hasTable('personal_access_tokens')) {
                    DB::table('personal_access_tokens')
                        ->where('tokenable_type', $user->getMorphClass())
                        ->where('tokenable_id', $user->id)
                        ->delete();
                }

                event(new PasswordReset($user));
                $this->audit($request, $user, 'password_reset_completed');
            });
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => __('auth_recovery.reset_failed'),
            ]);
        }

        return redirect()->route('login')->with('success', __('auth_recovery.password_reset_success'));
    }

    public function verificationNotice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function sendVerification(Request $request)
    {
        if (! $request->user()->hasVerifiedEmail() && $request->user()->is_active) {
            $request->user()->sendEmailVerificationNotification();
            $this->audit($request, $request->user(), 'email_verification_requested');
        }

        return back()->with('status', __('auth_recovery.verification_sent'));
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        abort_unless($request->user()->is_active, 403);

        if ($request->user()->markEmailAsVerified()) {
            $this->audit($request, $request->user(), 'email_verified');
        }

        return redirect()->route('dashboard')->with('success', __('auth_recovery.email_verified'));
    }

    private function audit(Request $request, User $user, string $action): void
    {
        ActivityLog::query()->create([
            'actor_id' => Auth::id(),
            'subject_user_id' => $user->id,
            'action' => $action,
            'changes' => null,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
        ]);
    }
}
