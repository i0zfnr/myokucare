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
            'total' => Oku::count(),
            'active' => Oku::active()->count(),
            'employed' => Oku::employed()->count(),
            'unemployed' => Oku::unemployed()->count(),
            'categories' => Oku::query()->selectRaw('oku_category, COUNT(*) AS total')->groupBy('oku_category')->pluck('total', 'oku_category')->all(),
        ];
    }

    public function getEmploymentHistory(Oku $oku): array
    {
        return [
            'total_employments' => $oku->employments()->count(),
            'currently_employed' => $oku->employments()->where('status', 'Active')->whereNull('end_date')->exists(),
            'latest_employment' => $oku->employments()->with('job.employer')->latest('start_date')->first(),
        ];
    }

    public function dashboard(): array
    {
        return $this->getStats() + [
            'active_employments' => OkuEmployment::where('status', 'Active')->count(),
            'pending_welfare' => WelfareApplication::whereIn('status', ['Pending', 'Under Review'])->count(),
        ];
    }
}
