<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin', 'jkm_officer', 'employer', 'oku_user') === true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan', 'Semua'])],
            'job_category' => ['nullable', Rule::in(config('jobs.categories'))],
            'location' => ['nullable', Rule::in(config('besut.mukims'))],
            'employment_type' => ['nullable', Rule::in(['Sepenuh Masa', 'Separuh Masa', 'Kontrak', 'Sementara'])],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:9999999', 'gte:salary_min'],
            'sort_by' => ['nullable', Rule::in(['created_at', 'title', 'salary_min', 'application_deadline'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([9, 12, 24, 48])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search')) {
            $this->merge(['search' => trim((string) $this->input('search'))]);
        }
    }
}
