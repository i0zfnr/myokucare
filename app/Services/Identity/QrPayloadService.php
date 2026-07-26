<?php

namespace App\Services\Identity;

class QrPayloadService
{
    public function classify(string $payload): string
    {
        $payload = trim($payload);
        if ($payload === '') {
            return 'INVALID';
        }
        if (filter_var($payload, FILTER_VALIDATE_URL)) {
            return 'URL';
        }
        json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return 'STRUCTURED_JSON';
        }
        if (preg_match('/^[A-Z]{1,5}\d{6,20}$/i', $payload)) {
            return 'REGISTRATION_IDENTIFIER';
        }
        if (preg_match('/^[A-Za-z0-9._~-]{20,}$/', $payload)) {
            return 'VERIFICATION_TOKEN';
        }

        return 'PLAIN_TEXT';
    }
}
