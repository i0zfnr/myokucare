<?php

namespace App\Services\Identity;

class IdentityComparisonService
{
    public function __construct(private IdentityNormalizer $normalizer) {}

    public function compare(array $oku, array $mykad): array
    {
        $okuNric = $this->normalizer->nric($oku['nric']['value'] ?? null);
        $mykadNric = $this->normalizer->nric($mykad['nric']['value'] ?? null);
        $okuName = $this->normalizer->name($oku['name']['value'] ?? null);
        $mykadName = $this->normalizer->name($mykad['name']['value'] ?? null);
        $nricMatch = strlen($okuNric) === 12 && hash_equals($okuNric, $mykadNric);
        $nameSimilarity = $this->similarity($okuName, $mykadName);
        $nameMatch = $nameSimilarity >= config('identity_verification.min_name_similarity');
        $confidence = min(
            (float) ($oku['nric']['confidence'] ?? 0),
            (float) ($oku['name']['confidence'] ?? 0),
            (float) ($mykad['nric']['confidence'] ?? 0),
            (float) ($mykad['name']['confidence'] ?? 0),
        );
        $reasons = [];

        if ($confidence < config('identity_verification.min_ocr_confidence')) {
            $result = 'MANUAL_REVIEW_REQUIRED';
            $reasons[] = 'LOW_OCR_CONFIDENCE';
        } elseif ($nricMatch && $nameMatch) {
            $result = 'VERIFIED_LOCALLY_ONLY';
        } elseif ($nricMatch) {
            $result = 'MANUAL_REVIEW_REQUIRED';
            $reasons[] = 'NAME_MISMATCH';
        } else {
            $result = 'DETAILS_MISMATCH';
            $reasons[] = 'NRIC_MISMATCH';
            if (! $nameMatch) {
                $reasons[] = 'NAME_MISMATCH';
            }
        }

        return compact('nricMatch', 'nameMatch', 'nameSimilarity', 'result', 'reasons') + [
            'normalisedValues' => [
                'oku' => ['name' => $okuName, 'nric' => $okuNric],
                'mykad' => ['name' => $mykadName, 'nric' => $mykadNric],
            ],
        ];
    }

    public function similarity(string $left, string $right): float
    {
        if ($left === '' || $right === '') {
            return 0.0;
        }
        if ($left === $right) {
            return 1.0;
        }
        $max = max(mb_strlen($left), mb_strlen($right));

        return round(max(0, 1 - levenshtein($left, $right) / $max), 4);
    }
}
