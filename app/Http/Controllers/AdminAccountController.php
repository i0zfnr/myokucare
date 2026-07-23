<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminProfileRequest;
use App\Http\Requests\AdminSettingsRequest;

class AdminAccountController extends Controller
{
    public function profile()
    {
        return view('admin.profile', ['user' => request()->user()]);
    }

    public function updateProfile(AdminProfileRequest $request)
    {
        $data = $request->safe()->only(['name', 'email']);
        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }
        $request->user()->update($data);

        return back()->with('success', 'Profil pentadbir berjaya dikemas kini.');
    }

    public function settings()
    {
        return view('admin.settings', ['preferences' => $this->preferences(request()->user()->preferences ?? [])]);
    }

    public function updateSettings(AdminSettingsRequest $request)
    {
        $request->user()->update(['preferences' => $this->preferences($request->validated())]);

        return back()->with('success', 'Tetapan pentadbir berjaya disimpan.');
    }

    private function preferences(array $preferences): array
    {
        return array_merge([
            'font_scale' => '100',
            'dashboard_refresh_seconds' => 10,
            'default_page_size' => 15,
            'high_contrast_default' => false,
            'compact_sidebar' => false,
            'show_help_panel' => true,
            'email_case_notifications' => true,
        ], $preferences);
    }
}
