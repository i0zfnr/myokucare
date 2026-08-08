<?php

namespace App\Http\Controllers;

use App\Services\BesutResidenceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuarterlyProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('quarterly-profile.show', [
            'oku' => $request->user()->oku,
        ]);
    }

    public function update(Request $request, BesutResidenceService $residence)
    {
        if ($residence->restrictedToBesut()) {
            $request->merge([
                'residential_state' => config('besut.state'),
                'residential_district' => config('besut.district'),
            ]);
        }

        $oku = $request->user()->oku;
        abort_unless($oku, 404);
        $besutOnly = $residence->restrictedToBesut();
        $isBesut = $besutOnly || ($request->input('residential_state') === config('besut.state') && strcasecmp((string) $request->input('residential_district'), config('besut.district')) === 0);

        $data = $request->validate([
            'employment_status' => ['required', Rule::in(['Bekerja', 'Tidak Bekerja', 'Sendiri'])],
            'address' => ['required', 'string', 'max:2000'],
            'residential_state' => ['required', Rule::in($besutOnly ? [config('besut.state')] : config('besut.states'))],
            'residential_district' => array_filter(['required', 'string', 'max:100', $besutOnly ? Rule::in([config('besut.district')]) : null]),
            'residential_mukim' => array_filter([$isBesut ? 'required' : 'nullable', 'string', 'max:100', $isBesut ? Rule::in(config('besut.mukims')) : null]),
            'residential_village' => ['required', 'string', 'max:255'],
            'residential_postcode' => ['required', 'regex:/^\d{5}$/'],
            'phone_number' => ['required', 'string', 'max:20'],
            'confirm_information' => ['accepted'],
        ], [
            'confirm_information.accepted' => 'Sila sahkan bahawa maklumat yang diberikan adalah terkini.',
        ], [
            'employment_status' => 'status pekerjaan',
            'address' => 'alamat semasa',
            'phone_number' => 'nombor telefon',
        ]);

        $oku->update($residence->resetIfLocationChanged($oku, $residence->declaration([
            'employment_status' => $data['employment_status'],
            'address' => $data['address'],
            'residential_state' => $data['residential_state'],
            'residential_district' => $data['residential_district'],
            'residential_mukim' => $data['residential_mukim'],
            'residential_village' => $data['residential_village'],
            'residential_postcode' => $data['residential_postcode'],
            'phone_number' => $data['phone_number'],
            'profile_reviewed_at' => now(),
        ])));

        return redirect()->route('dashboard')
            ->with('success', 'Maklumat anda telah disahkan. Akses sistem dipulihkan untuk tiga bulan.');
    }
}
