<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubmissionTranslation extends Model
{
    protected $fillable = ['user_id', 'translatable_type', 'translatable_id', 'field_name', 'original_text', 'original_language', 'translated_text_bm', 'translated_text_en', 'translation_confidence', 'provider_status', 'translated_at'];

    protected function casts(): array
    {
        return [
            'original_text' => 'encrypted',
            'translated_text_bm' => 'encrypted',
            'translated_text_en' => 'encrypted',
            'translation_confidence' => 'decimal:4',
            'translated_at' => 'datetime',
        ];
    }

    public function translatable()
    {
        return $this->morphTo();
    }
}
