<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Job;
use App\Models\Oku;
use App\Models\User;
use Illuminate\Database\Seeder;

class MyOkuCareSeeder extends Seeder
{
    public function run(): void
    {
        $oku = Oku::firstOrCreate(['ic_number' => '900101-14-5678'], ['name' => 'Ahmad bin Ali', 'gender' => 'Lelaki', 'age' => 36, 'marital_status' => 'Berkahwin', 'address' => 'Kuala Lumpur', 'education_level' => 'Diploma', 'oku_card_number' => 'OKU-000001', 'oku_category' => 'Fizikal', 'employment_status' => 'Tidak Bekerja', 'phone_number' => '0123456789', 'email' => 'oku@myokucare.test', 'has_smartphone' => true, 'has_internet' => true]);
        $employer = Employer::firstOrCreate(['registration_number' => 'MY-2026-0001'], ['company_name' => 'Inklusif Teknologi Sdn Bhd', 'address' => 'Cyberjaya, Selangor', 'industry_sector' => 'Technology', 'contact_person' => 'Siti Aminah', 'phone_number' => '03-88888888', 'email' => 'hr@inklusif.example', 'has_oku_quota' => true]);
        Job::firstOrCreate(['employer_id' => $employer->id, 'title' => 'Data Entry Assistant'], ['description' => 'Maintain and verify digital records.', 'requirements' => 'Basic computer skills and attention to detail.', 'oku_category_suitable' => 'Semua', 'salary_min' => 1800, 'salary_max' => 2300, 'location' => 'Cyberjaya', 'employment_type' => 'Sepenuh Masa', 'application_deadline' => now()->addMonths(2)]);

        $accounts = [
            ['name' => 'Admin System', 'email' => 'admin@myokucare.test', 'role' => 'super_admin'],
            ['name' => 'Pegawai JKM', 'email' => 'pegawai@myokucare.test', 'role' => 'jkm_officer'],
            ['name' => 'Wakil Majikan', 'email' => 'majikan@myokucare.test', 'role' => 'employer', 'employer_id' => $employer->id],
            ['name' => $oku->name, 'email' => 'oku@myokucare.test', 'role' => 'oku_user', 'oku_id' => $oku->id],

        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                $account + ['password' => 'Password123!', 'email_verified_at' => now(), 'is_active' => true],
            );
        }
    }
}
