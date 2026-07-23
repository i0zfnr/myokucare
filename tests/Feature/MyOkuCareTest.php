<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Job;
use App\Models\Oku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyOkuCareTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_oku_pages_are_available(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'is_active' => true]));
        $this->get('/dashboard')->assertOk()->assertSee('Selamat datang ke MyOKUcare');
        $this->get('/oku')->assertOk()->assertSee('Senarai Rekod OKU');
    }

    public function test_every_supported_role_can_log_in_with_email_and_password(): void
    {
        foreach (['super_admin', 'jkm_officer', 'employer', 'oku_user', 'family_member', 'viewer'] as $role) {
            $user = User::factory()->create([
                'email' => $role.'@example.test',
                'password' => 'Secret123!',
                'role' => $role,
                'is_active' => true,
            ]);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'Secret123!',
            ])->assertRedirect('/dashboard');

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
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => 'new-oku@example.test', 'role' => 'oku_user']);

        $this->post('/logout');
        $this->post('/register', [
            'name' => 'Fake Admin',
            'email' => 'fake-admin@example.test',
            'role' => 'super_admin',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'fake-admin@example.test']);
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

        $this->getJson('/api/v1/oku/matching-jobs?oku_id='.$oku->id)
            ->assertOk()
            ->assertJsonPath('0.title', 'Accessible Role')
            ->assertJsonPath('0.match_score', 80);
    }
}
