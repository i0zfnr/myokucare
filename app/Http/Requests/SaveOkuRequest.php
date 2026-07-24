<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin', 'jkm_officer') === true;
    }

    public function rules(): array
    {
        $oku = $this->route('oku');

        return [
            'name' => ['required', 'string', 'max:255'],
            'ic_number' => ['required', 'string', 'max:20', Rule::unique('okus', 'ic_number')->ignore($oku)],
            'gender' => ['required', Rule::in(['Lelaki', 'Perempuan'])],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'marital_status' => ['required', Rule::in(['Berkahwin', 'Bujang', 'Duda', 'Janda'])],
            'address' => ['required', 'string', 'max:1000'],
            'education_level' => ['required', 'string', 'max:100'],
            'oku_card_number' => ['required', 'string', 'max:50', Rule::unique('okus', 'oku_card_number')->ignore($oku)],
            'oku_category' => ['required', Rule::in(['Fizikal', 'Penglihatan', 'Pendengaran', 'Pertuturan', 'Pembelajaran', 'Mental', 'Pelbagai'])],
            'employment_status' => ['required', Rule::in(['Bekerja', 'Tidak Bekerja', 'Sendiri'])],
            'job_name' => ['nullable', 'string', 'max:255', Rule::requiredIf($this->input('employment_status') === 'Bekerja')],
            'sektor_pekerjaan' => ['nullable', Rule::in(['Sektor Awam', 'Sektor Swasta', 'Bekerja Sendiri', 'Tidak Bekerja'])],
            'assistance_type' => ['nullable', 'string', 'max:255'],
            'jenis_bantuan' => ['nullable', 'array'],
            'jenis_bantuan.*' => ['nullable', Rule::in(['EPOKU', 'BTB', 'BPT', 'BAT', 'Lain-lain', 'Tiada'])],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'has_smartphone' => ['required', 'boolean'],
            'has_internet' => ['required', 'boolean'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'oku_card_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama penuh',
            'ic_number' => 'nombor kad pengenalan',
            'gender' => 'jantina',
            'age' => 'umur',
            'marital_status' => 'status perkahwinan',
            'address' => 'alamat',
            'education_level' => 'tahap pendidikan',
            'oku_card_number' => 'nombor kad OKU',
            'oku_category' => 'kategori OKU',
            'employment_status' => 'status pekerjaan',
            'job_name' => 'nama pekerjaan',
            'assistance_type' => 'jenis bantuan',
            'phone_number' => 'nombor telefon',
            'email' => 'alamat e-mel',
            'emergency_contact_name' => 'nama hubungan kecemasan',
            'emergency_contact_phone' => 'telefon hubungan kecemasan',
            'oku_card_image' => 'imej Kad OKU',
            'profile_photo' => 'gambar profil',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_smartphone' => $this->boolean('has_smartphone'),
            'has_internet' => $this->boolean('has_internet'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
