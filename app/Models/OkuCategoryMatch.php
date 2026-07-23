<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OkuCategoryMatch extends Model
{
    protected $fillable = ['oku_category', 'job_category', 'match_score', 'notes'];

    protected function casts(): array
    {
        return ['match_score' => 'integer'];
    }
}
