<?php

namespace App\Services;

use App\Models\Employer;
use App\Models\Oku;
use App\Models\OkuEmployment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RecordAccessService
{
    private const AUTHORISED_RELATIONSHIP_STATUSES = ['ACTIVE', 'INACTIVE', 'TERMINATED', 'Active', 'Inactive', 'Resigned', 'Terminated', 'Completed'];

    public function __construct(private PermissionService $permissions) {}

    public function employers(User $user): Builder
    {
        $this->permissions->authorize($user, 'employer.view');
        $query = Employer::query();
        if ($user->hasRole('super_admin', 'jkm_officer')) {
            return $query;
        }
        if ($user->hasRole('employer')) {
            return $query->whereKey($user->employer_id);
        }

        return $query->whereHas('employments', fn (Builder $q) => $q
            ->where('oku_id', $user->oku_id)
            ->whereIn('status', self::AUTHORISED_RELATIONSHIP_STATUSES));
    }

    public function okus(User $user): Builder
    {
        $this->permissions->authorize($user, 'oku_user.view');
        $query = Oku::query();
        if ($user->hasRole('super_admin', 'jkm_officer')) {
            return $query;
        }
        if ($user->hasRole('oku_user')) {
            return $query->whereKey($user->oku_id);
        }

        return $query->whereHas('employments', fn (Builder $q) => $q
            ->where('employer_id', $user->employer_id)
            ->whereIn('status', self::AUTHORISED_RELATIONSHIP_STATUSES));
    }

    public function employments(User $user): Builder
    {
        $this->permissions->authorize($user, 'employment.view');
        $query = OkuEmployment::query();
        if ($user->hasRole('super_admin', 'jkm_officer')) {
            return $query;
        }
        if ($user->hasRole('oku_user')) {
            return $query->where('oku_id', $user->oku_id);
        }

        return $query->where('employer_id', $user->employer_id);
    }

    public function authorizeEmployer(User $user, Employer $employer): void
    {
        abort_unless($this->employers($user)->whereKey($employer)->exists(), 403);
    }

    public function authorizeOku(User $user, Oku $oku): void
    {
        abort_unless($this->okus($user)->whereKey($oku)->exists(), 403);
    }

    public function authorizeEmployment(User $user, OkuEmployment $employment): void
    {
        abort_unless($this->employments($user)->whereKey($employment)->exists(), 403);
    }
}
