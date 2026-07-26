<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VerificationSession extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'status', 'expires_at', 'consent_accepted_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consent_accepted_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(VerificationDocument::class, 'session_id');
    }

    public function comparison()
    {
        return $this->hasOne(IdentityComparison::class, 'session_id');
    }

    public function manualReview()
    {
        return $this->hasOne(ManualReview::class, 'session_id');
    }
}
