<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationDocument extends Model
{
    use SoftDeletes;

    protected $fillable = ['session_id', 'document_type', 'original_file_path', 'processed_file_path', 'quality_status', 'quality_score', 'quality_issues', 'processing_metadata'];

    protected function casts(): array
    {
        return ['quality_issues' => 'array', 'processing_metadata' => 'array', 'quality_score' => 'float'];
    }

    public function session()
    {
        return $this->belongsTo(VerificationSession::class, 'session_id');
    }

    public function fields()
    {
        return $this->hasMany(ExtractedField::class, 'document_id');
    }

    public function qrResults()
    {
        return $this->hasMany(QrScanResult::class, 'document_id');
    }
}
