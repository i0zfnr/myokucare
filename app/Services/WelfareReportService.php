<?php

namespace App\Services;

use App\Models\WelfareApplication;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class WelfareReportService
{
    public function build(array $filters): array
    {
        $records = $this->applyFilters(WelfareApplication::query(), $filters)
            ->with('oku:id,oku_category')
            ->get(['id', 'oku_id', 'application_type', 'status', 'application_date', 'review_date', 'next_review_date', 'created_at']);

        $statuses = collect(['Pending', 'Under Review', 'Approved', 'Rejected'])
            ->mapWithKeys(fn ($status) => [$status => $records->where('status', $status)->count()]);
        $total = $records->count();
        $decided = $statuses['Approved'] + $statuses['Rejected'];

        $types = $records->groupBy('application_type')
            ->map(fn ($items) => [
                'total' => $items->count(),
                'approved' => $items->where('status', 'Approved')->count(),
                'percentage' => $total ? round(($items->count() / $total) * 100, 1) : 0,
            ])
            ->sortByDesc('total')
            ->take(8);

        $categories = collect(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])
            ->mapWithKeys(function ($category) use ($records, $total) {
                $count = $records->filter(fn ($record) => $record->oku?->oku_category === $category)->count();

                return [$category => [
                    'total' => $count,
                    'percentage' => $total ? round(($count / $total) * 100, 1) : 0,
                ]];
            });

        $months = collect(range(5, 0))->map(fn ($offset) => now()->startOfMonth()->subMonths($offset));
        $monthly = $months->map(function (Carbon $month) use ($records) {
            $items = $records->filter(fn ($record) => $record->application_date->isSameMonth($month));

            return [
                'label' => $month->translatedFormat('M Y'),
                'total' => $items->count(),
                'approved' => $items->where('status', 'Approved')->count(),
            ];
        });

        $processed = $records->filter(fn ($record) => $record->review_date && in_array($record->status, ['Approved', 'Rejected'], true));
        $averageDays = $processed->count()
            ? round($processed->avg(fn ($record) => $record->application_date->diffInDays($record->review_date)), 1)
            : 0;

        return [
            'summary' => [
                'total' => $total,
                'pending' => $statuses['Pending'],
                'under_review' => $statuses['Under Review'],
                'approved' => $statuses['Approved'],
                'rejected' => $statuses['Rejected'],
                'approval_rate' => $decided ? round(($statuses['Approved'] / $decided) * 100, 1) : 0,
                'average_processing_days' => $averageDays,
                'overdue_reviews' => $records->filter(fn ($record) => in_array($record->status, ['Pending', 'Under Review'], true) && $record->next_review_date?->isPast())->count(),
            ],
            'statuses' => $statuses,
            'types' => $types,
            'categories' => $categories,
            'monthly' => $monthly,
            'generated_at' => now(),
        ];
    }

    public function availableTypes(): mixed
    {
        return WelfareApplication::query()->distinct()->orderBy('application_type')->pluck('application_type');
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('application_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('application_date', '<=', $filters['date_to']))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['type'] ?? null), fn ($query) => $query->where('application_type', $filters['type']))
            ->when(filled($filters['category'] ?? null), fn ($query) => $query->whereHas('oku', fn ($query) => $query->where('oku_category', $filters['category'])));
    }
}
