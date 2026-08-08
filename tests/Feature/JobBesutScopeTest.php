<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobBesutScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_job_is_limited_to_a_valid_besut_mukim_and_local_category(): void
    {
        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);
        $employer = $this->employer();

        $this->actingAs($officer)->post(route('jobs.store'), [
            'employer_id' => $employer->id,
            'title' => 'Pembantu Bengkel',
            'job_category' => 'Mekanik dan Automotif',
            'description' => 'Membantu operasi bengkel.',
            'requirements' => 'Boleh menerima arahan.',
            'oku_category_suitable' => 'Semua',
            'salary_min' => 1500,
            'workplace_mukim' => 'Kampung Raja',
            'workplace_village' => 'Kampung Gong Bayor',
            'employment_type' => 'Sepenuh Masa',
            'is_active' => '1',
        ])->assertRedirect(route('jobs.index'));

        $this->assertDatabaseHas('jobs', [
            'title' => 'Pembantu Bengkel',
            'job_category' => 'Mekanik dan Automotif',
            'workplace_state' => 'Terengganu',
            'workplace_district' => 'Besut',
            'workplace_mukim' => 'Kampung Raja',
            'location' => 'Kampung Gong Bayor, Kampung Raja, Besut, Terengganu',
        ]);
    }

    public function test_job_creation_rejects_a_location_outside_besut_and_unknown_category(): void
    {
        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);

        $this->actingAs($officer)->post(route('jobs.store'), [
            'employer_id' => $this->employer()->id,
            'title' => 'Jawatan Luar Skop',
            'job_category' => 'Pekerjaan Tidak Sah',
            'description' => 'Di luar kawasan.',
            'requirements' => 'Tiada.',
            'oku_category_suitable' => 'Semua',
            'salary_min' => 1500,
            'workplace_mukim' => 'Kuala Terengganu',
            'employment_type' => 'Sepenuh Masa',
        ])->assertSessionHasErrors(['job_category', 'workplace_mukim']);

        $this->assertDatabaseMissing('jobs', ['title' => 'Jawatan Luar Skop']);
    }

    public function test_directory_excludes_active_jobs_outside_besut(): void
    {
        $user = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);
        $employer = $this->employer();
        $base = [
            'employer_id' => $employer->id,
            'description' => 'Penerangan.',
            'requirements' => 'Keperluan.',
            'oku_category_suitable' => 'Semua',
            'salary_min' => 1500,
            'employment_type' => 'Sepenuh Masa',
            'is_active' => true,
        ];

        Job::query()->create($base + ['title' => 'Kerja Besut', 'location' => 'Kuala Besut', 'workplace_district' => 'Besut', 'workplace_mukim' => 'Kuala Besut', 'job_category' => 'Perikanan']);
        Job::query()->create($base + ['title' => 'Kerja Setiu', 'location' => 'Setiu', 'workplace_district' => 'Setiu', 'workplace_mukim' => 'Hulu Setiu', 'job_category' => 'Perikanan']);

        $this->actingAs($user)->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('Kerja Besut')
            ->assertDontSee('Kerja Setiu');
    }

    private function employer(): Employer
    {
        return Employer::query()->create([
            'company_name' => 'Majikan Besut',
            'registration_number' => 'BESUT-'.fake()->unique()->numerify('#####'),
            'address' => 'Kampung Raja, Besut',
            'industry_sector' => 'Perkhidmatan',
            'contact_person' => 'Pengurus',
            'phone_number' => '0123456789',
            'email' => fake()->unique()->safeEmail(),
            'is_active' => true,
        ]);
    }
}
