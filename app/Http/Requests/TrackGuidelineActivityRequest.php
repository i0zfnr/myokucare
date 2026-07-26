<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackGuidelineActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['OPENED', 'COMPLETED', 'SKIPPED', 'REPLAYED'])],
            'device_type' => ['required', Rule::in(['WEB', 'PWA'])],
        ];
    }
}
