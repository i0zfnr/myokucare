<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Job;
use App\Models\JobInterest;
use App\Models\Oku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobCandidateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_oku_must_consent_before_interest_shares_their_profile(): void
    {
        [$job, $candidate] = $this->scenario();

        $this->actingAs($candidate)->post(route('jobs.interest', $job))
            ->assertSessionHasErrors('share_profile');

        $this->post(route('jobs.interest', $job), ['share_profile' => '1'])->assertRedirect();
        $this->assertNotNull(JobInterest::query()->firstOrFail()->profile_shared_at);
    }

    public function test_employer_sees_candidates_only_for_their_own_job(): void
    {
        [$job, $candidate, $employerUser] = $this->scenario();
        $this->actingAs($candidate)->post(route('jobs.interest', $job), ['share_profile' => '1']);

        $this->actingAs($employerUser)->get(route('jobs.candidates.index', $job))
            ->assertOk()->assertSee($candidate->oku->name)->assertSee('Download Résumé');

        $otherEmployer = $this->employer('OTHER');
        $otherUser = User::factory()->create(['role' => 'employer', 'employer_id' => $otherEmployer->id, 'is_active' => true]);
        $this->actingAs($otherUser)->get(route('jobs.candidates.index', $job))->assertForbidden();
    }

    public function test_resume_requires_profile_consent_and_job_ownership(): void
    {
        Storage::fake('local');
        [$job, $candidate, $employerUser] = $this->scenario();
        Storage::disk('local')->put($candidate->oku->resume_path, 'resume');
        $interest = JobInterest::query()->create(['oku_id' => $candidate->oku_id, 'job_id' => $job->id, 'status' => 'Interested', 'application_date' => today()]);

        $this->actingAs($employerUser)->get(route('jobs.candidates.resume', [$job, $interest]))->assertForbidden();
        $interest->update(['profile_shared_at' => now()]);
        $this->get(route('jobs.candidates.resume', [$job, $interest]))->assertDownload();
    }

    public function test_status_change_notifies_the_candidate(): void
    {
        [$job, $candidate, $employerUser] = $this->scenario();
        $interest = JobInterest::query()->create(['oku_id' => $candidate->oku_id, 'job_id' => $job->id, 'status' => 'Interested', 'application_date' => today(), 'profile_shared_at' => now()]);

        $this->actingAs($employerUser)->patch(route('jobs.candidates.update', [$job, $interest]), [
            'status' => 'Shortlisted', 'notes' => 'Hubungi calon.',
        ])->assertRedirect();

        $this->assertSame('Shortlisted', $interest->fresh()->status);
        $this->assertSame('notifications.job_status_title', $candidate->fresh()->notifications()->first()->data['title_key']);
    }

    private function scenario(): array
    {
        $employer = $this->employer('OWNER');
        $employerUser = User::factory()->create(['role' => 'employer', 'employer_id' => $employer->id, 'is_active' => true]);
        $oku = Oku::query()->create([
            'name' => 'Calon Besut', 'ic_number' => 'CANDIDATE-IC', 'gender' => 'Lelaki', 'age' => 28,
            'marital_status' => 'Bujang', 'address' => 'Besut', 'education_level' => 'SPM',
            'oku_card_number' => 'CANDIDATE-OKU', 'oku_category' => 'Fizikal', 'skills' => 'Servis asas',
            'career_summary' => 'Berminat dalam automotif.', 'resume_path' => 'oku-documents/resume.pdf',
        ]);
        $candidate = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id, 'is_active' => true]);
        $job = Job::query()->create([
            'employer_id' => $employer->id, 'title' => 'Pembantu Bengkel', 'job_category' => 'Mekanik dan Automotif',
            'description' => 'Membantu bengkel.', 'requirements' => 'Boleh belajar.', 'oku_category_suitable' => 'Semua',
            'salary_min' => 1500, 'location' => 'Kampung Raja, Besut, Terengganu', 'workplace_state' => 'Terengganu',
            'workplace_district' => 'Besut', 'workplace_mukim' => 'Kampung Raja', 'employment_type' => 'Sepenuh Masa', 'is_active' => true,
        ]);

        return [$job, $candidate, $employerUser];
    }

    private function employer(string $suffix): Employer
    {
        return Employer::query()->create([
            'company_name' => "Majikan {$suffix}", 'registration_number' => "EMP-{$suffix}",
            'address' => 'Besut', 'industry_sector' => 'Automotif', 'contact_person' => 'Pengurus',
            'phone_number' => '0123456789', 'email' => strtolower($suffix).'@example.test', 'is_active' => true,
        ]);
    }
}
