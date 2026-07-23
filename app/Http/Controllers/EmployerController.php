<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use Illuminate\Http\Request;

class EmployerController extends Controller
{
    public function index()
    {
        return view('employers.index', ['employers' => Employer::withCount('jobs')->latest()->paginate(15)]);
    }

    public function show(Employer $employer)
    {
        return response()->json($employer->load('jobs'));
    }

    public function store(Request $r)
    {
        return response()->json(Employer::create($this->data($r)), 201);
    }

    public function update(Request $r, Employer $employer)
    {
        $employer->update($this->data($r, true));

        return response()->json($employer);
    }

    public function destroy(Employer $employer)
    {
        $employer->delete();

        return response()->noContent();
    }

    private function data(Request $r, bool $partial = false): array
    {
        $p = $partial ? 'sometimes' : 'required';

        return $r->validate(['company_name' => "$p|string|max:255", 'registration_number' => "$p|string|max:50|unique:employers,registration_number,".($r->route('employer')?->id ?? 'NULL'), 'address' => "$p|string", 'industry_sector' => "$p|string|max:100", 'contact_person' => "$p|string|max:255", 'phone_number' => "$p|string|max:20", 'email' => "$p|email|max:255", 'website' => 'nullable|url|max:255', 'company_description' => 'nullable|string', 'number_of_employees' => 'nullable|integer|min:0', 'has_oku_quota' => 'sometimes|boolean', 'is_active' => 'sometimes|boolean']);
    }
}
