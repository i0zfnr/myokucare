<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployerIndexRequest;
use App\Models\Employer;
use App\Models\Job;
use Illuminate\Http\Request;

class EmployerController extends Controller
{
    public function index(EmployerIndexRequest $request)
    {
        $filters = $request->validated();
        $sortBy = $filters['sort_by'] ?? 'company_name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $perPage = (int) ($filters['per_page'] ?? $request->user()->preferences['default_page_size'] ?? 15);

        $employers = Employer::query()
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
            'sectors' => Employer::query()->distinct()->orderBy('industry_sector')->pluck('industry_sector'),
            'stats' => [
                'total' => Employer::query()->count(),
                'active' => Employer::query()->where('is_active', true)->count(),
                'oku_friendly' => Employer::query()->where('has_oku_quota', true)->count(),
                'active_jobs' => Job::query()->where('is_active', true)->count(),
            ],
        ]);
    }

    public function show(Employer $employer)
    {
        return response()->json($employer->load('jobs'));
    }

    public function create()
    {
        return view('employers.form', ['employer' => new Employer]);
    }

    public function edit(Employer $employer)
    {
        return view('employers.form', compact('employer'));
    }

    public function store(Request $r)
    {
        $employer = Employer::query()->create($this->data($r));

        return $r->expectsJson()
            ? response()->json($employer, 201)
            : redirect()->route('employers.index')->with('success', 'Majikan berjaya didaftarkan.');
    }

    public function update(Request $r, Employer $employer)
    {
        $employer->update($this->data($r, true));

        return $r->expectsJson()
            ? response()->json($employer)
            : redirect()->route('employers.index')->with('success', 'Maklumat majikan berjaya dikemas kini.');
    }

    public function destroy(Employer $employer)
    {
        Employer::query()->whereKey($employer->getKey())->delete();

        return response()->noContent();
    }

    private function data(Request $r, bool $partial = false): array
    {
        $p = $partial ? 'sometimes' : 'required';

        return $r->validate(['company_name' => "$p|string|max:255", 'registration_number' => "$p|string|max:50|unique:employers,registration_number,".($r->route('employer')?->id ?? 'NULL'), 'address' => "$p|string", 'industry_sector' => "$p|string|max:100", 'contact_person' => "$p|string|max:255", 'phone_number' => "$p|string|max:20", 'email' => "$p|email|max:255", 'website' => 'nullable|url|max:255', 'company_description' => 'nullable|string', 'number_of_employees' => 'nullable|integer|min:0', 'has_oku_quota' => 'sometimes|boolean', 'is_active' => 'sometimes|boolean']);
    }
}
