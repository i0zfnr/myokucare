<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'employer_id', 'oku_id', 'mykad_verification_status', 'mykad_submitted_at', 'mykad_verified_at', 'mykad_verification_session_id', 'mykad_review_reason', 'mykad_resubmission_required', 'is_active', 'last_login_at', 'preferences', 'permissions', 'preferred_language'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'mykad_submitted_at' => 'datetime',
            'mykad_verified_at' => 'datetime',
            'mykad_resubmission_required' => 'boolean',
            'preferences' => 'array',
            'permissions' => 'array',
            'has_completed_guideline' => 'boolean',
            'guideline_completed_at' => 'datetime',
            'last_guideline_viewed_at' => 'datetime',
        ];
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function oku()
    {
        return $this->belongsTo(Oku::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function hasVerifiedMyKad(): bool
    {
        return $this->mykad_verification_status === 'VERIFIED';
    }

    public function verificationSessions()
    {
        return $this->hasMany(VerificationSession::class);
    }

    public function guidelineActivityLogs()
    {
        return $this->hasMany(GuidelineActivityLog::class);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => __('role.super_admin'),
            'jkm_officer' => __('role.jkm_officer'),
            'employer' => __('role.employer'),
            'oku_user' => __('role.oku_user'),
            default => __('role.oku_user'),
        };
    }
}
