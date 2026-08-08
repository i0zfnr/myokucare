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
            'besutOnlyLocationScopeEnabled' => $features->besutOnlyLocationScopeEnabled(),
        ]);
    }

    public function update(Request $request, FeatureManager $features)
    {
        $controls = [
            'identity_verification_enabled' => FeatureManager::IDENTITY_VERIFICATION,
            'besut_only_location_scope_enabled' => FeatureManager::BESUT_ONLY_LOCATION_SCOPE,
        ];

        foreach ($controls as $input => $feature) {
            if (! $request->has($input)) {
                continue;
            }

            $enabled = $request->boolean($input);
            $features->set($feature, $enabled, $request->user()->id);
            ActivityLog::query()->create([
                'actor_id' => $request->user()->id,
                'subject_user_id' => $request->user()->id,
                'action' => 'system_feature_toggled',
                'changes' => ['feature' => $feature, 'enabled' => $enabled],
                'ip_address' => $request->ip(),
                'user_agent' => str($request->userAgent())->limit(1000),
            ]);
        }

        return back()->with('success', 'Kawalan ciri berjaya dikemas kini.');
    }
}
