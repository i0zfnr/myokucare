<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackGuidelineActivityRequest;
use App\Models\GuidelineActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuidelineController extends Controller
{
    private const ROLES = ['oku_user', 'employer', 'jkm_officer'];

    private const LANGUAGES = ['BM', 'EN', 'ZH_CN'];

    public function show(Request $request): View
    {
        $requestedRole = $request->query('role');
        $userRole = $request->user()?->role;
        $role = in_array($requestedRole, self::ROLES, true)
            ? $requestedRole
            : (in_array($userRole, self::ROLES, true) ? $userRole : 'oku_user');

        return view('guideline.show', [
            'role' => $role,
            'roles' => self::ROLES,
            'languages' => [
                'BM' => 'Bahasa Melayu',
                'EN' => 'English',
                'ZH_CN' => '中文（简体）',
            ],
            'currentLanguage' => $request->session()->get('preferred_language', 'BM'),
            'isOnboarding' => $request->boolean('onboarding'),
            'isReplay' => $request->boolean('replay'),
            'version' => (string) config('app.guideline_version', '1'),
            'nextUrl' => $request->user() ? route('dashboard') : route('login'),
        ]);
    }

    public function track(TrackGuidelineActivityRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $version = (string) config('app.guideline_version', '1');
        $language = $request->session()->get('preferred_language', 'BM');

        if ($user && in_array($data['action'], ['COMPLETED', 'SKIPPED'], true)) {
            $alreadyRecorded = $user->has_completed_guideline
                && $user->guideline_completed_version === $version;

            $user->forceFill([
                'has_completed_guideline' => true,
                'guideline_completed_at' => $user->guideline_completed_at ?? now(),
                'last_guideline_viewed_at' => now(),
                'guideline_completed_version' => $version,
            ])->save();

            if ($alreadyRecorded && $data['action'] === 'COMPLETED') {
                return response()->json(['next_url' => route('dashboard')]);
            }
        } elseif ($user && $data['action'] === 'REPLAYED') {
            $user->forceFill(['last_guideline_viewed_at' => now()])->save();
        }

        GuidelineActivityLog::create([
            'user_id' => $user?->id,
            'action' => $data['action'],
            'language' => $language,
            'device_type' => $data['device_type'],
            'guideline_version' => $version,
        ]);

        return response()->json([
            'next_url' => $user ? route('dashboard') : route('login'),
        ]);
    }

    public function language(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'preferred_language' => ['required', Rule::in(self::LANGUAGES)],
        ]);

        $request->session()->put('preferred_language', $data['preferred_language']);
        $request->user()?->forceFill(['preferred_language' => $data['preferred_language']])->save();

        GuidelineActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'LANGUAGE_SELECTED',
            'language' => $data['preferred_language'],
            'device_type' => $request->input('device_type') === 'PWA' ? 'PWA' : 'WEB',
            'guideline_version' => (string) config('app.guideline_version', '1'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['reload' => true]);
        }

        return back();
    }
}
