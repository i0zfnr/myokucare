<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['actor_id', 'subject_user_id', 'action', 'changes', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject()
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}
