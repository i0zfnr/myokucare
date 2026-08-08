<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_request_uses_a_neutral_response_and_notifies_only_an_active_account(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', __('auth_recovery.reset_link_neutral'));
        $this->post(route('password.email'), ['email' => 'missing@example.test'])
            ->assertSessionHas('status', __('auth_recovery.reset_link_neutral'));

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('activity_logs', [
            'subject_user_id' => $user->id,
            'action' => 'password_reset_requested',
        ]);
    }

    public function test_password_reset_is_single_use_and_revokes_existing_access(): void
    {
        $user = User::factory()->create(['remember_token' => 'remember-me']);
        DB::table('sessions')->insert([
            'id' => 'old-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'Test', 'payload' => 'payload', 'last_activity' => now()->timestamp,
        ]);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token, 'email' => $user->email,
            'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123',
        ];

        $this->post(route('password.update'), $payload)->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
        $this->assertNull($user->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'old-session']);
        $this->assertDatabaseHas('activity_logs', ['subject_user_id' => $user->id, 'action' => 'password_reset_completed']);

        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_inactive_account_cannot_reset_its_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['is_active' => false]);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', __('auth_recovery.reset_link_neutral'));

        Notification::assertNothingSent();
    }

    public function test_unverified_user_is_blocked_until_signed_email_verification(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
        $this->post(route('verification.send'))->assertSessionHas('status');
        Notification::assertSentTo($user, VerifyEmail::class);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);
        $this->get($url)->assertRedirect(route('dashboard'));

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('activity_logs', ['subject_user_id' => $user->id, 'action' => 'email_verified']);
    }

    public function test_tampered_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.verify', [
            'id' => $user->id,
            'hash' => 'invalid',
        ]))->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }
}
