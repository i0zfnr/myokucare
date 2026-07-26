<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtractedField extends Model
{
    protected $fillable = ['document_id', 'field_name', 'encrypted_value', 'masked_value', 'confidence', 'source'];

    protected function casts(): array
    {
        return ['encrypted_value' => 'encrypted', 'confidence' => 'float'];
    }

    public function document()
    {
        return $this->belongsTo(VerificationDocument::class);
    }
}
