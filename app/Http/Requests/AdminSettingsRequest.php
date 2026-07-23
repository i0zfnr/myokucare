<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin', 'jkm_officer') === true;
    }

    public function rules(): array
    {
        return [
            'font_scale' => ['required', Rule::in(['100', '112.5', '125', '137.5'])],
            'dashboard_refresh_seconds' => ['required', 'integer', Rule::in([10, 30, 60, 120])],
            'default_page_size' => ['required', 'integer', Rule::in([10, 15, 25, 50])],
            'high_contrast_default' => ['required', 'boolean'],
            'compact_sidebar' => ['required', 'boolean'],
            'show_help_panel' => ['required', 'boolean'],
            'email_case_notifications' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['high_contrast_default', 'compact_sidebar', 'show_help_panel', 'email_case_notifications'] as $field) {
            $this->merge([$field => $this->boolean($field)]);
        }
    }
}
