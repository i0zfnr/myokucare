<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Job;
use App\Models\JobInterest;
use App\Models\ReviewSchedule;
use App\Models\User;
use App\Models\WelfareApplication;
use App\Services\JobMatchingService;
use App\Services\OkuDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, OkuDataService $service)
    {
        $user = $request->user();

        return match ($user->role) {
            'super_admin' => $this->admin($service),
            'jkm_officer' => $this->jkm($service),
            'employer' => $this->employer($user),
            'oku_user' => $this->oku($user),
            'family_member' => $this->family($user),
            default => $this->viewer($service),
        };
    }

    public function statistics(OkuDataService $service): JsonResponse
    {
        $stats = $service->dashboard();

        return response()->json([
            'total' => $stats['total'],
            'active' => $stats['active'],
            'employed' => $stats['employed'],
            'unemployed' => $stats['unemployed'],
            'active_employments' => $stats['active_employments'],
            'pending_welfare' => $stats['pending_welfare'],
            'total_users' => User::query()->count('*'),
            'total_employers' => Employer::query()->count('*'),
            'open_jobs' => $this->openJobsCount(),
            'employment_rate' => $stats['total'] > 0
                ? (int) round(($stats['employed'] / $stats['total']) * 100)
                : 0,
            'categories' => $stats['categories'],
            'updated_at' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    private function admin(OkuDataService $service)
    {
        return view('dashboard.admin', [
            'stats' => $service->dashboard(),
            'totalUsers' => User::query()->count('*'),
            'totalEmployers' => Employer::query()->count('*'),
            'openJobs' => $this->openJobsCount(),
            'roles' => User::query()->selectRaw('role, COUNT(*) AS total', [])->groupBy('role')->pluck('total', 'role'),
        ]);
    }

    private function jkm(OkuDataService $service)
    {
        $stats = $service->dashboard();

        return view('dashboard.jkm', [
            'stats' => $stats,
            'metrics' => $service->jkmMetrics($stats),
            'pendingApplications' => WelfareApplication::query()->with('oku')->whereIn('status', ['Pending', 'Under Review'])->latest('application_date')->limit(5)->get(),
            'upcomingReviews' => ReviewSchedule::query()->with('welfareApplication.oku')->where('status', 'Pending')->whereDate('scheduled_date', '>=', today())->orderBy('scheduled_date')->limit(5)->get(),
        ]);
    }

    private function employer(User $user)
    {
        $employer = $user->employer;
        $jobs = $employer?->jobs()->withCount('jobInterests')->latest()->get() ?? collect();

        return view('dashboard.employer', [
            'employer' => $employer,
            'jobs' => $jobs->take(5),
            'totalJobs' => $jobs->count(),
            'activeJobs' => $jobs->where('is_active', true)->count(),
            'applications' => $jobs->sum('job_interests_count'),
            'hired' => $employer ? JobInterest::query()->whereHas('job', fn ($query) => $query->where('employer_id', $employer->id))->where('status', 'Hired')->count('*') : 0,
        ]);
    }

    private function oku(User $user)
    {
        $oku = $user->oku;

        return view('dashboard.oku', [
            'oku' => $oku,
            'interests' => $oku?->jobInterests()->with('job.employer')->latest()->limit(5)->get() ?? collect(),
            'welfareApplications' => $oku?->welfareApplications()->latest()->limit(5)->get() ?? collect(),
            'activeEmployment' => $oku?->activeEmployment()->with('job.employer')->first(),
            'matchingJobs' => $oku ? app(JobMatchingService::class)->getSmartRecommendations($oku, 4) : collect(),
        ]);
    }

    private function family(User $user)
    {
        $oku = $user->oku;

        return view('dashboard.family', [
            'oku' => $oku,
            'welfareApplications' => $oku?->welfareApplications()->latest()->limit(5)->get() ?? collect(),
            'interests' => $oku?->jobInterests()->with('job.employer')->latest()->limit(5)->get() ?? collect(),
            'activeEmployment' => $oku?->activeEmployment()->with('job.employer')->first(),
        ]);
    }

    private function viewer(OkuDataService $service)
    {
        return view('dashboard.viewer', [
            'stats' => $service->dashboard(),
            'totalEmployers' => Employer::query()->where('is_active', true)->count('*'),
            'openJobs' => $this->openJobsCount(),
        ]);
    }

    private function openJobsCount(): int
    {
        return Job::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->whereDate('application_deadline', '>=', today())
                ->orWhereNull('application_deadline'))
            ->count('*');
    }
}
