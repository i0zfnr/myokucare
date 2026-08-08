<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Job;
use App\Models\Oku;
use App\Models\OkuEmployment;
use App\Models\User;
use App\Models\VerificationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_supplied_identity_evidence_cannot_verify_an_account(): void
    {
        $oku = $this->oku('Owner', '900101011111');
        $user = User::factory()->create([
            'role' => 'oku_user',
            'oku_id' => $oku->id,
            'mykad_verification_status' => 'SUBMISSION_IN_PROGRESS',
        ]);
        $session = $this->verificationSession($user);

        $response = $this->actingAs($user)->postJson(
            route('identity-verification.process', $session),
            [
                'documents' => [
                    'mykad_front' => [
                        'text' => "NAME: {$oku->name}\nNRIC: {$oku->ic_number}",
                        'confidence' => 1,
                    ],
                ],
                'qr' => ['payload' => '{"status":"VERIFIED"}', 'confidence' => 1],
            ],
        );

        $response->assertOk()->assertJsonPath('status', 'MANUAL_REVIEW_REQUIRED');
        $this->assertSame('MANUAL_REVIEW_REQUIRED', $user->fresh()->mykad_verification_status);
        $this->assertFalse($user->fresh()->hasVerifiedMyKad());
        $this->assertDatabaseHas('manual_reviews', [
            'session_id' => $session->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_employer_cannot_delete_another_employers_job(): void
    {
        $owner = $this->employer('Owner');
        $attacker = $this->employer('Attacker');
        $job = $this->job($owner);
        $user = User::factory()->create(['role' => 'employer', 'employer_id' => $attacker->id]);

        $this->actingAs($user)->deleteJson(route('jobs.destroy', $job))->assertForbidden();
        $this->assertDatabaseHas('jobs', ['id' => $job->id]);
    }

    public function test_oku_user_cannot_retrieve_coworker_identity_or_salary_data(): void
    {
        $employer = $this->employer('Shared');
        $owner = $this->oku('Owner', '900101011111');
        $coworker = $this->oku('Coworker', '900101012222');
        $this->employment($owner, $employer, '1800');
        $this->employment($coworker, $employer, '9900');
        $user = User::factory()->create([
            'role' => 'oku_user',
            'oku_id' => $owner->id,
            'mykad_verification_status' => 'VERIFIED',
        ]);

        $response = $this->actingAs($user)->getJson(route('employers.show', $employer))->assertOk();
        $content = $response->getContent();

        $this->assertStringNotContainsString($coworker->ic_number, $content);
        $this->assertStringNotContainsString('9900', $content);
        $response->assertJsonCount(1, 'employments')
            ->assertJsonPath('employments.0.oku_id', $owner->id);
    }

    public function test_restricted_jkm_export_never_contains_complete_sensitive_records(): void
    {
        $oku = $this->oku('Sensitive Person', '900101019999');
        $officer = User::factory()->create([
            'role' => 'jkm_officer',
            'permissions' => ['report.generate'],
        ]);

        $response = $this->actingAs($officer)->get(route('reports.export', 'oku'))->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('******-**-9999', $content);
        $this->assertStringNotContainsString($oku->ic_number, $content);
        $this->assertStringNotContainsString($oku->address, $content);
        $this->assertStringNotContainsString($oku->oku_card_number, $content);
        $this->assertStringNotContainsString($oku->oku_category, $content);
    }

    public function test_deactivated_user_is_blocked_on_the_next_existing_session_request(): void
    {
        $user = User::factory()->create(['role' => 'employer', 'is_active' => true]);
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->update(['is_active' => false]);

        $this->getJson(route('dashboard.statistics'))
            ->assertForbidden()
            ->assertJsonPath('code', 'ACCOUNT_DEACTIVATED');
        $this->assertGuest();
    }

    public function test_sensitive_and_authenticated_responses_disable_browser_caching(): void
    {
        Storage::fake('local');
        $oku = $this->oku('Document Owner', '900101013333');
        $path = "oku-documents/{$oku->id}/card/card.jpg";
        Storage::disk('local')->put($path, 'private-card');
        $oku->update(['oku_card_image_path' => $path]);
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');

        $this->get(route('career-profile.document', 'card'))
            ->assertOk()
            ->assertHeader('Content-Disposition')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    public function test_recovery_pages_have_security_headers_and_are_not_cached(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    public function test_card_upload_rejects_an_image_with_a_misleading_extension(): void
    {
        $oku = $this->oku('Upload Owner', '900101014444');
        $oku->update([
            'residential_state' => 'Terengganu', 'residential_district' => 'Besut',
            'residential_mukim' => 'Kampung Raja', 'residential_village' => 'Kampung Raja',
            'residential_postcode' => '22200', 'phone_number' => '0123456789',
            'availability_status' => 'Mencari Kerja',
        ]);
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id]);

        $this->actingAs($user)->put(route('career-profile.save'), [
            'name' => $oku->name, 'ic_number' => $oku->ic_number, 'gender' => $oku->gender,
            'age' => $oku->age, 'marital_status' => $oku->marital_status, 'address' => $oku->address,
            'residential_state' => 'Terengganu', 'residential_district' => 'Besut',
            'residential_mukim' => 'Kampung Raja', 'residential_village' => 'Kampung Raja',
            'residential_postcode' => '22200', 'education_level' => $oku->education_level,
            'oku_card_number' => $oku->oku_card_number, 'oku_category' => $oku->oku_category,
            'phone_number' => '0123456789', 'availability_status' => 'Mencari Kerja',
            'oku_card_image' => UploadedFile::fake()->createWithContent(
                'kad-oku.pdf',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ])->assertSessionHasErrors('oku_card_image');
    }

    private function oku(string $name, string $nric): Oku
    {
        return Oku::query()->create([
            'name' => $name,
            'ic_number' => $nric,
            'gender' => 'Lelaki',
            'age' => 30,
            'marital_status' => 'Bujang',
            'address' => 'Sensitive Address',
            'education_level' => 'SPM',
            'oku_card_number' => 'OKU-'.$nric,
            'oku_category' => 'Fizikal',
            'employment_status' => 'Bekerja',
            'profile_reviewed_at' => now(),
        ]);
    }

    private function employer(string $name): Employer
    {
        return Employer::query()->create([
            'company_name' => "{$name} Sdn Bhd",
            'registration_number' => "REG-{$name}",
            'address' => 'Kuala Lumpur',
            'industry_sector' => 'Services',
            'contact_person' => 'Manager',
            'phone_number' => '0123456789',
            'email' => strtolower($name).'@example.test',
        ]);
    }

    private function job(Employer $employer): Job
    {
        return Job::query()->create([
            'employer_id' => $employer->id,
            'title' => 'Clerk',
            'description' => 'Work',
            'requirements' => 'None',
            'oku_category_suitable' => 'Semua',
            'salary_min' => 1500,
            'location' => 'KL',
        ]);
    }

    private function employment(Oku $oku, Employer $employer, string $salary): OkuEmployment
    {
        return OkuEmployment::query()->create([
            'oku_id' => $oku->id,
            'employer_id' => $employer->id,
            'job_id' => $this->job($employer)->id,
            'job_title' => 'Clerk',
            'employment_type' => 'Full-time',
            'start_date' => now()->subMonth(),
            'status' => 'ACTIVE',
            'salary_encrypted' => $salary,
            'verification_status' => 'VERIFIED',
        ]);
    }

    private function verificationSession(User $user): VerificationSession
    {
        return $user->verificationSessions()->create([
            'status' => 'SUBMISSION_IN_PROGRESS',
            'consent_accepted_at' => now(),
            'expires_at' => now()->addHour(),
        ]);
    }
}
