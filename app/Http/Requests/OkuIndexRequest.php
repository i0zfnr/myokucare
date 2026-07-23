<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OkuIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin', 'jkm_officer') === true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])],
            'employment_status' => ['nullable', Rule::in(['Bekerja', 'Tidak Bekerja', 'Sendiri'])],
            'verification_status' => ['nullable', Rule::in(['Pending', 'Verified', 'Rejected'])],
            'age_min' => ['nullable', 'integer', 'min:1', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:1', 'max:120', 'gte:age_min'],
            'sort_by' => ['nullable', Rule::in(['name', 'ic_number', 'oku_category', 'employment_status', 'age', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search')) {
            $this->merge(['search' => trim((string) $this->input('search'))]);
        }
    }
}
