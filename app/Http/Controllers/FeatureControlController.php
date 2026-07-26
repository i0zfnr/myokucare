<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\FeatureManager;
use Illuminate\Http\Request;

class FeatureControlController extends Controller
{
    public function index(FeatureManager $features)
    {
        return view('admin.feature-controls', [
            'identityVerificationEnabled' => $features->identityVerificationEnabled(),
        ]);
    }

    public function update(Request $request, FeatureManager $features)
    {
        $enabled = $request->boolean('identity_verification_enabled');
        $features->set(FeatureManager::IDENTITY_VERIFICATION, $enabled, $request->user()->id);

        ActivityLog::query()->create([
            'actor_id' => $request->user()->id,
            'subject_user_id' => $request->user()->id,
            'action' => 'system_feature_toggled',
            'changes' => [
                'feature' => FeatureManager::IDENTITY_VERIFICATION,
                'enabled' => $enabled,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(1000),
        ]);

        return back()->with('success', $enabled
            ? 'Pengesahan identiti telah diaktifkan.'
            : 'Pengesahan identiti telah dimatikan sementara.');
    }
}
