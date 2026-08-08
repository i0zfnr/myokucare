<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobInterest;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobCandidateController extends Controller
{
    public function index(Request $request, Job $job)
    {
        $this->authorizeJob($request, $job);

        $candidates = $job->jobInterests()
            ->with('oku:id,name,oku_category,education_level,career_summary,skills,availability_status,resume_path')
            ->latest('application_date')
            ->paginate(20);

        return view('jobs.candidates', compact('job', 'candidates'));
    }

    public function update(Request $request, Job $job, JobInterest $jobInterest)
    {
        $this->authorizeInterest($request, $job, $jobInterest);
        $data = $request->validate([
            'status' => ['required', Rule::in(['Interested', 'Applied', 'Shortlisted', 'Interviewed', 'Hired', 'Rejected'])],
            'interview_date' => ['nullable', 'required_if:status,Interviewed', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $jobInterest->update($data);
        User::query()->where('oku_id', $jobInterest->oku_id)->where('is_active', true)->each(
            fn (User $user) => $user->notify(new SystemNotification(
                'notifications.job_status_title',
                'notifications.job_status_message',
                ['job' => $job->title, 'status_key' => 'notifications.job_status.'.strtolower($data['status'])],
                route('jobs.index'),
                'employment',
            )),
        );

        return back()->with('success', __('jobs.status_updated'));
    }

    public function resume(Request $request, Job $job, JobInterest $jobInterest): StreamedResponse
    {
        $this->authorizeInterest($request, $job, $jobInterest);
        abort_unless($jobInterest->profile_shared_at, 403);
        $path = $jobInterest->oku?->resume_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, null, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeInterest(Request $request, Job $job, JobInterest $jobInterest): void
    {
        abort_unless((int) $jobInterest->job_id === (int) $job->id, 404);
        $this->authorizeJob($request, $job);
    }

    private function authorizeJob(Request $request, Job $job): void
    {
        if ($request->user()->role === 'employer') {
            abort_unless((int) $request->user()->employer_id === (int) $job->employer_id, 403);
        }
    }
}
