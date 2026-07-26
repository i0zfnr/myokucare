<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityComparison extends Model
{
    protected $fillable = ['session_id', 'nric_match', 'name_match', 'name_similarity', 'result', 'reason_codes', 'normalised_values'];

    protected function casts(): array
    {
        return ['nric_match' => 'boolean', 'name_match' => 'boolean', 'name_similarity' => 'float', 'reason_codes' => 'array', 'normalised_values' => 'encrypted:array'];
    }
}
