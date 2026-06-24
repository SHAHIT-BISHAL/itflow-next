<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function render()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $invoices = Invoice::active()
            ->where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->with(['client'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                  ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('issue_date', 'desc')
            ->paginate(25);

        return view('livewire.invoices.index', [
            'invoices'    => $invoices,
            'totalPaid'   => Invoice::active()->where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->where('status', 'paid')->sum('total'),
            'totalOwed'   => Invoice::active()->where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->whereNotIn('status', ['paid', 'void', 'draft'])->sum(\DB::raw('total - amount_paid')),
            'overdueCount' => Invoice::active()->where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->overdue()->count(),
        ])->layout('components.layouts.app', ['header' => 'Invoices']);
    }
}
