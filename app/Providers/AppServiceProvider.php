<?php

namespace App\Providers;

use App\Jobs\SendPushNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('identity-verification', fn (Request $request) => Limit::perMinute(30)
            ->by((string) ($request->user()?->id ?? $request->ip())));

        if (config('services.firebase.enabled')) {
            DatabaseNotification::created(
                fn (DatabaseNotification $notification) => SendPushNotification::dispatch($notification->id)->afterCommit(),
            );
        }
    }
}
