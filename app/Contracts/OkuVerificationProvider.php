<?php

namespace App\Contracts;

interface OkuVerificationProvider
{
    public function verifyQrPayload(string $payload): array;

    public function verifyRegistrationNumber(string $registrationNumber): array;
}
