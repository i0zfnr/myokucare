<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewSchedule extends Model
{
    protected $fillable = ['welfare_application_id', 'scheduled_date', 'status', 'notes', 'completed_date', 'review_findings'];

    protected function casts(): array
    {
        return ['scheduled_date' => 'date', 'completed_date' => 'date'];
    }

    public function welfareApplication()
    {
        return $this->belongsTo(WelfareApplication::class);
    }
}
