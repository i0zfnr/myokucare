<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\ExportAuditLog;
use App\Models\Job;
use App\Models\Oku;
use App\Models\OkuEmployment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleBasedRecordsAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_oku_user_cannot_view_an_unlinked_employer(): void
    {
        [$oku] = $this->records();
        $unlinked = $this->employer('Other');
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id]);

        $this->actingAs($user)->get(route('employers.show', $unlinked))->assertForbidden();
    }

    public function test_employer_cannot_view_a_worker_from_another_organisation(): void
    {
        [$oku, $employer] = $this->records();
        $otherEmployer = $this->employer('Other');
        $user = User::factory()->create(['role' => 'employer', 'employer_id' => $otherEmployer->id]);

        $this->actingAs($user)->get(route('employments.show', $oku->employments()->first()))->assertForbidden();
    }

    public function test_employer_can_open_own_employer_page_but_not_another_company(): void
    {
        [, $employer] = $this->records();
        $otherEmployer = $this->employer('Other');
        $user = User::factory()->create(['role' => 'employer', 'employer_id' => $employer->id]);

        $this->actingAs($user)->get(route('employers.index'))
            ->assertRedirect(route('employers.show', $employer));
        $this->get(route('employers.show', $employer))->assertOk();
        $this->get(route('employers.show', $otherEmployer))->assertForbidden();
    }

    public function test_csv_export_is_private_audited_and_masks_nric(): void
    {
        Storage::fake('local');
        [$oku, $employer] = $this->records();
        $user = User::factory()->create(['role' => 'employer', 'employer_id' => $employer->id]);

        $this->actingAs($user)->post(route('exports.store'), [
            'format' => 'CSV', 'report_type' => 'OKU_USERS',
            'purpose' => 'Internal employer record',
        ])->assertRedirect(route('exports.index'));

        $export = ExportAuditLog::firstOrFail();
        Storage::disk('local')->assertExists($export->generated_file_path);
        $contents = Storage::disk('local')->get($export->generated_file_path);
        $this->assertStringContainsString('******-**-1234', $contents);
        $this->assertStringNotContainsString('900101011234', $contents);
    }

    public function test_delete_requires_permission_reason_and_confirmation(): void
    {
        [, $employer] = $this->records(false);
        $officer = User::factory()->create(['role' => 'jkm_officer']);
        $this->actingAs($officer)->delete(route('employers.destroy', $employer), [
            'reason' => 'Duplicate record', 'confirmation_text' => 'DELETE',
        ])->assertForbidden();

        $officer->update(['permissions' => ['employer.delete']]);
        $this->actingAs($officer)->delete(route('employers.destroy', $employer), [
            'reason' => 'Duplicate record', 'confirmation_text' => 'WRONG',
        ])->assertSessionHasErrors('confirmation_text');
        $this->assertNull($employer->fresh()->deleted_at);
    }

    public function test_pdf_and_xlsx_exports_are_valid_files(): void
    {
        Storage::fake('local');
        [, $employer] = $this->records();
        $user = User::factory()->create(['role' => 'employer', 'employer_id' => $employer->id]);

        foreach (['PDF' => '%PDF-', 'XLSX' => "PK\x03\x04"] as $format => $signature) {
            $this->actingAs($user)->post(route('exports.store'), [
                'format' => $format, 'report_type' => 'OKU_USERS',
                'purpose' => 'Internal employer record',
            ])->assertRedirect(route('exports.index'));
            $export = ExportAuditLog::query()->where('format', $format)->firstOrFail();
            $this->assertStringStartsWith($signature, Storage::disk('local')->get($export->generated_file_path));
        }
    }

    private function records(bool $employment = true): array
    {
        $oku = Oku::query()->create([
            'name' => 'Ahmad Bin Ali', 'ic_number' => '900101011234', 'gender' => 'Lelaki',
            'age' => 36, 'marital_status' => 'Bujang', 'address' => 'Kuala Lumpur',
            'education_level' => 'SPM', 'oku_card_number' => 'OKU-100',
            'oku_category' => 'Fizikal', 'employment_status' => 'Bekerja',
        ]);
        $employer = $this->employer('Linked');
        if ($employment) {
            $job = Job::query()->create([
                'employer_id' => $employer->id, 'title' => 'Clerk', 'description' => 'Work',
                'requirements' => 'None', 'oku_category_suitable' => 'Semua',
                'salary_min' => 1500, 'location' => 'KL',
            ]);
            OkuEmployment::query()->create([
                'oku_id' => $oku->id, 'employer_id' => $employer->id, 'job_id' => $job->id,
                'job_title' => 'Clerk', 'start_date' => now()->subMonth(), 'status' => 'ACTIVE',
            ]);
        }

        return [$oku, $employer];
    }

    private function employer(string $suffix): Employer
    {
        return Employer::query()->create([
            'company_name' => "{$suffix} Sdn Bhd", 'registration_number' => "REG-{$suffix}",
            'address' => 'Kuala Lumpur', 'industry_sector' => 'Services',
            'contact_person' => 'Manager', 'phone_number' => '0123456789',
            'email' => strtolower($suffix).'@example.test',
        ]);
    }
}
