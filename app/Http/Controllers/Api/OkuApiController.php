<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobInterest;
use App\Models\Oku;
use App\Services\JobMatchingService;
use App\Services\OkuDataService;
use Illuminate\Http\Request;

class OkuApiController extends Controller
{
    public function getOkuData(Request $r)
    {
        return response()->json(Oku::query()->findOrFail($r->integer('oku_id')));
    }

    public function getMatchingJobs(Request $r, JobMatchingService $s)
    {
        return response()->json($s->findMatchingJobs(Oku::query()->findOrFail($r->integer('oku_id'))));
    }

    public function getJobRecommendations(Request $r, JobMatchingService $s)
    {
        return response()->json($s->getSmartRecommendations(Oku::query()->findOrFail($r->integer('oku_id'))));
    }

    public function getEmploymentStats(OkuDataService $s)
    {
        return response()->json($s->dashboard());
    }

    public function submitJobInterest(Request $r)
    {
        $d = $r->validate(['oku_id' => 'required|exists:okus,id', 'job_id' => 'required|exists:jobs,id', 'notes' => 'nullable|string']);
        $interest = JobInterest::query()->updateOrCreate(['oku_id' => $d['oku_id'], 'job_id' => $d['job_id']], $d);
        if ($interest->wasRecentlyCreated) {
            Job::query()->whereKey($d['job_id'])->increment('applications_count', 1, []);
        }

        return response()->json($interest, $interest->wasRecentlyCreated ? 201 : 200);
    }
}
