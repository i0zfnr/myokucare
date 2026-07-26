<?php

namespace App\Services;

use App\Models\Employer;
use App\Models\ExportAuditLog;
use App\Models\Oku;
use App\Models\OkuEmployment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ExportService
{
    public const TYPES = ['OKU_USERS', 'EMPLOYERS', 'EMPLOYMENT_RELATIONSHIPS'];

    public const FORMATS = ['PDF', 'CSV', 'XLSX'];

    public const PURPOSES = ['Official JKM documentation', 'Employment verification', 'Internal employer record', 'Worker documentation', 'Compliance reporting', 'Programme monitoring', 'Audit', 'Other'];

    public function __construct(
        private RecordAccessService $access,
        private PermissionService $permissions,
    ) {}

    public function generate(Request $request, array $data): ExportAuditLog
    {
        $user = $request->user();
        $data['language'] ??= $user->preferred_language ?? 'BM';
        $data['content_mode'] ??= 'TRANSLATED';
        $previousLocale = App::getLocale();
        App::setLocale(['BM' => 'bm', 'EN' => 'en', 'ZH_CN' => 'zh-CN'][$data['language']]);
        $permission = match ($data['report_type']) {
            'OKU_USERS' => 'oku_user.export',
            'EMPLOYERS' => 'employer.export',
            default => 'employment.export',
        };
        $this->permissions->authorize($user, $permission);
        $rows = $this->rows($user, $data);
        $fields = $this->allowedFields($user, $data['report_type'], $data['fields'] ?? []);
        $sensitive = array_values(array_intersect($fields, ['full_nric', 'oku_category', 'salary']));
        if ($sensitive) {
            $this->permissions->authorize($user, 'sensitive_data.export');
        }

        $export = ExportAuditLog::query()->create([
            'exported_by_user_id' => $user->id,
            'exported_by_role' => $user->role,
            'export_type' => $data['report_type'],
            'format' => $data['format'],
            'status' => 'PROCESSING',
            'record_count' => $rows->count(),
            'filters' => $data['filters'] ?? null,
            'fields_included' => $fields,
            'sensitive_fields_included' => $sensitive ?: null,
            'purpose' => $data['purpose'],
            'language' => $data['language'],
            'content_mode' => $data['content_mode'],
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(1000),
            'expires_at' => now()->addHours(24),
        ]);

        try {
            $matrix = $this->matrix($rows, $fields, $data['content_mode'], $data['language']);
            $extension = strtolower($data['format']);
            $path = "exports/{$user->id}/report_".Str::ulid().".{$extension}";
            match ($data['format']) {
                'CSV' => $this->csv($path, $matrix),
                'XLSX' => $this->xlsx($path, $matrix, $data['report_type']),
                'PDF' => $this->pdf($path, $matrix, $export, $user, $data['filters'] ?? []),
            };
            $export->update(['status' => 'READY', 'generated_file_path' => $path]);
        } catch (\Throwable $exception) {
            $export->update(['status' => 'FAILED', 'failure_reason' => class_basename($exception)]);
            App::setLocale($previousLocale);
            throw $exception;
        }

        App::setLocale($previousLocale);

        return $export->fresh();
    }

    public function downloadUrl(ExportAuditLog $export): string
    {
        return URL::temporarySignedRoute('exports.download', $export->expires_at, ['export' => $export]);
    }

    private function rows(User $user, array $data): Collection
    {
        $filters = $data['filters'] ?? [];
        $query = match ($data['report_type']) {
            'OKU_USERS' => $this->access->okus($user)->with(['employments.employer', 'translations']),
            'EMPLOYERS' => $this->access->employers($user)->with('employments.oku'),
            default => $this->access->employments($user)->with(['oku', 'employer']),
        };
        if ($data['report_type'] === 'EMPLOYMENT_RELATIONSHIPS') {
            $query->when(filled($filters['status'] ?? null), fn (Builder $q) => $q->where('status', $filters['status']));
            $query->when(filled($filters['employer_id'] ?? null), fn (Builder $q) => $q->where('employer_id', $filters['employer_id']));
            $query->when(filled($filters['oku_id'] ?? null), fn (Builder $q) => $q->where('oku_id', $filters['oku_id']));
            $query->when(filled($filters['department'] ?? null), fn (Builder $q) => $q->where('department', $filters['department']));
        }
        $query->when(filled($filters['date_from'] ?? null), fn (Builder $q) => $q->whereDate('created_at', '>=', $filters['date_from']));
        $query->when(filled($filters['date_to'] ?? null), fn (Builder $q) => $q->whereDate('created_at', '<=', $filters['date_to']));

        return $query->get();
    }

    private function allowedFields(User $user, string $type, array $requested): array
    {
        $defaults = match ($type) {
            'OKU_USERS' => ['name', 'masked_nric', 'oku_registration_number', 'job_title', 'department', 'career_summary', 'employment_status', 'verification_status'],
            'EMPLOYERS' => ['employer_name', 'registration_number', 'contact', 'address', 'industry'],
            default => ['name', 'masked_nric', 'employer_name', 'job_title', 'department', 'start_date', 'end_date', 'employment_status', 'verification_status'],
        };
        $allowed = $defaults;
        if ($this->permissions->allows($user, 'sensitive_data.export')) {
            $allowed = array_merge($allowed, ['full_nric', 'oku_category', 'salary', 'supervisor']);
        }

        return $requested ? array_values(array_intersect($requested, $allowed)) : $defaults;
    }

    private function matrix(Collection $rows, array $fields, string $contentMode, string $language): array
    {
        $labels = collect(['name', 'masked_nric', 'full_nric', 'oku_registration_number', 'oku_category', 'employer_name', 'registration_number', 'contact', 'address', 'industry', 'job_title', 'department', 'career_summary', 'start_date', 'end_date', 'employment_status', 'verification_status', 'salary', 'supervisor'])
            ->mapWithKeys(fn ($field) => [$field => __("field.{$field}")])->all();
        $matrix = [array_map(fn ($field) => $labels[$field] ?? $field, $fields)];
        foreach ($rows as $row) {
            $employment = $row instanceof OkuEmployment ? $row : $row->employments->first();
            $oku = $row instanceof Oku ? $row : ($row instanceof OkuEmployment ? $row->oku : $employment?->oku);
            $employer = $row instanceof Employer ? $row : ($row instanceof OkuEmployment ? $row->employer : $employment?->employer);
            $values = [
                'name' => $oku?->name, 'masked_nric' => $this->maskNric($oku?->ic_number), 'full_nric' => $oku?->ic_number,
                'oku_registration_number' => $oku?->oku_card_number, 'oku_category' => $oku?->disability_export_consent ? $oku?->oku_category : __('value.not_included'),
                'employer_name' => $employer?->company_name, 'registration_number' => $employer?->registration_number,
                'contact' => trim(($employer?->contact_person ?? '').' '.($employer?->phone_number ?? '').' '.($employer?->email ?? '')),
                'address' => $employer?->address, 'industry' => $employer?->industry_sector,
                'job_title' => $employment?->job_title, 'department' => $employment?->department,
                'career_summary' => $this->contentValue($oku, 'career_summary', $contentMode, $language),
                'start_date' => $employment?->start_date?->format('Y-m-d'), 'end_date' => $employment?->end_date?->format('Y-m-d'),
                'employment_status' => $employment?->status ?? $oku?->employment_status, 'verification_status' => $employment?->verification_status ?? $oku?->verification_status,
                'salary' => $employment?->salary_value, 'supervisor' => $employment?->supervisor_name,
            ];
            $matrix[] = array_map(fn ($field) => $values[$field] ?? '', $fields);
        }

        return $matrix;
    }

    private function contentValue(?Oku $oku, string $field, string $mode, string $language): string
    {
        if (! $oku) {
            return '';
        }
        $original = (string) ($oku->{$field} ?? '');
        $translation = $oku->translations?->firstWhere('field_name', $field);
        $translated = $language === 'EN' ? $translation?->translated_text_en : $translation?->translated_text_bm;
        if ($mode === 'ORIGINAL' || blank($translated)) {
            return $original;
        }

        return $mode === 'DUAL' ? "{$original}\n{$translated}" : $translated;
    }

    private function csv(string $path, array $matrix): void
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, "\xEF\xBB\xBF");
        foreach ($matrix as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        Storage::disk('local')->put($path, stream_get_contents($stream));
        fclose($stream);
    }

    private function xlsx(string $path, array $matrix, string $title): void
    {
        $input = tempnam(sys_get_temp_dir(), 'xlsx').'.json';
        $output = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        file_put_contents($input, json_encode(['title' => $title, 'rows' => $matrix], JSON_THROW_ON_ERROR));
        $process = new Process([config('identity_verification.python_binary'), base_path('scripts/generate_secure_xlsx.py'), $input, $output]);
        $process->setTimeout(60)->run();
        if (! $process->isSuccessful() || ! is_file($output)) {
            throw new RuntimeException('XLSX_GENERATION_FAILED: '.str($process->getErrorOutput())->limit(500));
        }
        Storage::disk('local')->put($path, file_get_contents($output));
        @unlink($input);
        @unlink($output);
    }

    private function pdf(string $path, array $matrix, ExportAuditLog $export, User $user, array $filters): void
    {
        $input = tempnam(sys_get_temp_dir(), 'report').'.json';
        $output = tempnam(sys_get_temp_dir(), 'report').'.pdf';
        file_put_contents($input, json_encode([
            'reference' => $export->id, 'title' => __('export.report.'.strtolower($export->export_type)),
            'role' => $user->role_label, 'organisation' => $user->employer?->company_name ?? 'Jabatan Kebajikan Masyarakat',
            'generatedAt' => now()->format('d/m/Y H:i'), 'filters' => (object) $filters, 'rows' => $matrix,
            'language' => $export->language,
            'labels' => [
                'reference' => __('export.reference'), 'generatedBy' => __('export.generated_by'),
                'organisation' => __('export.organisation'), 'generatedAt' => __('export.generated_at'),
                'appliedFilters' => __('export.applied_filters'), 'none' => __('export.no_filters'),
                'recordSummary' => __('export.record_summary'), 'confidential' => __('export.confidential'),
                'page' => __('export.page'), 'records' => __('export.records'),
            ],
        ], JSON_THROW_ON_ERROR));
        $process = new Process([config('identity_verification.python_binary'), base_path('scripts/generate_secure_report.py'), $input, $output]);
        $process->setTimeout(60)->run();
        if (! $process->isSuccessful() || ! is_file($output)) {
            throw new RuntimeException('PDF_GENERATION_FAILED: '.str($process->getErrorOutput())->limit(500));
        }
        Storage::disk('local')->put($path, file_get_contents($output));
        @unlink($input);
        @unlink($output);
    }

    private function maskNric(?string $value): string
    {
        $digits = preg_replace('/\D/', '', $value ?? '');

        return strlen($digits) === 12 ? '******-**-'.substr($digits, -4) : __('value.not_available');
    }
}
