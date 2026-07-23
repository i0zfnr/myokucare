<?php

namespace App\Http\Requests;

use App\Services\AuditService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') === true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', Rule::in(array_keys(AuditService::ACTIONS))],
            'severity' => ['nullable', Rule::in(['info', 'warning'])],
            'date_from' => ['nullable', 'date', 'before_or_equal:today'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from', 'before_or_equal:today'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search')) {
            $this->merge(['search' => trim((string) $this->input('search'))]);
        }
    }
}
