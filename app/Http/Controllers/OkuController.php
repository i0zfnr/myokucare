<?php

namespace App\Http\Controllers;

use App\Models\Oku;
use App\Services\JobMatchingService;
use App\Services\OkuDataService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OkuController extends Controller
{
    public function index(Request $request, OkuDataService $data)
    {
        $okus = Oku::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('ic_number', 'like', '%'.$request->search.'%')->orWhere('oku_card_number', 'like', '%'.$request->search.'%')))
            ->when($request->filled('category'), fn ($q) => $q->where('oku_category', $request->category))
            ->when($request->filled('employment_status'), fn ($q) => $q->where('employment_status', $request->employment_status))
            ->latest()->paginate(15)->withQueryString();

        return view('oku.index', ['okus' => $okus, 'stats' => $data->getStats()]);
    }

    public function create()
    {
        return view('oku.form', ['oku' => new Oku]);
    }

    public function store(Request $request)
    {
        $oku = Oku::create($this->validated($request));

        return redirect()->route('oku.show', $oku)->with('success', 'OKU record created.');
    }

    public function show(Oku $oku, JobMatchingService $matching, OkuDataService $data)
    {
        return view('oku.show', ['oku' => $oku, 'matchingJobs' => $matching->getSmartRecommendations($oku), 'employmentHistory' => $oku->employments()->with('job.employer')->latest()->get(), 'welfareApplications' => $oku->welfareApplications()->latest()->get(), 'stats' => $data->getEmploymentHistory($oku)]);
    }

    public function edit(Oku $oku)
    {
        return view('oku.form', compact('oku'));
    }

    public function update(Request $request, Oku $oku)
    {
        $oku->update($this->validated($request, $oku));

        return redirect()->route('oku.show', $oku)->with('success', 'OKU record updated.');
    }

    public function destroy(Oku $oku)
    {
        $oku->delete();

        return redirect()->route('oku.index')->with('success', 'OKU record deleted.');
    }

    public function findJobs(Oku $oku, JobMatchingService $matching)
    {
        return view('oku.find-jobs', ['oku' => $oku, 'matchingJobs' => $matching->findMatchingJobs($oku)]);
    }

    private function validated(Request $request, ?Oku $oku = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255', 'ic_number' => ['required', 'string', 'max:20', Rule::unique('okus')->ignore($oku)],
            'gender' => ['required', Rule::in(['Lelaki', 'Perempuan'])], 'age' => 'required|integer|min:1|max:120',
            'marital_status' => ['required', Rule::in(['Berkahwin', 'Bujang', 'Duda', 'Janda'])], 'address' => 'required|string',
            'education_level' => 'required|string|max:100', 'oku_card_number' => ['required', 'string', 'max:50', Rule::unique('okus')->ignore($oku)],
            'oku_category' => ['required', Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])],
            'employment_status' => ['sometimes', Rule::in(['Bekerja', 'Tidak Bekerja', 'Sendiri'])],
            'phone_number' => 'nullable|string|max:20', 'email' => 'nullable|email|max:255', 'has_smartphone' => 'sometimes|boolean',
            'has_internet' => 'sometimes|boolean', 'emergency_contact_name' => 'nullable|string|max:255', 'emergency_contact_phone' => 'nullable|string|max:20', 'is_active' => 'sometimes|boolean',
        ]);
    }
}
