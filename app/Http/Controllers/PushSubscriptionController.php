<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function config()
    {
        return response()->json([
            'enabled' => (bool) config('services.firebase.enabled'),
            'firebase' => config('services.firebase.web'),
            'vapidKey' => config('services.firebase.vapid_public_key'),
            'serviceWorkerUrl' => route('push.service-worker'),
            'serviceWorkerScope' => '/firebase-cloud-messaging-push-scope',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(config('services.firebase.enabled'), 503, 'PUSH_NOT_CONFIGURED');
        abort_unless($request->header('X-MyOKUcare-PWA') === '1', 403, 'PWA_INSTALL_REQUIRED');
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['required', 'in:pwa'],
        ]);
        $hash = hash('sha256', $data['token']);

        PushSubscription::query()->updateOrCreate(
            ['token_hash' => $hash],
            [
                'user_id' => $request->user()->id,
                'token' => $data['token'],
                'device_name' => $data['device_name'] ?? null,
                'platform' => 'pwa',
                'last_seen_at' => now(),
                'failure_count' => 0,
            ],
        );

        return response()->json(['subscribed' => true]);
    }

    public function destroy(Request $request)
    {
        abort_unless($request->header('X-MyOKUcare-PWA') === '1', 403, 'PWA_INSTALL_REQUIRED');
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);
        $request->user()->pushSubscriptions()
            ->where('token_hash', hash('sha256', $data['token']))
            ->delete();

        return response()->json(['subscribed' => false]);
    }
}
