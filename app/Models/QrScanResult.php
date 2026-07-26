<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrScanResult extends Model
{
    protected $fillable = ['document_id', 'encrypted_payload', 'payload_type', 'detection_confidence', 'provider_status'];

    protected function casts(): array
    {
        return ['encrypted_payload' => 'encrypted', 'detection_confidence' => 'float'];
    }
}
