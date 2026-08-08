<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Job;
use App\Models\Oku;
use App\Models\User;
use App\Models\WelfareApplication;
use App\Services\OkuImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class MyOkuCareTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_oku_pages_are_available(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'is_active' => true]));
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Selamat datang, Admin System')
            ->assertSee('aria-current="page"', false)
            ->assertSee('action="'.route('oku.index').'"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('<svg', false);
        $this->get('/oku')->assertOk()->assertSee('Senarai Rekod OKU');
    }

    public function test_jkm_can_use_the_complete_oku_form_and_clear_boolean_values(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]));

        $this->get('/oku/create')
            ->assertOk()
            ->assertSee('Maklumat Peribadi')
            ->assertSee('Pendaftaran OKU')
            ->assertSeeText('Hubungan & Akses')
            ->assertSeeText('Dokumen & Gambar')
            ->assertSee('name="has_smartphone"', false);

        $response = $this->post('/oku', [
            'name' => 'Siti Ujian',
            'ic_number' => '900101115566',
            'gender' => 'Perempuan',
            'age' => 36,
            'marital_status' => 'Bujang',
            'address' => 'Besut, Terengganu',
            'residential_mukim' => 'Kampung Raja',
            'residential_village' => 'Kampung Raja',
            'residential_postcode' => '22200',
            'education_level' => 'SPM',
            'oku_card_number' => 'PH110500009999',
            'oku_category' => 'Fizikal',
            'employment_status' => 'Tidak Bekerja',
            'phone_number' => '0123456789',
            'has_smartphone' => '1',
            'has_internet' => '1',
            'is_active' => '1',
            'oku_card_image' => UploadedFile::fake()->createWithContent(
                'kad-oku.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
            'profile_photo' => UploadedFile::fake()->createWithContent(
                'profil.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);

        $oku = Oku::query()->where('ic_number', '900101115566')->firstOrFail();
        $response->assertRedirect(route('oku.show', $oku));
        $this->assertTrue($oku->has_smartphone);
        $this->assertSame('Pending', $oku->verification_status);
        Storage::disk('local')->assertExists($oku->oku_card_image_path);
        Storage::disk('local')->assertExists($oku->profile_photo_path);

        $this->put(route('oku.update', $oku), [
            'name' => $oku->name,
            'ic_number' => $oku->ic_number,
            'gender' => $oku->gender,
            'age' => $oku->age,
            'marital_status' => $oku->marital_status,
            'address' => $oku->address,
            'residential_mukim' => $oku->residential_mukim,
            'residential_village' => $oku->residential_village,
            'residential_postcode' => $oku->residential_postcode,
            'education_level' => $oku->education_level,
            'oku_card_number' => $oku->oku_card_number,
            'oku_category' => $oku->oku_category,
            'employment_status' => $oku->employment_status,
            'has_smartphone' => '0',
            'has_internet' => '0',
            'is_active' => '0',
        ])->assertRedirect(route('oku.show', $oku));

        $this->assertFalse($oku->fresh()->has_smartphone);
        $this->assertFalse($oku->fresh()->is_active);
    }

    public function test_only_super_admin_can_manage_users_with_audit_and_self_protection(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);
        $oku = Oku::query()->create([
            'name' => 'Test OKU', 'ic_number' => 'ADMIN-TEST-IC-1', 'gender' => 'Lelaki', 'age' => 30,
            'marital_status' => 'Bujang', 'address' => 'Test', 'education_level' => 'SPM',
            'oku_card_number' => 'ADMIN-TEST-OKU-1', 'oku_category' => 'Fizikal',
        ]);

        $this->actingAs($officer)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Pengurusan Pengguna')
            ->assertSee('Audit Aktiviti');

        $this->post(route('admin.users.store'), [
            'name' => 'Pegawai Baharu',
            'email' => 'pegawai.baharu@example.test',
            'role' => 'jkm_officer',
            'is_active' => '1',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
        ])->assertRedirect(route('admin.users.role', 'jkm_officer'));

        $created = User::query()->where('email', 'pegawai.baharu@example.test')->firstOrFail();
        $this->assertSame('jkm_officer', $created->role);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $admin->id,
            'subject_user_id' => $created->id,
            'action' => 'user_created',
        ]);

        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'oku_user',
            'oku_id' => $oku->id,
            'is_active' => '0',
        ])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->is_active);
        $this->assertSame('super_admin', $admin->fresh()->role);

        $this->get(route('admin.audit'))
            ->assertOk()
            ->assertSee('Akaun dicipta')
            ->assertSee('Jumlah aktiviti')
            ->assertDontSee('127.0.0.1');

        $this->get(route('admin.audit.export'))
            ->assertOk()
            ->assertDownload();
        $this->assertDatabaseHas('activity_logs', ['actor_id' => $admin->id, 'action' => 'audit_exported']);

        $this->get(route('admin.audit', ['date_from' => today()->addDay()->format('Y-m-d')]))
            ->assertSessionHasErrors('date_from');

        $this->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $targetId = $created->id;
        $this->delete(route('admin.users.destroy', $targetId))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $targetId]);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $admin->id,
            'action' => 'user_deleted',
        ]);
    }

    public function test_pentadbir_can_open_role_specific_user_pages_from_an_automatic_sidebar_dropdown(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin System Utama',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Pegawai Dalam Senarai',
            'role' => 'jkm_officer',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Majikan Tidak Dipaparkan',
            'role' => 'employer',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.role', 'jkm_officer'))
            ->assertOk()
            ->assertSee('Pengguna Pegawai JKM')
            ->assertSee('Pegawai Dalam Senarai')
            ->assertDontSee('Majikan Tidak Dipaparkan')
            ->assertSee('Pengguna OKU');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pentadbiran');
    }

    public function test_employer_directory_filters_sorts_and_shows_live_summary(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]));

        Employer::query()->create([
            'company_name' => 'Zeta Inklusif',
            'registration_number' => 'REG-ZETA',
            'address' => 'Besut',
            'industry_sector' => 'Teknologi',
            'contact_person' => 'Zainab',
            'phone_number' => '0123456701',
            'email' => 'zeta@example.test',
            'has_oku_quota' => true,
            'is_active' => true,
        ]);
        Employer::query()->create([
            'company_name' => 'Alfa Niaga',
            'registration_number' => 'REG-ALFA',
            'address' => 'Kuala Terengganu',
            'industry_sector' => 'Peruncitan',
            'contact_person' => 'Amin',
            'phone_number' => '0123456702',
            'email' => 'alfa@example.test',
            'is_active' => false,
        ]);

        $this->get('/employers?sector=Teknologi')
            ->assertOk()
            ->assertSee('Zeta Inklusif')
            ->assertDontSee('Alfa Niaga')
            ->assertSee('Mesra OKU');

        $this->get('/employers?sort_by=company_name&sort_direction=asc')
            ->assertOk()
            ->assertSeeInOrder(['Alfa Niaga', 'Zeta Inklusif']);

        $this->get('/employers?status=unknown')->assertSessionHasErrors('status');

        $this->get(route('employers.create'))
            ->assertOk()
            ->assertSee('Daftar Majikan Baharu');

        $this->post(route('employers.store'), [
            'company_name' => 'Best Inklusif Sdn Bhd',
            'registration_number' => 'REG-BEST',
            'address' => 'Jerteh, Terengganu',
            'industry_sector' => 'Perkhidmatan',
            'contact_person' => 'Siti',
            'phone_number' => '0123456799',
            'email' => 'siti@best.test',
            'website' => 'https://best.test',
            'number_of_employees' => 25,
            'has_oku_quota' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('employers.index'));

        $createdEmployer = Employer::query()->where('registration_number', 'REG-BEST')->firstOrFail();
        $this->assertTrue($createdEmployer->has_oku_quota);
        $this->get(route('employers.edit', $createdEmployer))
            ->assertOk()
            ->assertSee('Kemaskini Profil Majikan');
    }

    public function test_job_directory_filters_available_jobs_and_oku_user_can_record_interest(): void
    {
        $oku = Oku::query()->create([
            'name' => 'Pencari Kerja',
            'ic_number' => 'JOB-IC-1',
            'gender' => 'Lelaki',
            'age' => 29,
            'marital_status' => 'Bujang',
            'address' => 'Besut',
            'education_level' => 'Diploma',
            'oku_card_number' => 'JOB-OKU-1',
            'oku_category' => 'Fizikal',
        ]);
        $employer = Employer::query()->create([
            'company_name' => 'Syarikat Inklusif',
            'registration_number' => 'JOB-EMP-1',
            'address' => 'Besut',
            'industry_sector' => 'Teknologi',
            'contact_person' => 'HR',
            'phone_number' => '0123000000',
            'email' => 'jobs@example.test',
            'is_active' => true,
        ]);
        $job = Job::query()->create([
            'employer_id' => $employer->id,
            'title' => 'Pembantu Data',
            'description' => 'Mengurus data pejabat.',
            'requirements' => 'Boleh menggunakan komputer.',
            'oku_category_suitable' => 'Fizikal',
            'salary_min' => 2000,
            'salary_max' => 2500,
            'location' => 'Besut',
            'employment_type' => 'Sepenuh Masa',
            'application_deadline' => today()->addMonth(),
            'is_active' => true,
        ]);
        Job::query()->create([
            'employer_id' => $employer->id,
            'title' => 'Jawatan Ditutup',
            'description' => 'Tidak patut dipaparkan.',
            'requirements' => 'Tiada',
            'oku_category_suitable' => 'Semua',
            'salary_min' => 1500,
            'location' => 'Besut',
            'is_active' => false,
        ]);
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id, 'is_active' => true]);
        $this->actingAs($user);

        $this->get('/jobs?category=Fizikal&location=Besut')
            ->assertOk()
            ->assertSee('Pembantu Data')
            ->assertDontSee('Jawatan Ditutup')
            ->assertSee('Saya Berminat');

        $this->post(route('jobs.interest', $job))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('job_interests', ['oku_id' => $oku->id, 'job_id' => $job->id, 'status' => 'Interested']);
        $this->assertSame(1, $job->fresh()->applications_count);

        $this->post(route('jobs.interest', $job));
        $this->assertSame(1, $job->fresh()->applications_count);
        $this->get('/jobs?employment_type=Freelance')->assertSessionHasErrors('employment_type');
    }

    public function test_jkm_can_create_and_edit_an_inclusive_job_vacancy(): void
    {
        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);
        $employer = Employer::query()->create([
            'company_name' => 'Majikan Kerjaya', 'registration_number' => 'JOB-FORM-EMP',
            'address' => 'Besut', 'industry_sector' => 'Perkhidmatan', 'contact_person' => 'HR',
            'phone_number' => '0123000011', 'email' => 'hr@kerjaya.test', 'is_active' => true,
        ]);
        $this->actingAs($officer)->get(route('jobs.create'))->assertOk()->assertSee('Tambah Peluang Kerja');

        $this->post(route('jobs.store'), [
            'employer_id' => $employer->id, 'title' => 'Pembantu Operasi',
            'description' => 'Membantu operasi pejabat.', 'requirements' => 'Boleh bekerja dalam pasukan.',
            'oku_category_suitable' => 'Semua', 'salary_min' => 1800, 'salary_max' => 2300,
            'location' => 'Besut', 'employment_type' => 'Sepenuh Masa',
            'application_deadline' => today()->addMonth()->format('Y-m-d'), 'is_active' => '1',
        ])->assertRedirect(route('jobs.index'));

        $job = Job::query()->where('title', 'Pembantu Operasi')->firstOrFail();
        $this->assertTrue($job->is_active);
        $this->get(route('jobs.edit', $job))->assertOk()->assertSee('Kemaskini Peluang Kerja');
    }

    public function test_welfare_records_are_private_and_applicants_can_submit_for_their_linked_profile_only(): void
    {
        $firstOku = Oku::query()->create([
            'name' => 'Pemohon Pertama', 'ic_number' => 'WELFARE-IC-1', 'gender' => 'Perempuan', 'age' => 35,
            'marital_status' => 'Bujang', 'address' => 'Besut', 'education_level' => 'SPM',
            'oku_card_number' => 'WELFARE-OKU-1', 'oku_category' => 'Fizikal',
        ]);
        $secondOku = Oku::query()->create([
            'name' => 'Pemohon Kedua', 'ic_number' => 'WELFARE-IC-2', 'gender' => 'Lelaki', 'age' => 40,
            'marital_status' => 'Bujang', 'address' => 'Setiu', 'education_level' => 'SPM',
            'oku_card_number' => 'WELFARE-OKU-2', 'oku_category' => 'Pendengaran',
        ]);
        WelfareApplication::query()->create([
            'oku_id' => $firstOku->id, 'application_type' => 'Bantuan Mobiliti', 'application_date' => today(),
        ]);
        $otherApplication = WelfareApplication::query()->create([
            'oku_id' => $secondOku->id, 'application_type' => 'Bantuan Penjagaan', 'application_date' => today(),
        ]);
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $firstOku->id, 'is_active' => true]);
        $this->actingAs($user);

        $this->get('/welfare')
            ->assertOk()
            ->assertSee('Bantuan Mobiliti')
            ->assertDontSee('Bantuan Penjagaan');
        $this->get(route('welfare.show', $otherApplication))->assertForbidden();
        $this->get(route('welfare.show', $firstOku->welfareApplications()->first()))
            ->assertOk()
            ->assertSee('Butiran Permohonan')
            ->assertSee('Bantuan Mobiliti');

        $this->post(route('welfare.store'), [
            'oku_id' => $secondOku->id,
            'application_type' => 'Bantuan Kewangan',
            'notes' => 'Keperluan semasa.',
        ])->assertRedirect(route('welfare.index'));

        $this->assertDatabaseHas('welfare_applications', [
            'oku_id' => $firstOku->id,
            'application_type' => 'Bantuan Kewangan',
        ]);
    }

    public function test_jkm_can_filter_update_and_schedule_welfare_cases(): void
    {
        $oku = Oku::query()->create([
            'name' => 'Kes Kebajikan', 'ic_number' => 'CASE-IC-1', 'gender' => 'Lelaki', 'age' => 51,
            'marital_status' => 'Berkahwin', 'address' => 'Besut', 'education_level' => 'SPM',
            'oku_card_number' => 'CASE-OKU-1', 'oku_category' => 'Penglihatan',
        ]);
        $application = WelfareApplication::query()->create([
            'oku_id' => $oku->id, 'application_type' => 'Bantuan Alat Sokongan',
            'application_date' => today(), 'status' => 'Pending',
        ]);
        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);
        $this->actingAs($officer);

        $this->get('/welfare?status=Pending')
            ->assertOk()
            ->assertSee('Kes Kebajikan')
            ->assertSee('Menunggu');

        $this->put(route('welfare.update-status', $application), ['status' => 'Under Review'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('welfare_applications', [
            'id' => $application->id, 'status' => 'Under Review', 'reviewed_by' => $officer->id,
        ]);

        $reviewDate = today()->addWeek()->format('Y-m-d');
        $this->post(route('welfare.schedule-review', $application), ['scheduled_date' => $reviewDate])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame($reviewDate, $application->reviewSchedules()->firstOrFail()->scheduled_date->format('Y-m-d'));
        $this->assertSame($reviewDate, $application->fresh()->next_review_date->format('Y-m-d'));

        $this->get('/welfare?status=Unknown')->assertSessionHasErrors('status');
    }

    public function test_employment_report_filters_aggregate_data_and_exports_no_personal_records(): void
    {
        foreach ([
            ['name' => 'Rahsia Pertama', 'ic' => 'REPORT-SECRET-1', 'card' => 'REPORT-CARD-1', 'category' => 'Fizikal', 'gender' => 'Perempuan', 'status' => 'Bekerja', 'age' => 31],
            ['name' => 'Rahsia Kedua', 'ic' => 'REPORT-SECRET-2', 'card' => 'REPORT-CARD-2', 'category' => 'Fizikal', 'gender' => 'Perempuan', 'status' => 'Tidak Bekerja', 'age' => 42],
            ['name' => 'Rahsia Ketiga', 'ic' => 'REPORT-SECRET-3', 'card' => 'REPORT-CARD-3', 'category' => 'Mental', 'gender' => 'Lelaki', 'status' => 'Sendiri', 'age' => 25],
        ] as $record) {
            Oku::query()->create([
                'name' => $record['name'], 'ic_number' => $record['ic'], 'gender' => $record['gender'],
                'age' => $record['age'], 'marital_status' => 'Bujang', 'address' => 'Besut',
                'education_level' => 'SPM', 'oku_card_number' => $record['card'],
                'oku_category' => $record['category'], 'employment_status' => $record['status'],
            ]);
        }
        $this->actingAs(User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]));

        $this->get('/reports/employment?category=Fizikal&gender=Perempuan')
            ->assertOk()
            ->assertSee('Statistik Pekerjaan OKU')
            ->assertSee('50%')
            ->assertDontSee('Rahsia Pertama')
            ->assertDontSee('@if');

        $export = $this->get('/reports/employment/export?category=Fizikal&gender=Perempuan');
        $export->assertOk()->assertDownload();
        $csv = $export->streamedContent();
        $this->assertStringContainsString('LAPORAN STATISTIK PEKERJAAN OKU', $csv);
        $this->assertStringNotContainsString('Rahsia Pertama', $csv);
        $this->assertStringNotContainsString('REPORT-SECRET-1', $csv);

        $this->get('/reports/employment?gender=TidakSah')->assertSessionHasErrors('gender');
    }

    public function test_welfare_report_is_aggregate_filterable_and_blocks_raw_viewer_export(): void
    {
        $oku = Oku::query()->create([
            'name' => 'Nama Sulit Kebajikan', 'ic_number' => 'WREPORT-SECRET-1', 'gender' => 'Perempuan', 'age' => 38,
            'marital_status' => 'Bujang', 'address' => 'Besut', 'education_level' => 'SPM',
            'oku_card_number' => 'WREPORT-CARD-1', 'oku_category' => 'Fizikal',
        ]);
        foreach ([
            ['type' => 'Bantuan Mobiliti', 'status' => 'Approved'],
            ['type' => 'Bantuan Mobiliti', 'status' => 'Approved'],
            ['type' => 'Bantuan Kewangan', 'status' => 'Rejected'],
        ] as $record) {
            WelfareApplication::query()->create([
                'oku_id' => $oku->id,
                'application_type' => $record['type'],
                'application_date' => today()->subDays(10),
                'review_date' => today(),
                'status' => $record['status'],
                'notes' => 'Catatan sulit pemohon.',
            ]);
        }
        $this->actingAs(User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]));

        $this->get('/reports/welfare?category=Fizikal')
            ->assertOk()
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee('Skop laporan:')
            ->assertSee('Statistik Kebajikan OKU')
            ->assertSee('66.7%')
            ->assertDontSee('Nama Sulit Kebajikan')
            ->assertDontSee('Catatan sulit pemohon');

        $export = $this->get('/reports/welfare/export?category=Fizikal');
        $export->assertOk()->assertDownload();
        $csv = $export->streamedContent();
        $this->assertStringContainsString('LAPORAN STATISTIK KEBAJIKAN OKU', $csv);
        $this->assertStringNotContainsString('Nama Sulit Kebajikan', $csv);
        $this->assertStringNotContainsString('Catatan sulit pemohon', $csv);

        $this->get('/reports/export/welfare')
            ->assertOk()
            ->assertDownload();
        $this->get('/reports/welfare?status=TidakSah')->assertSessionHasErrors('status');
    }

    public function test_super_admin_can_manage_profile_and_personal_system_settings(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Lama',
            'email' => 'admin-old@example.test',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $this->get(route('admin.profile'))->assertOk()->assertSee('Profil Saya');
        $this->put(route('admin.profile.update'), [
            'name' => 'Admin Baharu',
            'email' => 'admin-new@example.test',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Admin Baharu', 'email' => 'admin-new@example.test']);

        $this->get(route('admin.settings'))->assertOk()->assertSee('Tetapan Sistem');
        $this->put(route('admin.settings.update'), [
            'font_scale' => '125',
            'dashboard_refresh_seconds' => 30,
            'default_page_size' => 25,
            'high_contrast_default' => '1',
            'compact_sidebar' => '1',
            'show_help_panel' => '0',
            'email_case_notifications' => '1',
        ])->assertRedirect()->assertSessionHas('success');

        $preferences = $admin->fresh()->preferences;
        $this->assertSame('125', $preferences['font_scale']);
        $this->assertSame(30, $preferences['dashboard_refresh_seconds']);
        $this->assertTrue($preferences['compact_sidebar']);
        $this->assertFalse($preferences['show_help_panel']);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('data-default-font-scale="125"', false)
            ->assertSee('data-dashboard-refresh="30"', false)
            ->assertDontSee('Perlukan bantuan?');

        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);
        $this->actingAs($officer)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Profil Saya')
            ->assertSee('Tetapan');

        $regularUser = User::factory()->create(['role' => 'oku_user', 'is_active' => true]);
        $this->actingAs($regularUser)->get(route('admin.settings'))->assertForbidden();
    }

    public function test_oku_index_filters_sorts_and_validates_query_parameters(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]));
        foreach ([
            ['name' => 'Zainab Fizikal', 'ic' => 'FILTER-IC-1', 'card' => 'FILTER-OKU-1', 'category' => 'Fizikal', 'age' => 42, 'verification' => 'Verified'],
            ['name' => 'Amin Pendengaran', 'ic' => 'FILTER-IC-2', 'card' => 'FILTER-OKU-2', 'category' => 'Pendengaran', 'age' => 23, 'verification' => 'Pending'],
        ] as $record) {
            Oku::query()->create([
                'name' => $record['name'], 'ic_number' => $record['ic'], 'gender' => 'Perempuan',
                'age' => $record['age'], 'marital_status' => 'Bujang', 'address' => 'Besut',
                'education_level' => 'SPM', 'oku_card_number' => $record['card'],
                'oku_category' => $record['category'],
                'verification_status' => $record['verification'],
            ]);
        }

        $this->get('/oku?category=Fizikal&age_min=40')
            ->assertOk()
            ->assertSee('Zainab Fizikal')
            ->assertDontSee('Amin Pendengaran')
            ->assertSee('berdasarkan penapis semasa')
            ->assertDontSee('@if')
            ->assertDontSee('@endif');

        $this->get('/oku?sort_by=name&sort_direction=asc')
            ->assertOk()
            ->assertSeeInOrder(['Amin Pendengaran', 'Zainab Fizikal']);

        $this->get('/oku?verification_status=Pending')
            ->assertOk()
            ->assertSee('Amin Pendengaran')
            ->assertDontSee('Zainab Fizikal')
            ->assertSee('Menunggu pengesahan');

        $this->get('/oku?category=KategoriTidakSah')->assertSessionHasErrors('category');
        $this->get('/oku?verification_status=TidakSah')->assertSessionHasErrors('verification_status');
    }

    public function test_authenticated_dashboard_statistics_are_returned_as_live_json(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]));
        Oku::create([
            'name' => 'Live Statistic', 'ic_number' => 'LIVE-IC-1', 'gender' => 'Lelaki', 'age' => 28,
            'marital_status' => 'Bujang', 'address' => 'Besut', 'education_level' => 'SPM',
            'oku_card_number' => 'LIVE-OKU-1', 'oku_category' => 'Fizikal',
        ]);

        $this->getJson('/dashboard/statistics')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('categories.Fizikal', 1)
            ->assertJsonStructure(['active', 'employed', 'unemployed', 'updated_at']);
    }

    public function test_pwa_manifest_and_service_worker_are_ready_for_mobile_installation(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('MyOKUcare', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#FF6565', $manifest['theme_color']);
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('icons/pwa-192.png'));
        $this->assertFileExists(public_path('icons/pwa-512.png'));
        $this->assertFileExists(public_path('icons/pwa-maskable-512.png'));
    }

    public function test_oku_user_can_create_career_profile_with_private_documents_and_jkm_can_verify_it(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => null, 'is_active' => true]);

        $this->actingAs($user)->put('/profil-kerjaya', [
            'name' => 'Ahmad Kerjaya',
            'ic_number' => 'CAREER-IC-1',
            'gender' => 'Lelaki',
            'age' => 28,
            'marital_status' => 'Bujang',
            'address' => 'Besut, Terengganu',
            'residential_mukim' => 'Kampung Raja',
            'residential_village' => 'Kampung Raja',
            'residential_postcode' => '22200',
            'education_level' => 'Diploma',
            'oku_card_number' => 'CAREER-OKU-1',
            'oku_category' => 'Fizikal',
            'phone_number' => '0123456789',
            'career_summary' => 'Berminat dalam bidang pentadbiran.',
            'skills' => 'Microsoft Office, Data Entry',
            'availability_status' => 'Mencari Kerja',
            'oku_card_image' => UploadedFile::fake()->createWithContent(
                'kad-oku.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ])->assertRedirect('/profil-kerjaya');

        $oku = $user->refresh()->oku;
        $this->assertNotNull($oku);
        $this->assertSame('Pending', $oku->verification_status);
        Storage::disk('local')->assertExists($oku->oku_card_image_path);
        Storage::disk('local')->assertExists($oku->resume_path);

        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);
        $this->actingAs($officer)->put("/oku/{$oku->id}/verification", [
            'verification_status' => 'Verified',
            'verification_notes' => 'Kad telah disemak.',
            'card_address' => 'Kampung Raja, 22200 Besut, Terengganu',
            'card_mukim' => 'Kampung Raja',
            'residence_verification_notes' => 'Alamat kad sepadan dengan pengisytiharan.',
        ])->assertRedirect();

        $this->assertDatabaseHas('okus', [
            'id' => $oku->id,
            'verification_status' => 'Verified',
            'verified_by' => $officer->id,
        ]);
    }

    public function test_every_supported_role_can_log_in_with_email_and_password(): void
    {
        foreach (['super_admin', 'jkm_officer', 'employer', 'oku_user'] as $role) {
            $user = User::factory()->create([
                'email' => $role.'@example.test',
                'password' => 'Secret123!',
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'Secret123!',
            ]);
            $response->assertRedirect($role === 'oku_user' ? '/profil-kerjaya' : '/dashboard');

            $this->assertAuthenticatedAs($user);
            $this->post('/logout');
        }
    }

    public function test_public_user_can_register_but_cannot_self_assign_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'New OKU User',
            'email' => 'new-oku@example.test',
            'role' => 'oku_user',
            'ic_number' => 'REGISTER-IC-1',
            'gender' => 'Lelaki',
            'age' => 25,
            'marital_status' => 'Bujang',
            'address' => 'Besut, Terengganu',
            'residential_mukim' => 'Kampung Raja',
            'residential_village' => 'Kampung Raja',
            'residential_postcode' => '22200',
            'phone_number' => '0123456789',
            'education_level' => 'SPM',
            'oku_card_number' => 'REGISTER-OKU-1',
            'oku_category' => 'Fizikal',
            'sektor_pekerjaan' => 'Tidak Bekerja',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertRedirect('/login');

        $this->assertDatabaseHas('users', ['email' => 'new-oku@example.test', 'role' => 'oku_user']);
        $this->assertDatabaseHas('okus', [
            'ic_number' => 'REGISTER-IC-1',
            'oku_card_number' => 'REGISTER-OKU-1',
            'verification_status' => 'Pending',
            'residence_verification_status' => 'UNVERIFIED',
        ]);
        $this->assertGuest();

        $this->post('/register', [
            'name' => 'Fake Admin',
            'email' => 'fake-admin@example.test',
            'role' => 'super_admin',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'fake-admin@example.test']);
    }

    public function test_each_role_receives_its_own_dashboard(): void
    {
        $dashboards = [
            'super_admin' => 'Admin System',
            'jkm_officer' => 'Operasi JKM',
            'employer' => 'Portal Majikan',
            'oku_user' => 'Ruang Peribadi',
        ];

        foreach ($dashboards as $role => $expectedText) {
            $user = User::factory()->create(['role' => $role, 'is_active' => true]);
            $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee($expectedText);
        }
    }

    public function test_jkm_officer_can_import_oku_records_from_csv(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]));
        $csv = implode("\n", [
            'NAMA,NOMBOR KAD PENGENALAN,JANTINA,UMUR,STATUS PERKAHWINAN,ALAMAT SURAT MENYURAT,NEGERI KEDIAMAN,DAERAH KEDIAMAN,MUKIM KEDIAMAN,KAMPUNG ATAU KAWASAN,POSKOD,TARAF PENDIDIKAN,NOMBOR PENDAFTARAN OKU,KATEGORI OKU,SEKTOR PEKERJAAN,NAMA PEKERJAAN,JENIS BANTUAN',
            'TEST IMPORT,900101115555,LELAKI,36 tahun,BALU,ALAMAT TEST,TERENGGANU,BESUT,KAMPUNG RAJA,KAMPUNG RAJA,22200,SEKOLAH MENENGAH,PH110500000001,FIZIKAL,SWASTA,PEMBANTU KEDAI,BANTUAN OKU TIDAK BEKERJA (BTB)',
        ]);

        $this->post('/oku/import', [
            'import_file' => UploadedFile::fake()->createWithContent('senarai.csv', $csv),
        ])->assertRedirect();

        $this->assertDatabaseHas('okus', [
            'name' => 'TEST IMPORT',
            'age' => 36,
            'marital_status' => 'Janda',
            'employment_status' => 'Bekerja',
            'job_name' => 'Pembantu Kedai',
            'assistance_type' => 'BANTUAN OKU TIDAK BEKERJA (BTB)',
        ]);
    }

    public function test_xlsx_reader_imports_first_worksheet(): void
    {
        $headers = [
            'NAMA', 'NOMBOR KAD PENGENALAN', 'JANTINA', 'UMUR', 'STATUS PERKAHWINAN',
            'ALAMAT SURAT MENYURAT', 'NEGERI KEDIAMAN', 'DAERAH KEDIAMAN', 'MUKIM KEDIAMAN', 'KAMPUNG ATAU KAWASAN', 'POSKOD', 'TARAF PENDIDIKAN', 'NOMBOR PENDAFTARAN OKU',
            'KATEGORI OKU', 'SEKTOR PEKERJAAN',
        ];
        $data = ['XLSX IMPORT', '910101115555', 'PEREMPUAN', '35', 'BUJANG', 'ALAMAT XLSX', 'TERENGGANU', 'BESUT', 'KAMPUNG RAJA', 'KAMPUNG RAJA', '22200', 'DIPLOMA', 'DE110500000002', 'PENDENGARAN', 'SENDIRI'];
        $path = tempnam(sys_get_temp_dir(), 'oku-xlsx-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $rowXml = function (array $row, int $number): string {
            $cells = '';
            foreach ($row as $index => $value) {
                $column = chr(65 + $index);
                $cells .= '<c r="'.$column.$number.'" t="inlineStr"><is><t>'.htmlspecialchars($value, ENT_XML1).'</t></is></c>';
            }

            return '<row r="'.$number.'">'.$cells.'</row>';
        };
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet><sheetData>'.$rowXml($headers, 1).$rowXml($data, 2).'</sheetData></worksheet>');
        $zip->close();

        $result = app(OkuImportService::class)->import($path, 'xlsx');
        unlink($path);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('okus', ['name' => 'XLSX IMPORT', 'employment_status' => 'Sendiri']);
    }

    public function test_matching_api_returns_a_suitable_active_job(): void
    {
        $oku = Oku::create([
            'name' => 'Test Person', 'ic_number' => 'TEST-IC-1', 'gender' => 'Lelaki', 'age' => 30,
            'marital_status' => 'Bujang', 'address' => 'Selangor', 'education_level' => 'Diploma',
            'oku_card_number' => 'TEST-OKU-1', 'oku_category' => 'Fizikal',
        ]);
        $employer = Employer::create([
            'company_name' => 'Test Employer', 'registration_number' => 'TEST-REG-1', 'address' => 'Selangor',
            'industry_sector' => 'Technology', 'contact_person' => 'HR', 'phone_number' => '0123456789',
            'email' => 'hr@test.example',
        ]);
        Job::create([
            'employer_id' => $employer->id, 'title' => 'Accessible Role', 'description' => 'Description',
            'requirements' => 'Requirements', 'oku_category_suitable' => 'Fizikal', 'salary_min' => 2000,
            'location' => 'Selangor',
        ]);

        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id]);
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/oku/matching-jobs')
            ->assertOk()
            ->assertJsonPath('0.title', 'Accessible Role')
            ->assertJsonPath('0.match_score', 80);
    }
}
