<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobIndexRequest;
use App\Models\Employer;
use App\Models\Job;
use App\Models\JobInterest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    public function index(JobIndexRequest $request)
    {
        $filters = $request->validated();
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $perPage = (int) ($filters['per_page'] ?? 12);

        $jobs = Job::query()
            ->with('employer:id,company_name,is_active')
            ->active()
            ->notExpired()
            ->whereHas('employer', fn ($query) => $query->where('is_active', true))
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $term = $filters['search'];
                $query->where(fn ($query) => $query
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('requirements', 'like', "%{$term}%")
                    ->orWhereHas('employer', fn ($query) => $query->where('company_name', 'like', "%{$term}%")));
            })
            ->when(filled($filters['category'] ?? null), fn ($query) => $query->byCategorySuitable($filters['category']))
            ->when(filled($filters['location'] ?? null), fn ($query) => $query->where('location', $filters['location']))
            ->when(filled($filters['employment_type'] ?? null), fn ($query) => $query->where('employment_type', $filters['employment_type']))
            ->when(isset($filters['salary_min']), fn ($query) => $query->where(fn ($query) => $query->where('salary_max', '>=', $filters['salary_min'])->orWhere(fn ($query) => $query->whereNull('salary_max')->where('salary_min', '>=', $filters['salary_min']))))
            ->when(isset($filters['salary_max']), fn ($query) => $query->where('salary_min', '<=', $filters['salary_max']))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $oku = $request->user()->role === 'oku_user' ? $request->user()->oku : null;
        $interestedJobIds = $oku
            ? JobInterest::query()->where('oku_id', $oku->id)->whereIn('job_id', $jobs->pluck('id'))->pluck('job_id')->all()
            : [];

        return view('jobs.index', [
            'jobs' => $jobs,
            'filters' => $filters,
            'locations' => Job::query()->active()->notExpired()->distinct()->orderBy('location')->pluck('location'),
            'interestedJobIds' => $interestedJobIds,
            'canApply' => (bool) $oku,
            'stats' => [
                'available' => Job::query()->active()->notExpired()->count(),
                'employers' => Job::query()->active()->notExpired()->distinct()->count('employer_id'),
                'locations' => Job::query()->active()->notExpired()->distinct()->count('location'),
            ],
        ]);
    }

    public function expressInterest(Request $request, Job $job)
    {
        $oku = $request->user()->oku;
        abort_unless($oku && $job->is_active && (! $job->application_deadline || $job->application_deadline->isToday() || $job->application_deadline->isFuture()), 422);

        $interest = JobInterest::query()->firstOrCreate(
            ['oku_id' => $oku->id, 'job_id' => $job->id],
            ['status' => 'Interested', 'application_date' => today()],
        );

        if ($interest->wasRecentlyCreated) {
            Job::query()->whereKey($job->id)->increment('applications_count', 1, []);
        }

        return back()->with('success', $interest->wasRecentlyCreated ? 'Minat terhadap jawatan berjaya direkodkan.' : 'Minat terhadap jawatan ini telah direkodkan sebelum ini.');
    }

    public function show(Job $job)
    {
        $job->increment('views_count', 1, []);

        return response()->json($job->load('employer'));
    }

    public function create(Request $request)
    {
        return view('jobs.form', ['job' => new Job, 'employers' => $this->availableEmployers($request)]);
    }

    public function edit(Request $request, Job $job)
    {
        $this->authorizeEmployerAccess($request, $job);

        return view('jobs.form', ['job' => $job, 'employers' => $this->availableEmployers($request)]);
    }

    public function store(Request $r)
    {
        $job = Job::query()->create($this->data($r));

        return $r->expectsJson() ? response()->json($job, 201) : redirect()->route('jobs.index')->with('success', 'Peluang kerja berjaya diterbitkan.');
    }

    public function update(Request $r, Job $job)
    {
        $this->authorizeEmployerAccess($r, $job);
        $job->update($this->data($r, true));

        return $r->expectsJson() ? response()->json($job) : redirect()->route('jobs.index')->with('success', 'Peluang kerja berjaya dikemas kini.');
    }

    public function destroy(Request $request, Job $job)
    {
        $this->authorizeEmployerAccess($request, $job);
        $job->delete();

        return response()->noContent();
    }

    private function data(Request $r, bool $partial = false): array
    {
        $p = $partial ? 'sometimes' : 'required';

        $data = $r->validate(['employer_id' => "$p|exists:employers,id", 'title' => "$p|string|max:255", 'description' => "$p|string", 'requirements' => "$p|string", 'responsibilities' => 'nullable|string', 'oku_category_suitable' => [$p, Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan', 'Semua'])], 'salary_min' => "$p|numeric|min:0", 'salary_max' => 'nullable|numeric|gte:salary_min', 'location' => "$p|string|max:255", 'working_hours' => 'nullable|string|max:100', 'employment_type' => [$p, Rule::in(['Sepenuh Masa', 'Separuh Masa', 'Kontrak', 'Sementara'])], 'application_deadline' => 'nullable|date', 'is_active' => 'sometimes|boolean']);
        if ($r->user()->role === 'employer') {
            abort_unless($r->user()->employer_id, 403);
            $data['employer_id'] = $r->user()->employer_id;
        }

        return $data;
    }

    private function availableEmployers(Request $request)
    {
        return Employer::query()->active()
            ->when($request->user()->role === 'employer', fn ($query) => $query->whereKey($request->user()->employer_id))
            ->orderBy('company_name')->get(['id', 'company_name']);
    }

    private function authorizeEmployerAccess(Request $request, Job $job): void
    {
        if ($request->user()->role === 'employer') {
            abort_unless((int) $request->user()->employer_id === (int) $job->employer_id, 403);
        }
    }
}
