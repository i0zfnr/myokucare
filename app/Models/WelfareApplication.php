<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WelfareApplication extends Model
{
    protected $fillable = ['oku_id', 'application_type', 'status', 'application_date', 'review_date', 'notes', 'rejection_reason', 'reviewed_by', 'next_review_date'];

    public function translations()
    {
        return $this->morphMany(UserSubmissionTranslation::class, 'translatable');
    }

    protected function casts(): array
    {
        return ['application_date' => 'date', 'review_date' => 'date', 'next_review_date' => 'date'];
    }

    public function oku()
    {
        return $this->belongsTo(Oku::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reviewSchedules()
    {
        return $this->hasMany(ReviewSchedule::class);
    }
}
