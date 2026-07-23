<?php

namespace App\Services;

use App\Models\Oku;
use App\Models\OkuEmployment;
use App\Models\WelfareApplication;

class OkuDataService
{
    public function getStats(): array
    {
        return [
            'total' => Oku::query()->count('*'),
            'active' => Oku::query()->where('is_active', true)->count('*'),
            'employed' => Oku::query()->where('employment_status', 'Bekerja')->count('*'),
            'unemployed' => Oku::query()->where('employment_status', 'Tidak Bekerja')->count('*'),
            'pending_verification' => Oku::query()->where('verification_status', 'Pending')->count('*'),
            'verified' => Oku::query()->where('verification_status', 'Verified')->count('*'),
            'categories' => Oku::query()->selectRaw('oku_category, COUNT(*) AS total')->groupBy('oku_category')->pluck('total', 'oku_category')->all(),
        ];
    }

    public function getEmploymentHistory(Oku $oku): array
    {
        return [
            'total_employments' => $oku->employments()->count('*'),
            'currently_employed' => $oku->employments()->where('status', 'Active')->whereNull('end_date')->exists(),
            'latest_employment' => $oku->employments()->with('job.employer')->latest('start_date')->first(),
        ];
    }

    public function dashboard(): array
    {
        return $this->getStats() + [
            'active_employments' => OkuEmployment::query()->where('status', 'Active')->count('*'),
            'pending_welfare' => WelfareApplication::query()->whereIn('status', ['Pending', 'Under Review'])->count('*'),
        ];
    }

    public function jkmMetrics(array $stats): array
    {
        return [
            ['label' => 'Jumlah OKU', 'value' => $stats['total'], 'key' => 'total', 'icon' => 'id-card', 'tone' => 'coral', 'caption' => 'Rekod berdaftar'],
            ['label' => 'Belum Bekerja', 'value' => $stats['unemployed'], 'key' => 'unemployed', 'icon' => 'job-search', 'tone' => 'amber', 'caption' => 'Perlu sokongan kerjaya'],
            ['label' => 'Permohonan Tertunda', 'value' => $stats['pending_welfare'], 'key' => 'pending_welfare', 'icon' => 'welfare', 'tone' => 'purple', 'caption' => 'Menunggu tindakan'],
            ['label' => 'Pekerjaan Aktif', 'value' => $stats['active_employments'], 'key' => 'active_employments', 'icon' => 'briefcase', 'tone' => 'green', 'caption' => 'Penempatan semasa'],
        ];
    }
}
