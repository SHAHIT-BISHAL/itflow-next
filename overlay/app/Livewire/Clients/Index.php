<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $type = null;

    #[Validate('nullable|url|max:255')]
    public ?string $website = null;

    #[Validate('nullable|numeric')]
    public ?string $rate = null;

    #[Validate('required|integer|min:0')]
    public int $net_terms = 30;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'type', 'website', 'rate', 'net_terms', 'notes']);
        $this->net_terms = 30;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $client = Client::findOrFail($id);
        abort_if(! Auth::user()->canAccessClient($client), 404);

        $this->editingId = $client->id;
        $this->name = $client->name;
        $this->type = $client->type;
        $this->website = $client->website;
        $this->rate = $client->rate;
        $this->net_terms = $client->net_terms;
        $this->notes = $client->notes;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $client = Client::findOrFail($this->editingId);
            abort_if(! Auth::user()->canAccessClient($client), 404);

            $before = AuditLogger::snapshot($client);
            $client->update($data);
            AuditLogger::record('client.updated', $client, 'Client updated.', $before, AuditLogger::snapshot($client));
            $message = 'Client updated.';
        } else {
            $client = Client::create($data);
            AuditLogger::record('client.created', $client, 'Client created.', null, AuditLogger::snapshot($client));
            $message = 'Client created.';
        }

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function toggleFavorite(int $id): void
    {
        $client = Client::findOrFail($id);
        abort_if(! Auth::user()->canAccessClient($client), 404);

        $before = AuditLogger::snapshot($client);
        $client->update(['is_favorite' => ! $client->is_favorite]);
        AuditLogger::record('client.favorite_toggled', $client, 'Client favorite toggled.', $before, AuditLogger::snapshot($client));
    }

    public function archive(int $id): void
    {
        $client = Client::findOrFail($id);
        abort_if(! Auth::user()->canAccessClient($client), 404);

        $before = AuditLogger::snapshot($client);
        $client->update(['archived_at' => now()]);
        AuditLogger::record('client.archived', $client, 'Client archived.', $before, AuditLogger::snapshot($client));
        session()->flash('success', 'Client archived.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'type', 'website', 'rate', 'net_terms', 'notes']);
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $clients = Client::query()
            ->active()
            ->visibleTo($user)
            ->search($this->search)
            ->orderByDesc('is_favorite')
            ->orderBy('name')
            ->withCount(['contacts', 'locations'])
            ->paginate(15);

        return view('livewire.clients.index', [
            'clients' => $clients,
        ])->layout('components.layouts.app', ['header' => 'Clients']);
    }
}
