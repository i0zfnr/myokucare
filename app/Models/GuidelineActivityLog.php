<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidelineActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'language',
        'device_type',
        'guideline_version',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
