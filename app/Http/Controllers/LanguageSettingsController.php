<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageSettingsController extends Controller
{
    public const LANGUAGES = [
        'BM' => 'Bahasa Melayu',
        'EN' => 'English',
        'ZH_CN' => '中文（简体）',
    ];

    public function edit(Request $request)
    {
        return view('settings.language', [
            'languages' => self::LANGUAGES,
            'currentLanguage' => $request->user()->preferred_language,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'preferred_language' => ['required', Rule::in(array_keys(self::LANGUAGES))],
        ]);
        $request->user()->update($data);
        $request->session()->put('preferred_language', $data['preferred_language']);

        return back()->with('success', __('language.saved'));
    }
}
