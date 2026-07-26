<?php

namespace App\Http\Controllers;

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

    public function update(Request $request)
    {
        $oku = $request->user()->oku;
        abort_unless($oku, 404);

        $data = $request->validate([
            'employment_status' => ['required', Rule::in(['Bekerja', 'Tidak Bekerja', 'Sendiri'])],
            'address' => ['required', 'string', 'max:2000'],
            'phone_number' => ['required', 'string', 'max:20'],
            'confirm_information' => ['accepted'],
        ], [
            'confirm_information.accepted' => 'Sila sahkan bahawa maklumat yang diberikan adalah terkini.',
        ], [
            'employment_status' => 'status pekerjaan',
            'address' => 'alamat semasa',
            'phone_number' => 'nombor telefon',
        ]);

        $oku->update([
            'employment_status' => $data['employment_status'],
            'address' => $data['address'],
            'phone_number' => $data['phone_number'],
            'profile_reviewed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Maklumat anda telah disahkan. Akses sistem dipulihkan untuk tiga bulan.');
    }
}
