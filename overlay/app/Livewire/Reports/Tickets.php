<?php

namespace App\Livewire\Reports;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Tickets extends Component
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

        $byStatus = Ticket::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byPriority = Ticket::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $monthly = collect(range(1, 12))->map(function ($m) use ($companyId) {
            $label = \Carbon\Carbon::create($this->year, $m)->format('M');
            $opened = Ticket::where('company_id', $companyId)
                ->when(Auth::user()->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', Auth::user()->permittedClients()->select('clients.id')))
                ->whereYear('created_at', $this->year)->whereMonth('created_at', $m)->count();
            $closed = Ticket::where('company_id', $companyId)
                ->when(Auth::user()->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', Auth::user()->permittedClients()->select('clients.id')))
                ->whereIn('status', ['resolved', 'closed'])
                ->whereYear('updated_at', $this->year)->whereMonth('updated_at', $m)->count();
            return ['month' => $label, 'opened' => $opened, 'closed' => $closed];
        });

        $totalOpen     = Ticket::where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->open()->count();
        $totalResolved = Ticket::where('company_id', $companyId)->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))->whereIn('status', ['resolved', 'closed'])->count();
        $totalThisMonth = Ticket::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->whereYear('created_at', today()->year)->whereMonth('created_at', today()->month)->count();

        // Top clients by ticket volume
        $topClients = Ticket::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->selectRaw('client_id, COUNT(*) as count')
            ->groupBy('client_id')
            ->orderByDesc('count')
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'name'  => \App\Models\Client::find($t->client_id)?->name ?? 'No client',
                'count' => $t->count,
            ]);

        $years = range(today()->year, max(today()->year - 4, 2020));

        return view('livewire.reports.tickets', compact(
            'byStatus', 'byPriority', 'monthly',
            'totalOpen', 'totalResolved', 'totalThisMonth', 'topClients', 'years'
        ))->layout('components.layouts.app', ['header' => 'Ticket Report']);
    }
}
