<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;

class AuditService
{
    public const ACTIONS = [
        'user_created' => ['label' => 'Akaun dicipta', 'severity' => 'info', 'description' => 'Akaun pengguna baharu telah dicipta.'],
        'user_updated' => ['label' => 'Akaun dikemas kini', 'severity' => 'info', 'description' => 'Maklumat atau akses akaun telah diubah.'],
        'user_updated_password_reset' => ['label' => 'Kata laluan ditetapkan semula', 'severity' => 'warning', 'description' => 'Maklumat akaun dan kata laluan telah diubah.'],
        'audit_exported' => ['label' => 'Log audit dieksport', 'severity' => 'warning', 'description' => 'Salinan log audit telah dimuat turun.'],
    ];

    public function search(array $filters)
    {
        return $this->query($filters)
            ->with(['actor:id,name', 'subject:id,name,email'])
            ->orderBy('created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function statistics(array $filters): array
    {
        $query = $this->query(collect($filters)->only(['date_from', 'date_to'])->all());

        return [
            'total' => (clone $query)->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'week' => (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'warning' => (clone $query)->whereIn('action', $this->actionsForSeverity('warning'))->count(),
        ];
    }

    public function exportRecords(array $filters)
    {
        return $this->query($filters)->with(['actor:id,name', 'subject:id,name,email'])->latest()->limit(5000)->get();
    }

    public function action(string $action): array
    {
        return self::ACTIONS[$action] ?? ['label' => str($action)->headline()->toString(), 'severity' => 'info', 'description' => 'Aktiviti pentadbiran sistem.'];
    }

    public function maskIp(?string $ip): string
    {
        if (! $ip) {
            return 'Tidak diketahui';
        }
        if (str_contains($ip, ':')) {
            return preg_replace('/:[^:]+$/', ':****', $ip) ?? $ip;
        }

        return preg_replace('/\.\d+$/', '.***', $ip) ?? $ip;
    }

    private function query(array $filters): Builder
    {
        return ActivityLog::query()
            ->when(filled($filters['search'] ?? null), fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('actor', fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%'))
                ->orWhereHas('subject', fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%')->orWhere('email', 'like', '%'.$filters['search'].'%'))))
            ->when(filled($filters['action'] ?? null), fn ($query) => $query->where('action', $filters['action']))
            ->when(filled($filters['severity'] ?? null), fn ($query) => $query->whereIn('action', $this->actionsForSeverity($filters['severity'])))
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']));
    }

    private function actionsForSeverity(string $severity): array
    {
        return collect(self::ACTIONS)->filter(fn ($data) => $data['severity'] === $severity)->keys()->all();
    }
}
