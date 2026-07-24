<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmploymentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin', 'jkm_officer') === true;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'category' => ['nullable', Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])],
            'gender' => ['nullable', Rule::in(['Lelaki', 'Perempuan'])],
            'age_min' => ['nullable', 'integer', 'min:1', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:1', 'max:120', 'gte:age_min'],
        ];
    }
}
