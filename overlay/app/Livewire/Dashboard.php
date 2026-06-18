<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Domain;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $restrictClients = fn ($q) => $q->when($user->hasClientRestrictions(), fn ($query) => $query->whereIn('client_id', $user->permittedClients()->select('clients.id')));

        return view('livewire.dashboard', [
            'clientCount'    => Client::active()->visibleTo($user)->count(),
            'leadCount'      => Client::active()->visibleTo($user)->where('is_lead', true)->count(),
            'contactCount'   => Contact::whereHas('client', fn ($q) => $q->visibleTo($user))->active()->count(),
            'assetCount'     => $restrictClients(Asset::active())->count(),
            'openTickets'    => $restrictClients(Ticket::active())->open()->count(),
            'openDeals'      => $restrictClients(Deal::active())->open()->count(),
            'pipelineValue'  => $restrictClients(Deal::active())->open()->sum('value'),
            'revenueMtd'     => $restrictClients(Payment::query())->whereMonth('paid_at', today()->month)->whereYear('paid_at', today()->year)->sum('amount'),
            'overdueInvoices' => $restrictClients(Invoice::active())->overdue()->count(),
            'urgentTickets'  => $restrictClients(Ticket::active())->open()->where('priority', 'urgent')->count(),
            'expiringDomains' => $restrictClients(Domain::active())->expiringSoon(30)->orderBy('expires_at')->take(5)->get(),
            'expiringCount'   => $restrictClients(Domain::active())->expiringSoon(30)->count(),
            'recentClients'  => Client::active()->visibleTo($user)->latest()->take(5)->get(),
            'recentTickets'  => $restrictClients(Ticket::active())->open()->with(['client', 'assignee'])->orderByRaw("FIELD(priority,'urgent','high','medium','low')")->take(5)->get(),
        ])->layout('components.layouts.app', ['header' => 'Dashboard']);
    }
}
