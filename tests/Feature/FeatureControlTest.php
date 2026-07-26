<?php

namespace Tests\Feature;

use App\Models\Oku;
use App\Models\User;
use App\Services\FeatureManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FeatureControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    public function test_only_super_admin_can_manage_feature_controls(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $officer = User::factory()->create(['role' => 'jkm_officer']);

        $this->actingAs($admin)
            ->get(route('admin.feature-controls.index'))
            ->assertOk()
            ->assertSeeText('Kawalan Ciri')
            ->assertSeeText('Status semasa: AKTIF');

        $this->actingAs($officer)
            ->get(route('admin.feature-controls.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_disable_and_enable_identity_verification(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->put(route('admin.feature-controls.update'), [
            'identity_verification_enabled' => '0',
        ])->assertRedirect();
        $this->assertFalse(app(FeatureManager::class)->identityVerificationEnabled());
        $this->assertDatabaseHas('activity_logs', ['action' => 'system_feature_toggled']);

        $this->put(route('admin.feature-controls.update'), [
            'identity_verification_enabled' => '1',
        ])->assertRedirect();
        $this->assertTrue(app(FeatureManager::class)->identityVerificationEnabled());
    }

    public function test_disabled_feature_bypasses_mykad_lock_but_disables_verification_routes(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->put(route('admin.feature-controls.update'), [
            'identity_verification_enabled' => '0',
        ]);

        $oku = Oku::query()->create([
            'name' => 'Pengguna Ujian',
            'ic_number' => '001122-03-3344',
            'gender' => 'Lelaki',
            'age' => 30,
            'marital_status' => 'Bujang',
            'address' => 'Besut, Terengganu',
            'education_level' => 'SPM / SPMV',
            'oku_card_number' => 'OKU-TOGGLE-001',
            'oku_category' => 'Fizikal',
            'employment_status' => 'Tidak Bekerja',
            'phone_number' => '0123456789',
            'profile_reviewed_at' => now(),
        ]);
        $user = User::factory()->create([
            'role' => 'oku_user',
            'oku_id' => $oku->id,
            'mykad_verification_status' => 'NOT_SUBMITTED',
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->get(route('identity-verification.show'))->assertRedirect(route('dashboard'));
        $this->postJson(route('identity-verification.session.create'), ['consent' => true])
            ->assertStatus(503)
            ->assertJsonPath('code', 'IDENTITY_VERIFICATION_DISABLED');
    }
}
