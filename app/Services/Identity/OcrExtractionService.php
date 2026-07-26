<?php

namespace App\Services\Identity;

class OcrExtractionService
{
    public function extract(string $text, float $confidence, string $documentType): array
    {
        $clean = preg_replace('/[ \t]+/', ' ', mb_strtoupper($text, 'UTF-8')) ?? '';
        preg_match('/\b\d{6}[-\s]?\d{2}[-\s]?\d{4}\b/', $clean, $nricMatch);
        $lines = collect(preg_split('/\R+/', $clean))
            ->map(fn ($line) => trim($line))
            ->filter();
        $excluded = ['MALAYSIA', 'KAD PENGENALAN', 'IDENTITY CARD', 'KAD OKU', 'ORANG KURANG UPAYA'];
        $name = $lines->first(fn ($line) => strlen($line) >= 5
            && preg_match('/^[A-Z @\/\'.-]+$/u', $line)
            && ! collect($excluded)->contains(fn ($word) => str_contains($line, $word)));

        $fields = [];
        if ($name) {
            $fields['name'] = ['value' => $name, 'confidence' => $confidence];
        }
        if ($nricMatch) {
            $fields['nric'] = ['value' => $nricMatch[0], 'confidence' => $confidence];
        }

        if (str_starts_with($documentType, 'oku_')) {
            preg_match('/\b[A-Z]{1,4}\d{7,14}\b/', $clean, $registration);
            if ($registration) {
                $fields['oku_registration_number'] = ['value' => $registration[0], 'confidence' => $confidence];
            }
        }

        return $fields;
    }
}
