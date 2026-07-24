<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditFilterRequest;
use App\Models\ActivityLog;
use App\Models\Employer;
use App\Models\Oku;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    private const ROLES = ['super_admin', 'jkm_officer', 'employer', 'oku_user', 'family_member', 'viewer'];

    public function index(Request $request, ?string $pageRole = null)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(self::ROLES)],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $users = User::query()->with(['employer:id,company_name', 'oku:id,name,oku_card_number'])
            ->when(filled($filters['search'] ?? null), fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$filters['search'].'%')
                ->orWhere('email', 'like', '%'.$filters['search'].'%')))
            ->when(filled($filters['role'] ?? null), fn ($query) => $query->where('role', $filters['role']))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')->paginate(15)->withQueryString();

        $statQuery = User::query()->when($pageRole, fn ($query) => $query->where('role', $pageRole));

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'pageRole' => $pageRole,
            'stats' => [
                'total' => (clone $statQuery)->count(),
                'active' => (clone $statQuery)->where('is_active', true)->count(),
                'linked' => (clone $statQuery)->where(fn ($query) => $query
                    ->whereNotNull('oku_id')
                    ->orWhereNotNull('employer_id'))->count(),
                'inactive' => (clone $statQuery)->where('is_active', false)->count(),
            ],
        ]);
    }

    public function roleIndex(Request $request, string $role)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);
        $request->merge(['role' => $role]);

        return $this->index($request, $role);
    }

    public function create()
    {
        return $this->form(new User);
    }

    public function edit(User $user)
    {
        return $this->form($user);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = User::query()->create($data);
        $this->log($request, $user, 'user_created', ['role' => $user->role, 'is_active' => $user->is_active]);

        return redirect()->route('admin.users.role', $user->role)->with('success', 'Akaun pengguna berjaya dicipta.');
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        $this->guardAdministrativeContinuity($request, $user, $data);
        $before = $user->only(['name', 'email', 'role', 'employer_id', 'oku_id', 'is_active']);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);
        $changes = collect($user->only(array_keys($before)))->filter(fn ($value, $key) => $value != $before[$key])->all();
        $this->log($request, $user, isset($data['password']) ? 'user_updated_password_reset' : 'user_updated', $changes);

        return redirect()->route('admin.users.role', $user->role)->with('success', 'Akaun pengguna berjaya dikemas kini.');
    }

    public function audit(AuditFilterRequest $request, AuditService $service)
    {
        $filters = $request->validated();

        return view('admin.users.audit', [
            'logs' => $service->search($filters),
            'filters' => $filters,
            'statistics' => $service->statistics($filters),
            'actions' => AuditService::ACTIONS,
            'auditService' => $service,
        ]);
    }

    public function exportAudit(AuditFilterRequest $request, AuditService $service)
    {
        $filters = $request->validated();
        $logs = $service->exportRecords($filters);
        ActivityLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => 'audit_exported',
            'changes' => ['filters' => collect($filters)->except('per_page')->all()],
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(1000),
        ]);

        return response()->streamDownload(function () use ($logs, $service) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tarikh', 'Aktiviti', 'Pentadbir', 'Pengguna Sasaran', 'E-mel Sasaran', 'IP']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at->format('d/m/Y H:i:s'),
                    $service->action($log->action)['label'],
                    $log->actor?->name ?? 'Akaun dipadam',
                    $log->subject?->name ?? 'Tiada',
                    $log->subject?->email ?? 'Tiada',
                    $service->maskIp($log->ip_address),
                ]);
            }
            fclose($out);
        }, 'audit-aktiviti-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function form(User $user)
    {
        return view('admin.users.form', [
            'managedUser' => $user,
            'roles' => self::ROLES,
            'employers' => Employer::query()->active()->orderBy('company_name')->get(['id', 'company_name']),
            'okus' => Oku::query()->active()->orderBy('name')->get(['id', 'name', 'oku_card_number']),
        ]);
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $required = $user?->exists ? 'nullable' : 'required';

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(self::ROLES)],
            'employer_id' => ['nullable', 'required_if:role,employer', 'exists:employers,id'],
            'oku_id' => ['nullable', 'required_if:role,oku_user,family_member', 'exists:okus,id'],
            'is_active' => ['required', 'boolean'],
            'password' => [$required, 'nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $data['employer_id'] = $data['role'] === 'employer' ? ($data['employer_id'] ?? null) : null;
        $data['oku_id'] = in_array($data['role'], ['oku_user', 'family_member'], true) ? ($data['oku_id'] ?? null) : null;

        return $data;
    }

    private function guardAdministrativeContinuity(Request $request, User $user, array $data): void
    {
        $removesAdmin = $user->role === 'super_admin' && ($data['role'] !== 'super_admin' || ! $data['is_active']);
        if ($request->user()->is($user) && $removesAdmin) {
            throw ValidationException::withMessages(['role' => 'Anda tidak boleh menurunkan peranan atau menyahaktifkan akaun sendiri.']);
        }
        if ($removesAdmin && User::query()->where('role', 'super_admin')->where('is_active', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Sistem mesti mempunyai sekurang-kurangnya seorang Pentadbir aktif.']);
        }
    }

    private function log(Request $request, User $subject, string $action, array $changes): void
    {
        ActivityLog::query()->create([
            'actor_id' => $request->user()->id,
            'subject_user_id' => $subject->id,
            'action' => $action,
            'changes' => $changes ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(1000),
        ]);
    }
}
