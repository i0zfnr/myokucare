<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Oku;
use Illuminate\Support\Collection;

class JobMatchingService
{
    public function findMatchingJobs(Oku $oku, int $limit = 20): Collection
    {
        return Job::query()
            ->with('employer')
            ->active()
            ->notExpired()
            ->inBesut()
            ->byCategorySuitable($oku->oku_category)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (Job $job) use ($oku) {
                $job->setAttribute('match_score', $this->score($oku, $job));

                return $job;
            })
            ->sortByDesc('match_score')
            ->values();
    }

    public function getSmartRecommendations(Oku $oku, int $limit = 10): Collection
    {
        return $this->findMatchingJobs($oku, max($limit * 3, 20))->take($limit)->values();
    }

    private function score(Oku $oku, Job $job): int
    {
        $score = $job->oku_category_suitable === $oku->oku_category ? 70 : 50;
        if ($job->oku_category_suitable === 'Semua') {
            $score += 10;
        }
        if ($oku->employment_status === 'Tidak Bekerja') {
            $score += 10;
        }
        if ($job->application_deadline?->isAfter(now()->addWeeks(2))) {
            $score += 5;
        }

        return min($score, 100);
    }
}
