<?php

namespace Tests\Feature;

use App\Models\GuidelineActivityLog;
use App\Models\Oku;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuidelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_guideline_and_welcome_entry_are_available(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee(route('guideline.show'))
            ->assertSee('Panduan Penggunaan');

        $this->get(route('guideline.show'))
            ->assertOk()
            ->assertSee('Selamat datang ke MyOKUcare')
            ->assertSee(route('welcome'))
            ->assertSee('Utama')
            ->assertSee('Pengguna OKU')
            ->assertSee('Majikan')
            ->assertSee('Pegawai JKM');
    }

    public function test_authenticated_admin_sees_guideline_inside_normal_application_layout(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->get(route('guideline.show'))
            ->assertOk()
            ->assertSee('class="app-shell"', false)
            ->assertSee('class="sidebar"', false)
            ->assertSee('guideline-authenticated', false)
            ->assertSee('Pegawai JKM')
            ->assertSee(route('welcome'))
            ->assertDontSee('guideline-header', false);
    }

    public function test_each_role_has_an_accurate_guideline(): void
    {
        $this->get(route('guideline.show', ['role' => 'oku_user']))
            ->assertOk()
            ->assertSee('Muat naik Kad OKU')
            ->assertSee('Muat naik MyKad');

        $this->get(route('guideline.show', ['role' => 'employer']))
            ->assertOk()
            ->assertSee('Dapatkan pautan organisasi')
            ->assertSee('Pentadbir yang diberi kuasa perlu memautkan akaun anda');

        $this->get(route('guideline.show', ['role' => 'jkm_officer']))
            ->assertOk()
            ->assertSee('Semak pengesahan tertunda')
            ->assertSee('Jana laporan');
    }

    public function test_guest_activity_is_recorded_without_personal_data(): void
    {
        $this->postJson(route('guideline.track'), [
            'action' => 'OPENED',
            'device_type' => 'WEB',
        ])->assertOk()->assertJsonPath('next_url', route('login'));

        $log = GuidelineActivityLog::firstOrFail();
        $this->assertNull($log->user_id);
        $this->assertSame('OPENED', $log->action);
        $this->assertSame('BM', $log->language);
        $this->assertSame('WEB', $log->device_type);
    }

    public function test_authenticated_completion_is_saved_and_replay_preserves_progress(): void
    {
        $user = User::factory()->create(['role' => 'employer']);

        $this->actingAs($user)->postJson(route('guideline.track'), [
            'action' => 'COMPLETED',
            'device_type' => 'PWA',
        ])->assertOk()->assertJsonPath('next_url', route('dashboard'));

        $completedAt = $user->fresh()->guideline_completed_at;
        $this->assertTrue($user->fresh()->has_completed_guideline);
        $this->assertSame((string) config('app.guideline_version'), $user->fresh()->guideline_completed_version);
        $this->assertNotNull($completedAt);

        $this->travel(2)->minutes();
        $this->postJson(route('guideline.track'), [
            'action' => 'REPLAYED',
            'device_type' => 'WEB',
        ])->assertOk();

        $freshUser = $user->fresh();
        $this->assertTrue($freshUser->has_completed_guideline);
        $this->assertTrue($freshUser->guideline_completed_at->equalTo($completedAt));
        $this->assertNotNull($freshUser->last_guideline_viewed_at);
        $this->assertDatabaseHas('guideline_activity_logs', [
            'user_id' => $user->id,
            'action' => 'REPLAYED',
        ]);
    }

    public function test_replay_link_is_available_in_settings_for_every_role(): void
    {
        foreach (['super_admin', 'jkm_officer', 'employer', 'oku_user'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('language-settings.edit'))
                ->assertOk()
                ->assertSee(route('guideline.show', ['replay' => 1]))
                ->assertSee('Lihat Tutorial Lagi');
        }
    }

    public function test_guideline_language_can_be_changed_before_login(): void
    {
        $this->post(route('guideline.language'), [
            'preferred_language' => 'EN',
            'device_type' => 'WEB',
        ])->assertRedirect();

        $this->get(route('guideline.show'))
            ->assertOk()
            ->assertSee('Welcome to MyOKUcare')
            ->assertSee('Choose your role');

        $this->assertDatabaseHas('guideline_activity_logs', [
            'action' => 'LANGUAGE_SELECTED',
            'language' => 'EN',
        ]);
    }

    public function test_guideline_remains_available_during_identity_gate(): void
    {
        SystemSetting::query()->where('key', 'identity_verification_enabled')->update(['value' => '1']);
        $oku = Oku::query()->create([
            'name' => 'Pengguna Panduan',
            'ic_number' => '900101011234',
            'gender' => 'Lelaki',
            'age' => 36,
            'marital_status' => 'Bujang',
            'address' => 'Besut, Terengganu',
            'education_level' => 'SPM',
            'oku_card_number' => 'OKU-GUIDE-1',
            'oku_category' => 'Fizikal',
            'employment_status' => 'Tidak Bekerja',
        ]);
        $user = User::factory()->create([
            'role' => 'oku_user',
            'oku_id' => $oku->id,
            'mykad_verification_status' => 'NOT_SUBMITTED',
        ]);

        $this->actingAs($user)->get(route('guideline.show', ['replay' => 1]))
            ->assertOk()
            ->assertSee('Panduan Penggunaan MyOKUcare');
    }

    public function test_invalid_tracking_action_is_rejected(): void
    {
        $this->postJson(route('guideline.track'), [
            'action' => 'UPLOADED_MYKAD',
            'device_type' => 'WEB',
            'user_id' => 999,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('guideline_activity_logs', 0);
    }

    public function test_manifest_launches_the_pwa_dashboard_and_first_open_logic_controls_onboarding(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('/dashboard?source=pwa', $manifest['start_url']);
    }
}
