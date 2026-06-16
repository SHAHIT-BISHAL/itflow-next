<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Domain;
use App\Models\Deal;
use App\Models\Ticket;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard', [
            'clientCount'    => Client::active()->count(),
            'leadCount'      => Client::active()->where('is_lead', true)->count(),
            'contactCount'   => Contact::whereHas('client')->active()->count(),
            'assetCount'     => Asset::active()->count(),
            'openTickets'    => Ticket::active()->open()->count(),
            'openDeals'      => Deal::active()->open()->count(),
            'pipelineValue'  => Deal::active()->open()->sum('value'),
            'urgentTickets'  => Ticket::active()->open()->where('priority', 'urgent')->count(),
            'expiringDomains' => Domain::active()->expiringSoon(30)->orderBy('expires_at')->take(5)->get(),
            'expiringCount'   => Domain::active()->expiringSoon(30)->count(),
            'recentClients'  => Client::active()->latest()->take(5)->get(),
            'recentTickets'  => Ticket::active()->open()->with(['client', 'assignee'])->orderByRaw("FIELD(priority,'urgent','high','medium','low')")->take(5)->get(),
        ])->layout('components.layouts.app', ['header' => 'Dashboard']);
    }
}
