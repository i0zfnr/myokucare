<?php

namespace App\Services;

use App\Models\RecordAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RecordAuditService
{
    public function log(Request $request, Model $model, string $action, array $before = [], array $after = [], ?string $reason = null, ?string $notes = null): void
    {
        RecordAuditLog::query()->create([
            'performed_by_user_id' => $request->user()->id,
            'performed_by_role' => $request->user()->role,
            'action' => $action,
            'entity_type' => class_basename($model),
            'entity_id' => $model->getKey(),
            'previous_data' => $this->protected($before),
            'updated_data' => $this->protected($after),
            'deletion_reason' => $reason,
            'deletion_notes' => $notes,
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(1000),
        ]);
    }

    private function protected(array $data): array
    {
        foreach (['ic_number', 'nric'] as $key) {
            if (isset($data[$key])) {
                $digits = preg_replace('/\D/', '', (string) $data[$key]);
                $data[$key] = strlen($digits) === 12 ? '******-**-'.substr($digits, -4) : '[MASKED]';
            }
        }
        unset($data['salary_encrypted'], $data['password'], $data['remember_token']);

        return $data;
    }
}
