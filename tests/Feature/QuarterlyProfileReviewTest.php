<?php

namespace Tests\Feature;

use App\Models\Oku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuarterlyProfileReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_oku_user_is_locked_out_of_system_features(): void
    {
        [$user] = $this->okuUser(now()->subMonths(3)->subDay());

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('quarterly-profile.show'));

        $this->actingAs($user)
            ->get(route('jobs.index'))
            ->assertRedirect(route('quarterly-profile.show'));

        $this->actingAs($user)
            ->get(route('quarterly-profile.show'))
            ->assertOk()
            ->assertSeeText('Sahkan maklumat terkini anda')
            ->assertSee('name="employment_status"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="phone_number"', false);
    }

    public function test_overdue_api_style_request_receives_locked_response(): void
    {
        [$user] = $this->okuUser(now()->subMonths(4));

        $this->actingAs($user)
            ->getJson(route('dashboard.statistics'))
            ->assertStatus(423)
            ->assertJsonPath('redirect', route('quarterly-profile.show'));
    }

    public function test_user_must_confirm_information_before_access_is_restored(): void
    {
        [$user, $oku] = $this->okuUser(now()->subMonths(4));

        $this->actingAs($user)
            ->put(route('quarterly-profile.update'), [
                'employment_status' => 'Bekerja',
                'address' => 'Alamat baharu, Kuala Terengganu',
                'phone_number' => '0198765432',
            ])
            ->assertSessionHasErrors('confirm_information');

        $this->put(route('quarterly-profile.update'), [
            'employment_status' => 'Bekerja',
            'address' => 'Alamat baharu, Kuala Terengganu',
            'phone_number' => '0198765432',
            'confirm_information' => '1',
        ])->assertRedirect(route('dashboard'));

        $oku->refresh();
        $this->assertSame('Bekerja', $oku->employment_status);
        $this->assertSame('Alamat baharu, Kuala Terengganu', $oku->address);
        $this->assertSame('0198765432', $oku->phone_number);
        $this->assertTrue($oku->profile_reviewed_at->isToday());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_current_oku_user_and_non_oku_users_are_not_locked(): void
    {
        [$okuUser] = $this->okuUser(now()->subMonths(2));
        $this->actingAs($okuUser)->get(route('dashboard'))->assertOk();

        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
    }

    private function okuUser($reviewedAt): array
    {
        $oku = Oku::query()->create([
            'name' => 'Pengguna Ujian',
            'ic_number' => fake()->unique()->numerify('######-##-####'),
            'gender' => 'Lelaki',
            'age' => 30,
            'marital_status' => 'Bujang',
            'address' => 'Alamat asal, Besut',
            'education_level' => 'SPM / SPMV',
            'oku_card_number' => fake()->unique()->bothify('OKU-########'),
            'oku_category' => 'Fizikal',
            'employment_status' => 'Tidak Bekerja',
            'phone_number' => '0123456789',
            'profile_reviewed_at' => $reviewedAt,
        ]);

        $user = User::factory()->create([
            'role' => 'oku_user',
            'oku_id' => $oku->id,
            'is_active' => true,
        ]);

        return [$user, $oku];
    }
}
