<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Deal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $companyId = Auth::user()->company_id;

        $revenueThisMonth = Payment::whereMonth('paid_at', today()->month)
            ->whereYear('paid_at', today()->year)
            ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
            ->sum('amount');

        $revenueLastMonth = Payment::whereMonth('paid_at', today()->subMonth()->month)
            ->whereYear('paid_at', today()->subMonth()->year)
            ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
            ->sum('amount');

        $revenueYtd = Payment::whereYear('paid_at', today()->year)
            ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
            ->sum('amount');

        $expensesThisMonth = Expense::where('company_id', $companyId)
            ->whereMonth('expense_date', today()->month)
            ->whereYear('expense_date', today()->year)
            ->sum('amount');

        $overdueInvoices = Invoice::where('company_id', $companyId)->active()->overdue()->count();
        $overdueAmount   = Invoice::where('company_id', $companyId)->active()->overdue()->sum(\DB::raw('total - amount_paid'));

        $openTickets   = Ticket::where('company_id', $companyId)->active()->open()->count();
        $closedThisMonth = Ticket::where('company_id', $companyId)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereMonth('updated_at', today()->month)
            ->whereYear('updated_at', today()->year)
            ->count();

        $openDeals    = Deal::where('company_id', $companyId)->active()->open()->count();
        $pipelineValue = Deal::where('company_id', $companyId)->active()->open()->sum('value');

        // Revenue last 6 months for sparkline
        $last6 = collect(range(5, 0))->map(function ($offset) use ($companyId) {
            $date = today()->subMonths($offset);
            return [
                'month'   => $date->format('M'),
                'revenue' => Payment::whereMonth('paid_at', $date->month)
                    ->whereYear('paid_at', $date->year)
                    ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
                    ->sum('amount'),
            ];
        });

        return view('livewire.reports.overview', compact(
            'revenueThisMonth', 'revenueLastMonth', 'revenueYtd',
            'expensesThisMonth', 'overdueInvoices', 'overdueAmount',
            'openTickets', 'closedThisMonth', 'openDeals', 'pipelineValue', 'last6'
        ))->layout('components.layouts.app', ['header' => 'Reports']);
    }
}
