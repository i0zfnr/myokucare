<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployerIndexRequest;
use App\Models\Employer;
use App\Models\Job;
use App\Services\PermissionService;
use App\Services\RecordAccessService;
use App\Services\RecordAuditService;
use App\Services\RecordLifecycleService;
use Illuminate\Http\Request;

class EmployerController extends Controller
{
    public function index(EmployerIndexRequest $request, RecordAccessService $access)
    {
        if ($request->user()->hasRole('employer')) {
            abort_unless($request->user()->employer_id, 403, 'EMPLOYER_PROFILE_NOT_LINKED');

            return redirect()->route('employers.show', $request->user()->employer_id);
        }

        $filters = $request->validated();
        $sortBy = $filters['sort_by'] ?? 'company_name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $perPage = (int) ($filters['per_page'] ?? $request->user()->preferences['default_page_size'] ?? 15);

        $baseQuery = $access->employers($request->user());
        $employers = (clone $baseQuery)
            ->withCount(['jobs', 'activeJobs'])
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $term = $filters['search'];
                $query->where(fn ($query) => $query
                    ->where('company_name', 'like', "%{$term}%")
                    ->orWhere('registration_number', 'like', "%{$term}%")
                    ->orWhere('contact_person', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when(filled($filters['sector'] ?? null), fn ($query) => $query->where('industry_sector', $filters['sector']))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return view('employers.index', [
            'employers' => $employers,
            'filters' => $filters,
            'sectors' => (clone $baseQuery)->distinct()->orderBy('industry_sector')->pluck('industry_sector'),
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->where('is_active', true)->count(),
                'oku_friendly' => (clone $baseQuery)->where('has_oku_quota', true)->count(),
                'active_jobs' => Job::query()->whereIn('employer_id', (clone $baseQuery)->select('id'))->where('is_active', true)->count(),
            ],
        ]);
    }

    public function show(Request $request, Employer $employer, RecordAccessService $access)
    {
        $access->authorizeEmployer($request->user(), $employer);
        $employer->load('jobs');
        $employer->load(['employments' => function ($query) use ($request) {
            if ($request->user()->hasRole('oku_user')) {
                $query->where('oku_id', $request->user()->oku_id);
            }
            $query->with('oku:id,name');
        }]);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $employer->id,
                'company_name' => $employer->company_name,
                'industry_sector' => $employer->industry_sector,
                'website' => $employer->website,
                'company_description' => $employer->company_description,
                'jobs' => $employer->jobs->map->only([
                    'id', 'title', 'description', 'requirements', 'location',
                    'employment_type', 'application_deadline', 'is_active',
                ]),
                'employments' => $employer->employments->map(fn ($employment) => [
                    'id' => $employment->id,
                    'oku_id' => $employment->oku_id,
                    'oku_name' => $employment->oku?->name,
                    'job_title' => $employment->job_title,
                    'status' => $employment->status,
                ]),
            ]);
        }

        return view('employers.show', compact('employer'));
    }

    public function create(Request $request, PermissionService $permissions)
    {
        $permissions->authorize($request->user(), 'employer.create');

        return view('employers.form', ['employer' => new Employer]);
    }

    public function edit(Request $request, Employer $employer, PermissionService $permissions)
    {
        $permissions->authorize($request->user(), 'employer.update');

        return view('employers.form', compact('employer'));
    }

    public function store(Request $r, PermissionService $permissions, RecordAuditService $audit)
    {
        $permissions->authorize($r->user(), 'employer.create');
        $employer = Employer::query()->create($this->data($r));
        $audit->log($r, $employer, 'CREATED', [], $employer->toArray());

        return $r->expectsJson()
            ? response()->json($employer, 201)
            : redirect()->route('employers.index')->with('success', 'Majikan berjaya didaftarkan.');
    }

    public function update(Request $r, Employer $employer, PermissionService $permissions, RecordAuditService $audit)
    {
        $permissions->authorize($r->user(), 'employer.update');
        $before = $employer->toArray();
        $employer->update($this->data($r, true));
        $audit->log($r, $employer, 'UPDATED', $before, $employer->toArray());

        return $r->expectsJson()
            ? response()->json($employer)
            : redirect()->route('employers.index')->with('success', 'Maklumat majikan berjaya dikemas kini.');
    }

    public function destroy(Request $request, Employer $employer, RecordLifecycleService $lifecycle)
    {
        $lifecycle->softDelete($request, $employer, 'employer.delete', $employer->company_name);

        return $request->expectsJson() ? response()->noContent() : redirect()->route('employers.index')->with('success', 'Majikan telah dipadam secara lembut.');
    }

    private function data(Request $r, bool $partial = false): array
    {
        $p = $partial ? 'sometimes' : 'required';

        return $r->validate(['company_name' => "$p|string|max:255", 'registration_number' => "$p|string|max:50|unique:employers,registration_number,".($r->route('employer')?->id ?? 'NULL'), 'address' => "$p|string", 'industry_sector' => "$p|string|max:100", 'contact_person' => "$p|string|max:255", 'phone_number' => "$p|string|max:20", 'email' => "$p|email|max:255", 'website' => 'nullable|url|max:255', 'company_description' => 'nullable|string', 'number_of_employees' => 'nullable|integer|min:0', 'has_oku_quota' => 'sometimes|boolean', 'is_active' => 'sometimes|boolean']);
    }
}
