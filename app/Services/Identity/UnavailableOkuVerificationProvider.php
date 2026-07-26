<?php

namespace App\Services\Identity;

use App\Contracts\OkuVerificationProvider;

class UnavailableOkuVerificationProvider implements OkuVerificationProvider
{
    public function verifyQrPayload(string $payload): array
    {
        return ['status' => 'UNVERIFIED_EXTERNAL_DATA', 'message' => 'No authorised JKM/SMOKU provider is configured.'];
    }

    public function verifyRegistrationNumber(string $registrationNumber): array
    {
        return ['status' => 'UNVERIFIED_EXTERNAL_DATA', 'message' => 'No authorised JKM/SMOKU provider is configured.'];
    }
}
