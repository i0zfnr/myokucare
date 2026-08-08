<?php

namespace App\Http\Controllers;

use App\Http\Requests\WelfareIndexRequest;
use App\Models\Oku;
use App\Models\WelfareApplication;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\UserContentTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WelfareController extends Controller
{
    public function index(WelfareIndexRequest $request, UserContentTranslationService $translations)
    {
        $filters = $request->validated();
        $sortBy = $filters['sort_by'] ?? 'application_date';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $isStaff = $request->user()->hasRole('super_admin', 'jkm_officer');
        $okuId = $isStaff ? null : $request->user()->oku_id;
        $translatedIds = filled($filters['search'] ?? null)
            ? $translations->matchingRecordIds(WelfareApplication::class, $filters['search'])
            : collect();

        $scope = fn () => WelfareApplication::query()
            ->when(! $isStaff, fn ($query) => $query->where('oku_id', $okuId ?? 0));

        $applications = $scope()
            ->with(['oku:id,name,oku_card_number', 'reviewer:id,name'])
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters, $translatedIds) {
                $term = $filters['search'];
                $query->where(fn ($query) => $query
                    ->where('application_type', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereIn('id', $translatedIds)
                    ->orWhereHas('oku', fn ($query) => $query->where('name', 'like', "%{$term}%")->orWhere('oku_card_number', 'like', "%{$term}%")));
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['type'] ?? null), fn ($query) => $query->where('application_type', $filters['type']))
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('application_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('application_date', '<=', $filters['date_to']))
            ->orderBy($sortBy, $sortDirection)
            ->paginate((int) ($filters['per_page'] ?? $request->user()->preferences['default_page_size'] ?? 15))
            ->withQueryString();

        return view('welfare.index', [
            'applications' => $applications,
            'filters' => $filters,
            'isStaff' => $isStaff,
            'types' => $scope()->distinct()->orderBy('application_type')->pluck('application_type'),
            'stats' => [
                'total' => $scope()->count(),
                'pending' => $scope()->where('status', 'Pending')->count(),
                'review' => $scope()->where('status', 'Under Review')->count(),
                'approved' => $scope()->where('status', 'Approved')->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $isStaff = $request->user()->hasRole('super_admin', 'jkm_officer');

        return view('welfare.create', [
            'isStaff' => $isStaff,
            'okus' => $isStaff ? Oku::query()->active()->orderBy('name')->get(['id', 'name', 'oku_card_number']) : collect(),
            'oku' => $request->user()->oku,
        ]);
    }

    public function show(Request $request, WelfareApplication $welfareApplication)
    {
        abort_unless(
            $request->user()->hasRole('super_admin', 'jkm_officer') || $request->user()->oku_id === $welfareApplication->oku_id,
            403,
        );

        $application = $welfareApplication->load(['oku', 'reviewer', 'reviewSchedules']);
        $isStaff = $request->user()->hasRole('super_admin', 'jkm_officer');

        return $request->expectsJson()
            ? response()->json($application)
            : view('welfare.show', compact('application', 'isStaff'));
    }

    public function store(Request $r, UserContentTranslationService $translations)
    {
        $isStaff = $r->user()->hasRole('super_admin', 'jkm_officer');
        $data = $r->validate([
            'oku_id' => [$isStaff ? 'required' : 'nullable', 'exists:okus,id'],
            'application_type' => ['required', 'string', 'max:100'],
            'application_date' => [$isStaff ? 'required' : 'nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (! $isStaff) {
            abort_unless($r->user()->oku_id, 422, 'Profil OKU belum dipautkan.');
            $data['oku_id'] = $r->user()->oku_id;
            $data['application_date'] = today();
        }

        $application = WelfareApplication::query()->create($data);
        $translations->capture($r->user(), $application, 'notes', $data['notes'] ?? null);

        return $r->expectsJson()
            ? response()->json($application, 201)
            : redirect()->route('welfare.index')->with('success', 'Permohonan kebajikan berjaya direkodkan.');
    }

    public function updateStatus(Request $r, WelfareApplication $welfareApplication)
    {
        $data = $r->validate(['status' => ['required', Rule::in(['Pending', 'Under Review', 'Approved', 'Rejected'])], 'rejection_reason' => 'nullable|required_if:status,Rejected|string', 'review_date' => 'nullable|date', 'next_review_date' => 'nullable|date']);
        $data['reviewed_by'] = $r->user()?->id;
        $data['review_date'] ??= today();
        $welfareApplication->update($data);
        User::query()->where('oku_id', $welfareApplication->oku_id)->where('is_active', true)->each(
            fn (User $user) => $user->notify(new SystemNotification(
                'notifications.welfare_status_title',
                'notifications.welfare_status_message',
                ['type' => $welfareApplication->application_type, 'status' => $data['status']],
                route('welfare.show', $welfareApplication),
                'welfare',
            )),
        );

        return $r->expectsJson()
            ? response()->json($welfareApplication)
            : back()->with('success', 'Status permohonan berjaya dikemas kini.');
    }

    public function scheduleReview(Request $r, WelfareApplication $welfareApplication)
    {
        $data = $r->validate(['scheduled_date' => 'required|date|after_or_equal:today', 'notes' => 'nullable|string|max:1000']);

        $schedule = DB::transaction(function () use ($welfareApplication, $data) {
            $welfareApplication->update(['next_review_date' => $data['scheduled_date']]);

            return $welfareApplication->reviewSchedules()->create($data);
        });
        User::query()->where('oku_id', $welfareApplication->oku_id)->where('is_active', true)->each(
            fn (User $user) => $user->notify(new SystemNotification(
                'notifications.welfare_review_title',
                'notifications.welfare_review_message',
                ['date' => $schedule->scheduled_date->format('d/m/Y')],
                route('welfare.show', $welfareApplication),
                'welfare',
            )),
        );

        return $r->expectsJson()
            ? response()->json($schedule, 201)
            : back()->with('success', 'Jadual semakan berjaya ditetapkan.');
    }
}
