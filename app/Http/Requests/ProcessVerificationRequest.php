<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('oku_user') === true;
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array'],
            'documents.*.text' => ['nullable', 'string', 'max:10000'],
            'documents.*.confidence' => ['nullable', 'numeric', 'between:0,1'],
            'documents.*.edited' => ['nullable', 'boolean'],
            'qr.payload' => ['nullable', 'string', 'max:4096'],
            'qr.confidence' => ['nullable', 'numeric', 'between:0,1'],
        ];
    }
}
