<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    public function index()
    {
        return view('jobs.index', ['jobs' => Job::with('employer')->latest()->paginate(15)]);
    }

    public function show(Job $job)
    {
        $job->increment('views_count');

        return response()->json($job->load('employer'));
    }

    public function store(Request $r)
    {
        return response()->json(Job::create($this->data($r)), 201);
    }

    public function update(Request $r, Job $job)
    {
        $job->update($this->data($r, true));

        return response()->json($job);
    }

    public function destroy(Job $job)
    {
        $job->delete();

        return response()->noContent();
    }

    private function data(Request $r, bool $partial = false): array
    {
        $p = $partial ? 'sometimes' : 'required';

        return $r->validate(['employer_id' => "$p|exists:employers,id", 'title' => "$p|string|max:255", 'description' => "$p|string", 'requirements' => "$p|string", 'responsibilities' => 'nullable|string', 'oku_category_suitable' => [$p, Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan', 'Semua'])], 'salary_min' => "$p|numeric|min:0", 'salary_max' => 'nullable|numeric|gte:salary_min', 'location' => "$p|string|max:255", 'working_hours' => 'nullable|string|max:100', 'employment_type' => ['sometimes', Rule::in(['Sepenuh Masa', 'Separuh Masa', 'Kontrak', 'Sementara'])], 'application_deadline' => 'nullable|date', 'is_active' => 'sometimes|boolean']);
    }
}
