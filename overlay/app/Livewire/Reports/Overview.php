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
        $user = Auth::user();
        $companyId = $user->company_id;
        $restrictClients = fn ($q) => $q->when($user->hasClientRestrictions(), fn ($query) => $query->whereIn('client_id', $user->permittedClients()->select('clients.id')));

        $revenueThisMonth = Payment::whereMonth('paid_at', today()->month)
            ->whereYear('paid_at', today()->year)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
            ->sum('amount');

        $revenueLastMonth = Payment::whereMonth('paid_at', today()->subMonth()->month)
            ->whereYear('paid_at', today()->subMonth()->year)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
            ->sum('amount');

        $revenueYtd = Payment::whereYear('paid_at', today()->year)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->whereHas('invoice', fn ($q) => $q->where('company_id', $companyId))
            ->sum('amount');

        $expensesThisMonth = Expense::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->whereMonth('expense_date', today()->month)
            ->whereYear('expense_date', today()->year)
            ->sum('amount');

        $overdueInvoices = $restrictClients(Invoice::where('company_id', $companyId)->active())->overdue()->count();
        $overdueAmount   = $restrictClients(Invoice::where('company_id', $companyId)->active())->overdue()->sum(\DB::raw('total - amount_paid'));

        $openTickets   = $restrictClients(Ticket::where('company_id', $companyId)->active())->open()->count();
        $closedThisMonth = Ticket::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->whereIn('status', ['resolved', 'closed'])
            ->whereMonth('updated_at', today()->month)
            ->whereYear('updated_at', today()->year)
            ->count();

        $openDeals    = $restrictClients(Deal::where('company_id', $companyId)->active())->open()->count();
        $pipelineValue = $restrictClients(Deal::where('company_id', $companyId)->active())->open()->sum('value');

        // Revenue last 6 months for sparkline
        $last6 = collect(range(5, 0))->map(function ($offset) use ($companyId) {
            $date = today()->subMonths($offset);
            return [
                'month'   => $date->format('M'),
                'revenue' => Payment::whereMonth('paid_at', $date->month)
                    ->whereYear('paid_at', $date->year)
                    ->when(Auth::user()->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', Auth::user()->permittedClients()->select('clients.id')))
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
