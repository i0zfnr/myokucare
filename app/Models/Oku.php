<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Oku extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'ic_number', 'gender', 'age', 'marital_status', 'address', 'education_level', 'oku_card_number', 'oku_category', 'employment_status', 'job_name', 'assistance_type', 'career_summary', 'skills', 'availability_status', 'resume_path', 'oku_card_image_path', 'verification_status', 'verification_notes', 'verified_at', 'verified_by', 'phone_number', 'email', 'has_smartphone', 'has_internet', 'emergency_contact_name', 'emergency_contact_phone', 'profile_photo_path', 'is_active'];

    protected function casts(): array
    {
        return ['age' => 'integer', 'has_smartphone' => 'boolean', 'has_internet' => 'boolean', 'is_active' => 'boolean', 'verified_at' => 'datetime'];
    }

    public function employments()
    {
        return $this->hasMany(OkuEmployment::class);
    }

    public function welfareApplications()
    {
        return $this->hasMany(WelfareApplication::class);
    }

    public function jobInterests()
    {
        return $this->hasMany(JobInterest::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function activeEmployment()
    {
        return $this->hasOne(OkuEmployment::class)->where('status', 'Active')->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('oku_category', $category);
    }

    public function scopeEmployed(Builder $query): Builder
    {
        return $query->where('employment_status', 'Bekerja');
    }

    public function scopeUnemployed(Builder $query): Builder
    {
        return $query->where('employment_status', 'Tidak Bekerja');
    }

    public function getAgeRangeAttribute(): string
    {
        return match (true) {
            $this->age < 18 => 'Under 18', $this->age < 30 => '18-29', $this->age < 45 => '30-44', $this->age < 60 => '45-59', default => '60+'
        };
    }
}
