<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Expenses extends Component
{
    public int $year;

    public function mount(): void
    {
        $this->year = today()->year;
    }

    public function render()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $byCategory = Expense::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->whereYear('expense_date', $this->year)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($e) => ['category' => ucfirst($e->category), 'total' => (float) $e->total]);

        $monthly = collect(range(1, 12))->map(function ($m) use ($companyId) {
            $label = \Carbon\Carbon::create($this->year, $m)->format('M');
            $billable   = Expense::where('company_id', $companyId)->whereYear('expense_date', $this->year)
                ->when(Auth::user()->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', Auth::user()->permittedClients()->select('clients.id')))
                ->whereMonth('expense_date', $m)->where('is_billable', true)->sum('amount');
            $internal   = Expense::where('company_id', $companyId)->whereYear('expense_date', $this->year)
                ->when(Auth::user()->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', Auth::user()->permittedClients()->select('clients.id')))
                ->whereMonth('expense_date', $m)->where('is_billable', false)->sum('amount');
            return ['month' => $label, 'billable' => (float) $billable, 'internal' => (float) $internal];
        });

        $totalYtd        = Expense::where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->whereYear('expense_date', $this->year)->sum('amount');
        $totalBillable   = Expense::where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->whereYear('expense_date', $this->year)->where('is_billable', true)->sum('amount');
        $totalInternal   = Expense::where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->whereYear('expense_date', $this->year)->where('is_billable', false)->sum('amount');

        $years = range(today()->year, max(today()->year - 4, 2020));

        return view('livewire.reports.expenses', compact(
            'byCategory', 'monthly', 'totalYtd', 'totalBillable', 'totalInternal', 'years'
        ))->layout('components.layouts.app', ['header' => 'Expense Report']);
    }
}
