<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Revenue extends Component
{
    public int $year;

    public function mount(): void
    {
        $this->year = today()->year;
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        $months = collect(range(1, 12))->map(function ($m) use ($companyId) {
            $label = \Carbon\Carbon::create($this->year, $m)->format('M');
            $revenue = Payment::whereYear('paid_at', $this->year)
                ->whereMonth('paid_at', $m)
                ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
                ->sum('amount');
            $expenses = \App\Models\Expense::where('company_id', $companyId)
                ->whereYear('expense_date', $this->year)
                ->whereMonth('expense_date', $m)
                ->sum('amount');
            return ['month' => $label, 'revenue' => (float) $revenue, 'expenses' => (float) $expenses];
        });

        $totalRevenue  = $months->sum('revenue');
        $totalExpenses = $months->sum('expenses');
        $netProfit     = $totalRevenue - $totalExpenses;

        // Top clients by revenue this year
        $topClients = Payment::selectRaw('invoices.client_id, SUM(payments.amount) as total')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.company_id', $companyId)
            ->whereYear('payments.paid_at', $this->year)
            ->groupBy('invoices.client_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('invoice.client')
            ->get()
            ->map(fn ($p) => [
                'name'  => \App\Models\Client::find($p->client_id)?->name ?? 'Unknown',
                'total' => (float) $p->total,
            ]);

        $years = range(today()->year, max(today()->year - 4, 2020));

        return view('livewire.reports.revenue', compact('months', 'totalRevenue', 'totalExpenses', 'netProfit', 'topClients', 'years'))
            ->layout('components.layouts.app', ['header' => 'Revenue Report']);
    }
}
