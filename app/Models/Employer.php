<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_name', 'registration_number', 'address', 'industry_sector', 'contact_person', 'phone_number', 'email', 'website', 'company_description', 'number_of_employees', 'has_oku_quota', 'is_active', 'logo_path'];

    protected function casts(): array
    {
        return ['number_of_employees' => 'integer', 'has_oku_quota' => 'boolean', 'is_active' => 'boolean'];
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function activeJobs()
    {
        return $this->hasMany(Job::class)->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
