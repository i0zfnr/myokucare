<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OkuEmployment extends Model
{
    use SoftDeletes;

    protected $fillable = ['oku_id', 'employer_id', 'job_id', 'job_title', 'department', 'employment_type', 'start_date', 'end_date', 'status', 'supervisor_name', 'salary_encrypted', 'notes', 'verification_status', 'verified_by_pegawai_id', 'verified_at', 'deleted_by_user_id', 'deletion_reason', 'deletion_notes'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'salary' => 'decimal:2', 'salary_encrypted' => 'encrypted', 'verified_at' => 'datetime'];
    }

    public function oku()
    {
        return $this->belongsTo(Oku::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function getSalaryValueAttribute(): ?string
    {
        return $this->salary_encrypted ?? ($this->salary !== null ? (string) $this->salary : null);
    }
}
