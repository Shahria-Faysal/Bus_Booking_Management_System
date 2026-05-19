<?php

namespace App\Livewire\AuditLogs;

use App\Services\AuditLogService;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogList extends Component
{
    use WithPagination;

    public string $action       = '';
    public string $search       = '';
    public array  $actions      = [];

    public function updatedAction(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }

    public function render(AuditLogService $service)
    {
        $this->actions = $service->actions();

        $logs = $service->paginate(action: $this->action);

        return view('livewire.audit-logs.audit-log-list', compact('logs'));
    }
}
