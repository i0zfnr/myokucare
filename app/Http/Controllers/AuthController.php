<?php

namespace App\Http\Controllers;

use App\Models\Oku;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function signup(Request $request)
    {
        $okuRequired = Rule::requiredIf($request->input('role') === 'oku_user');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['employer', 'oku_user'])],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'ic_number' => [$okuRequired, 'nullable', 'string', 'max:20', 'unique:okus,ic_number'],
            'gender' => [$okuRequired, 'nullable', Rule::in(['Lelaki', 'Perempuan'])],
            'age' => [$okuRequired, 'nullable', 'integer', 'min:16', 'max:120'],
            'marital_status' => [$okuRequired, 'nullable', Rule::in(['Berkahwin', 'Bujang', 'Duda', 'Janda'])],
            'address' => [$okuRequired, 'nullable', 'string', 'max:2000'],
            'phone_number' => [$okuRequired, 'nullable', 'string', 'max:20'],
            'education_level' => [$okuRequired, 'nullable', 'string', 'max:100'],
            'oku_card_number' => [$okuRequired, 'nullable', 'string', 'max:50', 'unique:okus,oku_card_number'],
            'oku_category' => [$okuRequired, 'nullable', Rule::in(['Fizikal', 'Penglihatan', 'Pendengaran', 'Pertuturan', 'Pembelajaran', 'Mental', 'Pelbagai'])],
            'sektor_pekerjaan' => [$okuRequired, 'nullable', Rule::in(['Sektor Awam', 'Sektor Swasta', 'Bekerja Sendiri', 'Tidak Bekerja'])],
            'jenis_bantuan' => ['nullable', 'array'],
            'jenis_bantuan.*' => ['nullable', Rule::in(['EPOKU', 'BTB', 'BPT', 'BAT', 'Lain-lain', 'Tiada'])],
        ]);

        DB::transaction(function () use ($data): void {
            $oku = null;
            if ($data['role'] === 'oku_user') {
                $oku = Oku::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'ic_number' => $data['ic_number'],
                    'gender' => $data['gender'],
                    'age' => $data['age'],
                    'marital_status' => $data['marital_status'],
                    'address' => $data['address'],
                    'phone_number' => $data['phone_number'],
                    'education_level' => $data['education_level'],
                    'oku_card_number' => $data['oku_card_number'],
                    'oku_category' => $data['oku_category'],
                    'sektor_pekerjaan' => $data['sektor_pekerjaan'] ?? null,
                    'jenis_bantuan' => $data['jenis_bantuan'] ?? null,
                    'availability_status' => 'Mencari Kerja',
                    'verification_status' => 'Pending',
                ]);
            }

            User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'password' => $data['password'],
                'oku_id' => $oku?->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        });

        return redirect()->route('login')->with('success', 'Pendaftaran berjaya. Sila log masuk dan muat naik gambar Kad OKU anda.');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak cubaan log masuk. Sila cuba semula dalam {$seconds} saat.",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'E-mel atau kata laluan tidak tepat.',
            ]);
        }

        if (! $request->user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akaun ini tidak aktif. Sila hubungi pentadbir.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        if ($request->user()->role === 'oku_user' && ! $request->user()->oku?->oku_card_image_path) {
            return redirect()->route('career-profile.show')
                ->with('success', 'Sila muat naik gambar Kad OKU anda untuk memulakan proses pengesahan JKM.');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
