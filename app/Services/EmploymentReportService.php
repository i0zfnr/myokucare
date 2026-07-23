<?php

namespace App\Services;

use App\Models\Oku;
use App\Models\OkuEmployment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EmploymentReportService
{
    public function build(array $filters): array
    {
        $records = $this->applyFilters(Oku::query(), $filters)
            ->get(['id', 'oku_category', 'employment_status', 'gender', 'age', 'created_at']);

        $total = $records->count();
        $employed = $records->where('employment_status', 'Bekerja')->count();
        $selfEmployed = $records->where('employment_status', 'Sendiri')->count();
        $unemployed = $records->where('employment_status', 'Tidak Bekerja')->count();
        $working = $employed + $selfEmployed;

        $categories = collect(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])
            ->mapWithKeys(function (string $category) use ($records) {
                $items = $records->where('oku_category', $category);
                $working = $items->whereIn('employment_status', ['Bekerja', 'Sendiri'])->count();

                return [$category => [
                    'total' => $items->count(),
                    'working' => $working,
                    'unemployed' => $items->where('employment_status', 'Tidak Bekerja')->count(),
                    'rate' => $items->count() ? round(($working / $items->count()) * 100, 1) : 0,
                ]];
            });

        $ageGroups = collect([
            '18 tahun ke bawah' => fn ($age) => $age <= 18,
            '19–30 tahun' => fn ($age) => $age >= 19 && $age <= 30,
            '31–45 tahun' => fn ($age) => $age >= 31 && $age <= 45,
            '46–60 tahun' => fn ($age) => $age >= 46 && $age <= 60,
            '61 tahun ke atas' => fn ($age) => $age >= 61,
        ])->map(fn ($condition) => $records->filter(fn ($record) => $condition($record->age))->count());

        $months = collect(range(5, 0))->map(fn ($offset) => now()->startOfMonth()->subMonths($offset));
        $monthly = $months->map(fn (Carbon $month) => [
            'label' => $month->translatedFormat('M Y'),
            'total' => $records->filter(fn ($record) => $record->created_at->isSameMonth($month))->count(),
        ]);

        $activeEmployments = OkuEmployment::query()
            ->where('status', 'Active')
            ->whereHas('oku', fn (Builder $query) => $this->applyFilters($query, $filters))
            ->count();

        return [
            'summary' => [
                'total' => $total,
                'employed' => $employed,
                'self_employed' => $selfEmployed,
                'unemployed' => $unemployed,
                'working' => $working,
                'active_employments' => $activeEmployments,
                'employment_rate' => $total ? round(($working / $total) * 100, 1) : 0,
            ],
            'categories' => $categories,
            'age_groups' => $ageGroups,
            'gender' => [
                'Lelaki' => $records->where('gender', 'Lelaki')->count(),
                'Perempuan' => $records->where('gender', 'Perempuan')->count(),
            ],
            'monthly' => $monthly,
            'generated_at' => now(),
        ];
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->when(filled($filters['category'] ?? null), fn ($query) => $query->where('oku_category', $filters['category']))
            ->when(filled($filters['gender'] ?? null), fn ($query) => $query->where('gender', $filters['gender']))
            ->when(isset($filters['age_min']), fn ($query) => $query->where('age', '>=', $filters['age_min']))
            ->when(isset($filters['age_max']), fn ($query) => $query->where('age', '<=', $filters['age_max']));
    }
}
