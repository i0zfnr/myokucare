<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    public const ALL = [
        'employer.create', 'employer.view', 'employer.update', 'employer.delete',
        'oku_user.create', 'oku_user.view', 'oku_user.update', 'oku_user.delete',
        'employment.create', 'employment.view', 'employment.update', 'employment.delete',
        'employer.export', 'oku_user.export', 'employment.export', 'report.generate',
        'record.restore', 'record.permanent_delete', 'sensitive_data.view',
        'sensitive_data.export', 'identity_document.view',
    ];

    private const DEFAULTS = [
        'jkm_officer' => [
            'employer.create', 'employer.view', 'employer.update',
            'oku_user.create', 'oku_user.view', 'oku_user.update',
            'employment.create', 'employment.view', 'employment.update',
            'employer.export', 'oku_user.export', 'employment.export', 'report.generate',
        ],
        'oku_user' => ['employer.view', 'employer.export', 'employment.view', 'employment.export'],
        'employer' => ['employer.view', 'oku_user.view', 'oku_user.export', 'employment.view', 'employment.export'],
    ];

    public function permissions(User $user): array
    {
        if ($user->hasRole('super_admin')) {
            return self::ALL;
        }

        return $user->permissions ?? self::DEFAULTS[$user->role] ?? [];
    }

    public function allows(User $user, string $permission): bool
    {
        return in_array($permission, $this->permissions($user), true);
    }

    public function authorize(User $user, string $permission): void
    {
        abort_unless($this->allows($user, $permission), 403, 'PERMISSION_DENIED');
    }
}
