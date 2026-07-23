<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WelfareReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin', 'jkm_officer', 'viewer') === true;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(['Pending', 'Under Review', 'Approved', 'Rejected'])],
            'type' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])],
        ];
    }
}
