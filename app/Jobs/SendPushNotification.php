<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPushNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(public readonly string $notificationId) {}

    public function handle(FirebaseCloudMessagingService $firebase): void
    {
        if (! config('services.firebase.enabled')) return;
        $notification = DatabaseNotification::query()->find($this->notificationId);
        $user = $notification?->notifiable;
        if (! $notification || ! $user instanceof User || ! $user->is_active) return;

        $locale = match ($user->preferred_language) {
            'EN' => 'en', 'ZH_CN' => 'zh-CN', default => 'bm',
        };
        $previousLocale = App::getLocale();
        App::setLocale($locale);
        $payload = [
            'title' => __('push.generic_title'),
            'body' => __('push.generic_body'),
            'url' => route('notifications.read', $notification),
            'tag' => 'myokucare-'.($notification->data['category'] ?? 'account').'-'.$notification->id,
            'notification_id' => $notification->id,
        ];

        try {
            $user->pushSubscriptions()->each(function ($subscription) use ($firebase, $payload): void {
                try {
                    $firebase->send($subscription, $payload);
                } catch (Throwable $exception) {
                    Log::warning('Firebase push delivery failed.', [
                        'subscription_id' => $subscription->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });
        } finally {
            App::setLocale($previousLocale);
        }
    }
}
