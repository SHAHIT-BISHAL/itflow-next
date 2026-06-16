<?php

namespace App\Livewire\Domains;

use App\Models\Domain;
use App\Models\Client;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Manager extends Component
{
    public Client $client;

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $registrar = null;

    #[Validate('nullable|date')]
    public ?string $expires_at = null;

    #[Validate('boolean')]
    public bool $auto_renew = false;

    #[Validate('nullable|string|max:255')]
    public ?string $dns_provider = null;

    #[Validate('nullable|date')]
    public ?string $ssl_expires_at = null;

    #[Validate('nullable|string|max:255')]
    public ?string $ssl_issuer = null;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'registrar', 'expires_at', 'auto_renew',
            'dns_provider', 'ssl_expires_at', 'ssl_issuer', 'notes']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $domain = Domain::findOrFail($id);
        $this->editingId = $domain->id;
        $this->name = $domain->name;
        $this->registrar = $domain->registrar;
        $this->expires_at = $domain->expires_at?->format('Y-m-d');
        $this->auto_renew = $domain->auto_renew;
        $this->dns_provider = $domain->dns_provider;
        $this->ssl_expires_at = $domain->ssl_expires_at?->format('Y-m-d');
        $this->ssl_issuer = $domain->ssl_issuer;
        $this->notes = $domain->notes;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;

        if ($this->editingId) {
            Domain::findOrFail($this->editingId)->update($data);
        } else {
            Domain::create($data);
        }

        $this->closeModal();
    }

    public function archive(int $id): void
    {
        Domain::findOrFail($id)->update(['archived_at' => now()]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'registrar', 'expires_at', 'auto_renew',
            'dns_provider', 'ssl_expires_at', 'ssl_issuer', 'notes']);
        $this->resetValidation();
    }

    public function render()
    {
        $domains = Domain::query()
            ->where('client_id', $this->client->id)
            ->active()
            ->orderBy('expires_at')
            ->get();

        return view('livewire.domains.manager', ['domains' => $domains]);
    }
}
