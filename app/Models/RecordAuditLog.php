<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordAuditLog extends Model
{
    protected $fillable = ['performed_by_user_id', 'performed_by_role', 'action', 'entity_type', 'entity_id', 'previous_data', 'updated_data', 'deletion_reason', 'deletion_notes', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['previous_data' => 'encrypted:array', 'updated_data' => 'encrypted:array'];
    }
}
