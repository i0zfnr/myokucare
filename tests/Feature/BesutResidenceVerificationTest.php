<?php

namespace Tests\Feature;

use App\Models\Oku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BesutResidenceVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    public function test_public_oku_registration_requires_an_approved_besut_mukim_and_postcode(): void
    {
        $response = $this->post(route('register.store'), $this->registrationData([
            'residential_mukim' => 'Kuala Terengganu',
            'residential_postcode' => 'ABC',
        ]));

        $response->assertSessionHasErrors(['residential_mukim', 'residential_postcode']);
        $this->assertDatabaseMissing('users', ['email' => 'besut-resident@example.test']);
    }

    public function test_officer_card_review_verifies_a_matching_declared_mukim(): void
    {
        $oku = $this->oku();
        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);

        $this->actingAs($officer)->put(route('oku.verify', $oku), [
            'verification_status' => 'Verified',
            'card_address' => 'Kampung Raja, 22200 Besut, Terengganu',
            'card_mukim' => 'Kampung Raja',
            'residence_verification_notes' => 'Alamat kad jelas dan sepadan.',
        ])->assertRedirect();

        $oku->refresh();
        $this->assertSame('VERIFIED', $oku->residence_verification_status);
        $this->assertSame('Kampung Raja, 22200 Besut, Terengganu', $oku->card_address);
        $this->assertSame($officer->id, $oku->residence_verified_by);
        $this->assertNotSame($oku->card_address, $oku->getRawOriginal('card_address'));
    }

    public function test_officer_card_review_flags_mismatch_and_location_changes_reset_the_result(): void
    {
        $oku = $this->oku();
        $officer = User::factory()->create(['role' => 'jkm_officer', 'is_active' => true]);

        $this->actingAs($officer)->put(route('oku.verify', $oku), [
            'verification_status' => 'Verified',
            'card_address' => 'Jabi, 22020 Besut, Terengganu',
            'card_mukim' => 'Jabi',
        ])->assertRedirect();
        $this->assertSame('MISMATCH', $oku->fresh()->residence_verification_status);

        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id, 'is_active' => true]);
        $this->actingAs($user)->put(route('quarterly-profile.update'), [
            'employment_status' => 'Tidak Bekerja',
            'address' => 'Alamat baharu, Pasir Akar, Besut',
            'residential_mukim' => 'Pasir Akar',
            'residential_village' => 'Kampung Pasir Akar',
            'residential_postcode' => '22010',
            'phone_number' => '0123456789',
            'confirm_information' => '1',
        ])->assertRedirect(route('dashboard'));

        $oku->refresh();
        $this->assertSame('UNVERIFIED', $oku->residence_verification_status);
        $this->assertNull($oku->card_address);
        $this->assertNull($oku->residence_verified_by);
    }

    public function test_disabling_besut_scope_allows_registration_from_another_malaysian_district(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->put(route('admin.feature-controls.update'), [
            'besut_only_location_scope_enabled' => '0',
        ])->assertRedirect();
        $this->post(route('logout'))->assertRedirect();

        $this->post(route('register.store'), $this->registrationData([
            'email' => 'selangor-resident@example.test',
            'ic_number' => '900101-10-4444',
            'oku_card_number' => 'OKU-SELANGOR-001',
            'residential_state' => 'Selangor',
            'residential_district' => 'Petaling',
            'residential_mukim' => null,
            'residential_village' => 'Petaling Jaya',
            'residential_postcode' => '46000',
        ]))->assertRedirect(route('login'));

        $this->assertDatabaseHas('okus', [
            'residential_state' => 'Selangor',
            'residential_district' => 'Petaling',
            'residence_verification_status' => 'NOT_APPLICABLE',
        ]);
    }

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Besut Resident',
            'email' => 'besut-resident@example.test',
            'role' => 'oku_user',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'ic_number' => '900101-11-5555',
            'gender' => 'Lelaki',
            'age' => 30,
            'marital_status' => 'Bujang',
            'address' => 'Kampung Raja, Besut, Terengganu',
            'residential_mukim' => 'Kampung Raja',
            'residential_village' => 'Kampung Raja',
            'residential_postcode' => '22200',
            'phone_number' => '0123456789',
            'education_level' => 'SPM',
            'oku_card_number' => 'OKU-BESUT-001',
            'oku_category' => 'Fizikal',
            'sektor_pekerjaan' => 'Tidak Bekerja',
        ], $overrides);
    }

    private function oku(): Oku
    {
        return Oku::query()->create([
            'name' => 'Pengguna Besut',
            'ic_number' => '900101-11-5555',
            'gender' => 'Lelaki',
            'age' => 30,
            'marital_status' => 'Bujang',
            'address' => 'Kampung Raja, Besut, Terengganu',
            'residential_mukim' => 'Kampung Raja',
            'residential_village' => 'Kampung Raja',
            'residential_postcode' => '22200',
            'education_level' => 'SPM',
            'oku_card_number' => 'OKU-BESUT-001',
            'oku_category' => 'Fizikal',
            'phone_number' => '0123456789',
            'oku_card_image_path' => 'oku-documents/card.jpg',
            'profile_reviewed_at' => now(),
        ]);
    }
}
