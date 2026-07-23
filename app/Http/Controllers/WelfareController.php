<?php

namespace App\Http\Controllers;

use App\Models\WelfareApplication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WelfareController extends Controller
{
    public function index()
    {
        return view('welfare.index', ['applications' => WelfareApplication::with('oku')->latest()->paginate(15)]);
    }

    public function create()
    {
        return redirect()->route('welfare.index');
    }

    public function show(WelfareApplication $welfareApplication)
    {
        return response()->json($welfareApplication->load(['oku', 'reviewer', 'reviewSchedules']));
    }

    public function store(Request $r)
    {
        $data = $r->validate(['oku_id' => 'required|exists:okus,id', 'application_type' => 'required|string|max:100', 'application_date' => 'required|date', 'notes' => 'nullable|string']);

        return response()->json(WelfareApplication::create($data), 201);
    }

    public function updateStatus(Request $r, WelfareApplication $welfareApplication)
    {
        $data = $r->validate(['status' => ['required', Rule::in(['Pending', 'Under Review', 'Approved', 'Rejected'])], 'rejection_reason' => 'nullable|required_if:status,Rejected|string', 'review_date' => 'nullable|date', 'next_review_date' => 'nullable|date']);
        $data['reviewed_by'] = $r->user()?->id;
        $welfareApplication->update($data);

        return response()->json($welfareApplication);
    }

    public function scheduleReview(Request $r, WelfareApplication $welfareApplication)
    {
        $data = $r->validate(['scheduled_date' => 'required|date', 'notes' => 'nullable|string']);

        return response()->json($welfareApplication->reviewSchedules()->create($data), 201);
    }
}
