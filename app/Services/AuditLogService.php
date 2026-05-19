<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function paginate(string $action = '', int $perPage = 25): LengthAwarePaginator
    {
        return AuditLog::query()
            ->when($action, fn($q) => $q->where('action_type', $action))
            ->latest('logged_at')
            ->paginate($perPage);
    }

    public function actions(): array
    {
        return AuditLog::distinct()->pluck('action_type')->sort()->values()->toArray();
    }
}
