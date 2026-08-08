<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Throwable;

class FeatureManager
{
    public const IDENTITY_VERIFICATION = 'identity_verification_enabled';

    public const BESUT_ONLY_LOCATION_SCOPE = 'besut_only_location_scope_enabled';

    public function identityVerificationEnabled(): bool
    {
        return $this->enabled(self::IDENTITY_VERIFICATION, true);
    }

    public function besutOnlyLocationScopeEnabled(): bool
    {
        return $this->enabled(self::BESUT_ONLY_LOCATION_SCOPE, true);
    }

    public function enabled(string $key, bool $default = false): bool
    {
        try {
            return (bool) Cache::rememberForever(
                "system-feature:{$key}",
                fn () => SystemSetting::query()->find($key)?->value ?? $default,
            );
        } catch (Throwable) {
            return $default;
        }
    }

    public function set(string $key, bool $enabled, int $updatedBy): void
    {
        SystemSetting::query()->updateOrCreate(['key' => $key], [
            'value' => $enabled,
            'updated_by' => $updatedBy,
        ]);
        Cache::forget("system-feature:{$key}");
    }
}
