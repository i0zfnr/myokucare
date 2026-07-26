<?php

namespace App\Contracts;

interface TranslationProvider
{
    public function available(): bool;

    public function detectLanguage(string $text): array;

    public function translate(string $text, string $sourceLanguage, string $targetLanguage): array;
}
