<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['employer_id', 'title', 'description', 'requirements', 'responsibilities', 'oku_category_suitable', 'salary_min', 'salary_max', 'location', 'working_hours', 'employment_type', 'application_deadline', 'is_active', 'views_count', 'applications_count'];

    protected function casts(): array
    {
        return ['salary_min' => 'decimal:2', 'salary_max' => 'decimal:2', 'application_deadline' => 'date', 'is_active' => 'boolean', 'views_count' => 'integer', 'applications_count' => 'integer'];
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function okuEmployments()
    {
        return $this->hasMany(OkuEmployment::class);
    }

    public function jobInterests()
    {
        return $this->hasMany(JobInterest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategorySuitable(Builder $query, string $category): Builder
    {
        return $query->where(fn (Builder $q) => $q->where('oku_category_suitable', $category)->orWhere('oku_category_suitable', 'Semua'));
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereDate('application_deadline', '>=', today())->orWhereNull('application_deadline'));
    }

    public function getSalaryRangeAttribute(): string
    {
        return $this->salary_max ? 'RM '.number_format((float) $this->salary_min, 2).' - RM '.number_format((float) $this->salary_max, 2) : 'RM '.number_format((float) $this->salary_min, 2).'+';
    }
}
