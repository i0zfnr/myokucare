<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExportAuditLog extends Model
{
    use HasUuids;

    protected $fillable = ['exported_by_user_id', 'exported_by_role', 'export_type', 'format', 'status', 'record_count', 'filters', 'fields_included', 'sensitive_fields_included', 'purpose', 'language', 'content_mode', 'ip_address', 'user_agent', 'generated_file_path', 'failure_reason', 'expires_at', 'downloaded_at'];

    protected function casts(): array
    {
        return ['filters' => 'array', 'fields_included' => 'array', 'sensitive_fields_included' => 'array', 'expires_at' => 'datetime', 'downloaded_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'exported_by_user_id');
    }
}
