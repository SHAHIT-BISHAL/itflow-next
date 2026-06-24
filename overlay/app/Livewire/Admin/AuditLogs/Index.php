<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $action = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAction(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        $logs = AuditLog::query()
            ->where('company_id', $companyId)
            ->with(['actor', 'subject'])
            ->when($this->search, function ($query) {
                $query->where(function ($nested) {
                    $nested
                        ->where('action', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhere('ip_address', 'like', "%{$this->search}%");
                });
            })
            ->when($this->action, fn ($query) => $query->where('action', $this->action))
            ->latest()
            ->paginate(25);

        return view('livewire.admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::query()
                ->where('company_id', $companyId)
                ->select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
        ])->layout('components.layouts.app', ['header' => 'Audit Logs']);
    }
}
