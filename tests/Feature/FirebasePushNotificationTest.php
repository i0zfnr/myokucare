<?php

namespace Tests\Feature;

use App\Jobs\SendPushNotification;
use App\Models\PushSubscription;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FirebasePushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.firebase.enabled', true);
        config()->set('services.firebase.project_id', 'myokucare-test');
        config()->set('services.firebase.vapid_public_key', 'public-vapid-key');
        config()->set('services.firebase.web', [
            'apiKey' => 'public-api-key', 'authDomain' => 'myokucare-test.firebaseapp.com',
            'projectId' => 'myokucare-test', 'storageBucket' => 'myokucare-test.appspot.com',
            'messagingSenderId' => '123456789', 'appId' => '1:123456789:web:test',
        ]);
    }

    public function test_every_role_can_register_an_encrypted_pwa_device_token(): void
    {
        foreach (['super_admin', 'jkm_officer', 'employer', 'oku_user'] as $index => $role) {
            $user = User::factory()->create(['role' => $role]);
            $token = "firebase-token-{$role}-{$index}";

            $this->actingAs($user)->withHeader('X-MyOKUcare-PWA', '1')->postJson(route('push.subscriptions.store'), [
                'token' => $token, 'platform' => 'pwa', 'device_name' => 'Installed test PWA',
            ])->assertOk()->assertJson(['subscribed' => true]);

            $subscription = PushSubscription::query()->where('user_id', $user->id)->firstOrFail();
            $this->assertSame($token, $subscription->token);
            $this->assertNotSame($token, $subscription->getRawOriginal('token'));
            $this->post(route('logout'));
        }
    }

    public function test_normal_browser_request_cannot_register_a_push_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('push.subscriptions.store'), [
            'token' => 'browser-token', 'platform' => 'pwa',
        ])->assertForbidden();
    }

    public function test_user_can_only_remove_own_device_and_logout_revokes_remaining_devices(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownerToken = 'owner-firebase-token';
        $otherToken = 'other-firebase-token';
        $this->subscription($owner, $ownerToken);
        $this->subscription($other, $otherToken);

        $this->actingAs($owner)->withHeader('X-MyOKUcare-PWA', '1')->deleteJson(route('push.subscriptions.destroy'), [
            'token' => $otherToken,
        ])->assertOk();
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $other->id]);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertDatabaseMissing('push_subscriptions', ['user_id' => $owner->id]);
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $other->id]);
    }

    public function test_public_firebase_configuration_never_exposes_service_account_path(): void
    {
        config()->set('services.firebase.service_account_path', 'C:\\secrets\\firebase.json');
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('push.config'))
            ->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonMissing(['service_account_path' => 'C:\\secrets\\firebase.json']);

        $this->get(route('push.service-worker'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertDontSee('firebase.json');
    }

    public function test_fcm_delivery_contains_only_a_generic_alert_and_authenticated_link(): void
    {
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'message-id'], 200)]);
        Cache::put('firebase:fcm-access-token', 'test-access-token', 3000);
        $user = User::factory()->create(['preferred_language' => 'BM']);
        $subscription = $this->subscription($user, 'delivery-token');
        $user->notify(new SystemNotification(
            'notifications.welfare_status_title', 'notifications.welfare_status_message',
            ['type' => 'Sensitive welfare type', 'status' => 'Approved'], route('welfare.index'), 'welfare',
        ));
        $notification = $user->fresh()->notifications->first();

        (new SendPushNotification($notification->id))->handle(app(FirebaseCloudMessagingService::class));

        Http::assertSent(function ($request): bool {
            $data = $request->data()['message']['data'];
            $encoded = json_encode($request->data());

            return $request->hasHeader('Authorization', 'Bearer test-access-token')
                && $data['title'] === 'Makluman MyOKUcare'
                && $data['body'] === 'Terdapat perkembangan baharu pada akaun anda. Buka aplikasi untuk melihat.'
                && str_contains($data['url'], '/notifikasi/')
                && ! str_contains($encoded, 'Sensitive welfare type')
                && ! str_contains($encoded, 'Approved');
        });
        $this->assertNotNull($subscription->fresh()->last_success_at);
    }

    public function test_unregistered_firebase_token_is_removed(): void
    {
        Http::fake(['fcm.googleapis.com/*' => Http::response(['error' => ['status' => 'UNREGISTERED']], 404)]);
        Cache::put('firebase:fcm-access-token', 'test-access-token', 3000);
        $user = User::factory()->create();
        $subscription = $this->subscription($user, 'expired-token');

        try {
            app(FirebaseCloudMessagingService::class)->send($subscription, [
                'title' => 'MyOKUcare', 'body' => 'Update', 'url' => route('notifications.index'),
            ]);
        } catch (\RuntimeException) {
            // Expected delivery failure; the stale token must still be removed.
        }

        $this->assertDatabaseMissing('push_subscriptions', ['id' => $subscription->id]);
    }

    private function subscription(User $user, string $token): PushSubscription
    {
        return PushSubscription::query()->create([
            'user_id' => $user->id, 'token_hash' => hash('sha256', $token),
            'token' => $token, 'platform' => 'pwa', 'last_seen_at' => now(),
        ]);
    }
}
