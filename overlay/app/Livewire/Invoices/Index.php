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
        $companyId = Auth::user()->company_id;

        $invoices = Invoice::active()
            ->where('company_id', $companyId)
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
            'totalPaid'   => Invoice::active()->where('company_id', $companyId)->where('status', 'paid')->sum('total'),
            'totalOwed'   => Invoice::active()->where('company_id', $companyId)->whereNotIn('status', ['paid', 'void', 'draft'])->sum('amount_due'),
            'overdueCount' => Invoice::active()->where('company_id', $companyId)->overdue()->count(),
        ])->layout('components.layouts.app', ['header' => 'Invoices']);
    }
}
