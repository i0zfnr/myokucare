<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OkuEmployment extends Model
{
    protected $fillable = ['oku_id', 'job_id', 'start_date', 'end_date', 'status', 'salary', 'notes'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'salary' => 'decimal:2'];
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
