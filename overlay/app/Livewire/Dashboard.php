<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Domain;
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
            'expiringDomains' => Domain::active()->expiringSoon(30)->orderBy('expires_at')->take(5)->get(),
            'expiringCount'   => Domain::active()->expiringSoon(30)->count(),
            'recentClients'  => Client::active()->latest()->take(5)->get(),
        ])->layout('components.layouts.app', ['header' => 'Dashboard']);
    }
}
