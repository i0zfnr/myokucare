<?php

namespace App\Services;

use App\Models\PushSubscription;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseCloudMessagingService
{
    public function send(PushSubscription $subscription, array $data): bool
    {
        $projectId = (string) config('services.firebase.project_id');
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(12)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $subscription->token,
                    'data' => array_map(fn ($value) => (string) $value, $data),
                    'webpush' => [
                        'headers' => ['Urgency' => 'normal', 'TTL' => '86400'],
                        'fcm_options' => ['link' => $data['url']],
                    ],
                ],
            ]);

        if ($response->successful()) {
            $subscription->update(['last_success_at' => now(), 'failure_count' => 0]);

            return true;
        }

        $subscription->increment('failure_count');
        if ($response->status() === 404 || str_contains((string) $response->body(), 'UNREGISTERED')) {
            $subscription->delete();
        }

        throw new RuntimeException('FCM delivery failed with HTTP '.$response->status().'.');
    }

    private function accessToken(): string
    {
        return Cache::remember('firebase:fcm-access-token', 3000, function (): string {
            $path = (string) config('services.firebase.service_account_path');
            if ($path === '' || ! is_file($path)) {
                throw new RuntimeException('Firebase service-account file is not configured.');
            }
            $credentials = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            $provider = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                $credentials,
            );
            $token = $provider->fetchAuthToken()['access_token'] ?? null;

            return is_string($token) && $token !== ''
                ? $token
                : throw new RuntimeException('Firebase access token could not be obtained.');
        });
    }
}
