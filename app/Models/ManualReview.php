<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualReview extends Model
{
    protected $fillable = ['session_id', 'status', 'reason_codes', 'reviewer_id', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reason_codes' => 'array', 'reviewed_at' => 'datetime'];
    }

    public function session()
    {
        return $this->belongsTo(VerificationSession::class, 'session_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
