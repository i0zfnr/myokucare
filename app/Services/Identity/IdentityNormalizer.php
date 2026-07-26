<?php

namespace App\Services\Identity;

class IdentityNormalizer
{
    public function nric(?string $value): string
    {
        return preg_replace('/\D+/', '', $value ?? '') ?? '';
    }

    public function name(?string $value): string
    {
        $value = mb_strtoupper($value ?? '', 'UTF-8');
        $value = preg_replace('/[.,]/u', '', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    public function maskNric(?string $value): string
    {
        $nric = $this->nric($value);

        return strlen($nric) === 12 ? '******-**-'.substr($nric, -4) : 'Tidak tersedia';
    }
}
