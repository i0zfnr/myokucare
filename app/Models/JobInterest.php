<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobInterest extends Model
{
    protected $fillable = ['oku_id', 'job_id', 'status', 'notes', 'application_date', 'interview_date'];

    protected function casts(): array
    {
        return ['application_date' => 'date', 'interview_date' => 'date'];
    }

    public function oku()
    {
        return $this->belongsTo(Oku::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
