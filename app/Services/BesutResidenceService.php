<?php

namespace App\Services;

use App\Models\Oku;
use App\Models\User;

class BesutResidenceService
{
    public function __construct(private FeatureManager $features) {}

    public const SPECIAL_CARD_LOCATIONS = [
        'OUTSIDE_BESUT' => 'Alamat di luar Daerah Besut',
        'UNREADABLE' => 'Alamat tidak dapat dibaca atau dipastikan',
    ];

    public function restrictedToBesut(): bool
    {
        return $this->features->besutOnlyLocationScopeEnabled();
    }

    public function declaration(array $data, bool $creating = false): array
    {
        if ($this->restrictedToBesut()) {
            $data['residential_state'] = config('besut.state');
            $data['residential_district'] = config('besut.district');
        }

        if ($creating) {
            $data['residence_verification_status'] = $this->isBesutLocation($data) ? 'UNVERIFIED' : 'NOT_APPLICABLE';
        }

        return $data;
    }

    public function isBesutLocation(array|Oku $location): bool
    {
        $state = $location instanceof Oku ? $location->residential_state : ($location['residential_state'] ?? null);
        $district = $location instanceof Oku ? $location->residential_district : ($location['residential_district'] ?? null);

        return strcasecmp((string) $state, (string) config('besut.state')) === 0
            && strcasecmp((string) $district, (string) config('besut.district')) === 0;
    }

    public function resetIfLocationChanged(Oku $oku, array $data): array
    {
        $locationFields = ['address', 'residential_state', 'residential_district', 'residential_mukim', 'residential_village', 'residential_postcode'];
        $changed = collect($locationFields)->contains(
            fn (string $field) => array_key_exists($field, $data)
                && (string) $oku->getAttribute($field) !== (string) $data[$field],
        );

        if (! $changed) {
            return $data;
        }

        return array_merge($data, [
            'residence_verification_status' => $this->isBesutLocation($data) ? 'UNVERIFIED' : 'NOT_APPLICABLE',
            'card_address' => null,
            'card_mukim' => null,
            'residence_verification_notes' => null,
            'residence_verified_at' => null,
            'residence_verified_by' => null,
        ]);
    }

    public function verifyFromCard(Oku $oku, User $officer, array $data): string
    {
        if (! $this->isBesutLocation($oku)) {
            $oku->forceFill([
                'card_address' => null,
                'card_mukim' => null,
                'residence_verification_status' => 'NOT_APPLICABLE',
                'residence_verification_notes' => null,
                'residence_verified_at' => null,
                'residence_verified_by' => null,
            ])->save();

            return 'NOT_APPLICABLE';
        }

        $status = match (true) {
            $data['verification_status'] !== 'Verified' => 'MANUAL_REVIEW',
            $data['card_mukim'] === 'UNREADABLE' => 'MANUAL_REVIEW',
            $data['card_mukim'] === 'OUTSIDE_BESUT' => 'OUTSIDE_BESUT',
            $data['card_mukim'] === $oku->residential_mukim => 'VERIFIED',
            default => 'MISMATCH',
        };

        $oku->forceFill([
            'card_address' => $data['card_address'],
            'card_mukim' => $data['card_mukim'],
            'residence_verification_status' => $status,
            'residence_verification_notes' => $data['residence_verification_notes'] ?? null,
            'residence_verified_at' => now(),
            'residence_verified_by' => $officer->id,
        ])->save();

        return $status;
    }
}
