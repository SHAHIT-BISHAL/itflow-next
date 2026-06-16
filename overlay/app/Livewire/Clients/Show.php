<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;

class Show extends Component
{
    public Client $client;

    public string $tab = 'overview';

    public function mount(Client $client): void
    {
        $this->client = $client;
        $client->update(['accessed_at' => now()]);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.clients.show', [
            'client' => $this->client->loadCount(['contacts', 'locations', 'assets', 'documents', 'passwords', 'domains']),
        ])->layout('components.layouts.app', ['header' => $this->client->name]);
    }
}
