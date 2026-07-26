<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Job;
use App\Models\Oku;
use App\Models\OkuEmployment;
use App\Services\PermissionService;
use App\Services\RecordAccessService;
use App\Services\RecordAuditService;
use App\Services\RecordLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmploymentRelationshipController extends Controller
{
    public function index(Request $request, RecordAccessService $access)
    {
        $query = $access->employments($request->user())
            ->with(['oku', 'employer', 'job'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('department'), fn ($q) => $q->where('department', 'like', '%'.$request->string('department').'%'))
            ->latest('start_date');

        return view('employments.index', [
            'employments' => $query->paginate(20)->withQueryString(),
            'canManage' => app(PermissionService::class)->allows($request->user(), 'employment.create'),
        ]);
    }

    public function show(Request $request, OkuEmployment $employment, RecordAccessService $access)
    {
        $access->authorizeEmployment($request->user(), $employment);

        return view('employments.show', ['employment' => $employment->load(['oku', 'employer', 'job'])]);
    }

    public function create(Request $request, PermissionService $permissions)
    {
        $permissions->authorize($request->user(), 'employment.create');

        return view('employments.form', [
            'employment' => new OkuEmployment,
            'okus' => Oku::query()->orderBy('name')->get(['id', 'name']),
            'employers' => Employer::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    public function store(Request $request, PermissionService $permissions, RecordAuditService $audit)
    {
        $permissions->authorize($request->user(), 'employment.create');
        $data = $this->data($request);
        $employment = OkuEmployment::query()->create($data);
        $audit->log($request, $employment, 'CREATED', [], $employment->toArray());

        return redirect()->route('employments.show', $employment)->with('success', 'Rekod pekerjaan berjaya diwujudkan.');
    }

    public function edit(Request $request, OkuEmployment $employment, PermissionService $permissions)
    {
        $permissions->authorize($request->user(), 'employment.update');

        return view('employments.form', [
            'employment' => $employment,
            'okus' => Oku::query()->orderBy('name')->get(['id', 'name']),
            'employers' => Employer::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    public function update(Request $request, OkuEmployment $employment, PermissionService $permissions, RecordAuditService $audit)
    {
        $permissions->authorize($request->user(), 'employment.update');
        $before = $employment->toArray();
        $employment->update($this->data($request));
        $audit->log($request, $employment, 'UPDATED', $before, $employment->toArray());

        return redirect()->route('employments.show', $employment)->with('success', 'Rekod pekerjaan berjaya dikemas kini.');
    }

    public function destroy(Request $request, OkuEmployment $employment, RecordLifecycleService $lifecycle)
    {
        $lifecycle->softDelete($request, $employment, 'employment.delete', $employment->job_title ?? 'Employment');

        return redirect()->route('employments.index')->with('success', 'Rekod pekerjaan telah dipadam secara lembut.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'oku_id' => ['required', 'exists:okus,id'],
            'employer_id' => ['required', 'exists:employers,id'],
            'job_id' => ['nullable', 'exists:jobs,id'],
            'job_title' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['PENDING', 'ACTIVE', 'INACTIVE', 'TERMINATED', 'REJECTED', 'UNDER_REVIEW'])],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'verification_status' => ['required', Rule::in(['PENDING', 'VERIFIED', 'REJECTED', 'UNDER_REVIEW'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (! empty($data['job_id'])) {
            $job = Job::query()->whereKey($data['job_id'])->where('employer_id', $data['employer_id'])->firstOrFail();
            $data['job_title'] = $job->title;
        }
        $data['salary_encrypted'] = isset($data['salary']) ? (string) $data['salary'] : null;
        unset($data['salary']);
        if ($data['verification_status'] === 'VERIFIED') {
            $data['verified_by_pegawai_id'] = $request->user()->id;
            $data['verified_at'] = now();
        }

        return $data;
    }
}
