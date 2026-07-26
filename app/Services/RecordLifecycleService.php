<?php

namespace App\Services;

use App\Models\Employer;
use App\Models\Oku;
use App\Models\OkuEmployment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RecordLifecycleService
{
    private const REASONS = ['Duplicate record', 'Incorrect registration', 'User request', 'Employer closed', 'Fraudulent record', 'Created by mistake', 'Data retention requirement', 'Other'];

    public function __construct(private PermissionService $permissions, private RecordAuditService $audit) {}

    public function softDelete(Request $request, Model $model, string $permission, string $name): void
    {
        $this->permissions->authorize($request->user(), $permission);
        $data = $request->validate([
            'reason' => ['required', 'in:'.implode(',', self::REASONS)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'confirmation_text' => ['required', 'string'],
        ]);
        if (! in_array($data['confirmation_text'], ['DELETE', $name], true)) {
            throw ValidationException::withMessages(['confirmation_text' => 'DELETE_CONFIRMATION_INVALID']);
        }
        $this->assertNoActiveDependencies($model);
        $before = $model->toArray();
        $metadata = [
            'deleted_by_user_id' => $request->user()->id,
            'deletion_reason' => $data['reason'],
            'deletion_notes' => $data['notes'] ?? null,
        ];
        if (! $model instanceof OkuEmployment) {
            $metadata['previous_status'] = $model->getAttribute('is_active') !== null ? ($model->is_active ? 'ACTIVE' : 'INACTIVE') : $model->getAttribute('status');
        }
        $model->forceFill($metadata)->save();
        $model->delete();
        $this->audit->log($request, $model, 'SOFT_DELETED', $before, [], $data['reason'], $data['notes'] ?? null);
    }

    public function restore(Request $request, Model $model): void
    {
        $this->permissions->authorize($request->user(), 'record.restore');
        $data = $request->validate(['restore_reason' => ['required', 'string', 'max:255']]);
        $model->restore();
        if (! $model instanceof OkuEmployment) {
            $model->forceFill([
                'restored_at' => now(),
                'restored_by_user_id' => $request->user()->id,
                'restore_reason' => $data['restore_reason'],
                'is_active' => $model->getAttribute('is_active') !== null ? $model->previous_status === 'ACTIVE' : $model->getAttribute('is_active'),
            ])->save();
        }
        $this->audit->log($request, $model, 'RESTORED', [], $model->toArray());
    }

    public function permanentDelete(Request $request, Model $model): void
    {
        $this->permissions->authorize($request->user(), 'record.permanent_delete');
        abort_unless($model->trashed(), 409, 'PERMANENT_DELETE_NOT_ALLOWED');
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'confirmation_text' => ['required', 'in:PERMANENTLY DELETE'],
            'password' => ['required', 'string'],
        ]);
        abort_unless(Hash::check($data['password'], $request->user()->password), 403, 'REAUTHENTICATION_REQUIRED');
        $this->assertNoDependencies($model);
        $this->audit->log($request, $model, 'PERMANENTLY_DELETED', $model->toArray(), [], $data['reason']);
        $model->forceDelete();
    }

    private function assertNoActiveDependencies(Model $model): void
    {
        $active = match (true) {
            $model instanceof Employer => $model->employments()->whereIn('status', ['ACTIVE', 'Active'])->count(),
            $model instanceof Oku => $model->employments()->whereIn('status', ['ACTIVE', 'Active'])->count(),
            default => 0,
        };
        if ($active > 0) {
            throw ValidationException::withMessages(['record' => "ACTIVE_RELATIONSHIPS_EXIST: {$active}"]);
        }
    }

    private function assertNoDependencies(Model $model): void
    {
        $count = match (true) {
            $model instanceof Employer => $model->employments()->withTrashed()->count(),
            $model instanceof Oku => $model->employments()->withTrashed()->count(),
            default => 0,
        };
        abort_if($count > 0, 409, 'RELATED_RECORDS_REQUIRE_REVIEW');
    }
}
