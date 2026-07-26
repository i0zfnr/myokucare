<?php

namespace Tests\Feature;

use App\Models\ExportAuditLog;
use App\Models\Oku;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserSubmissionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultilingualSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_language_catalogues_have_the_same_keys(): void
    {
        $bm = array_keys(json_decode(file_get_contents(lang_path('bm.json')), true, flags: JSON_THROW_ON_ERROR));
        $en = array_keys(json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR));
        $zh = array_keys(json_decode(file_get_contents(lang_path('zh-CN.json')), true, flags: JSON_THROW_ON_ERROR));
        sort($bm);
        sort($en);
        sort($zh);

        $this->assertSame($bm, $en);
        $this->assertSame($en, $zh);
    }

    public function test_user_can_switch_language_and_preference_persists(): void
    {
        $user = User::factory()->create(['role' => 'jkm_officer', 'preferred_language' => 'BM']);

        $this->actingAs($user)->put(route('language-settings.update'), [
            'preferred_language' => 'EN',
        ])->assertRedirect();

        $this->assertSame('EN', $user->fresh()->preferred_language);
        $this->get(route('language-settings.edit'))
            ->assertOk()
            ->assertSee('Language Preference')
            ->assertSee('Current language');
    }

    public function test_simplified_chinese_interface_loads_after_login(): void
    {
        $user = User::factory()->create(['role' => 'jkm_officer', 'preferred_language' => 'ZH_CN']);

        $this->actingAs($user)->get(route('language-settings.edit'))
            ->assertOk()
            ->assertSee('语言偏好')
            ->assertSee('保存语言偏好');
    }

    public function test_language_settings_remain_available_during_mykad_gate(): void
    {
        SystemSetting::query()->where('key', 'identity_verification_enabled')->update(['value' => '1']);
        $user = User::factory()->create([
            'role' => 'oku_user',
            'oku_id' => $this->oku()->id,
            'mykad_verification_status' => 'NOT_SUBMITTED',
        ]);

        $this->actingAs($user)->get(route('language-settings.edit'))->assertOk();
    }

    public function test_original_user_content_is_preserved_when_provider_is_unavailable(): void
    {
        $oku = $this->oku();
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id, 'preferred_language' => 'ZH_CN']);

        $this->actingAs($user)->post(route('welfare.store'), [
            'application_type' => 'Bantuan pekerjaan',
            'notes' => '需要工作调整',
        ])->assertRedirect();

        $translation = UserSubmissionTranslation::firstOrFail();
        $this->assertSame('需要工作调整', $translation->original_text);
        $this->assertSame('ZH_CN', $translation->original_language);
        $this->assertSame('PROVIDER_UNAVAILABLE', $translation->provider_status);
        $this->assertDatabaseHas('welfare_applications', ['notes' => '需要工作调整']);
    }

    public function test_english_export_uses_english_headers(): void
    {
        Storage::fake('local');
        $oku = $this->oku();
        $user = User::factory()->create([
            'role' => 'jkm_officer',
            'preferred_language' => 'EN',
            'permissions' => ['oku_user.view', 'oku_user.export'],
        ]);

        $this->actingAs($user)->post(route('exports.store'), [
            'format' => 'CSV',
            'report_type' => 'OKU_USERS',
            'purpose' => 'Official JKM documentation',
            'language' => 'EN',
            'content_mode' => 'TRANSLATED',
        ])->assertRedirect();

        $export = ExportAuditLog::firstOrFail();
        $contents = Storage::disk('local')->get($export->generated_file_path);
        $this->assertStringContainsString('Masked NRIC', $contents);
        $this->assertStringContainsString('Career Summary', $contents);
        $this->assertSame('EN', $export->language);
    }

    private function oku(): Oku
    {
        return Oku::query()->create([
            'name' => 'Chen Wei Ming',
            'ic_number' => '900101011234',
            'gender' => 'Lelaki',
            'age' => 36,
            'marital_status' => 'Bujang',
            'address' => 'Kuala Lumpur',
            'education_level' => 'SPM',
            'oku_card_number' => 'OKU-ML-1',
            'oku_category' => 'Fizikal',
            'employment_status' => 'Tidak Bekerja',
            'career_summary' => '需要工作调整',
        ]);
    }
}
