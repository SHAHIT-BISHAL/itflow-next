<?php

namespace App\Livewire\Domains;

use App\Models\Domain;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = ''; // '', 'active', 'expiring', 'expired'

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

    public ?int $client_id = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'registrar', 'expires_at', 'auto_renew',
            'dns_provider', 'ssl_expires_at', 'ssl_issuer', 'notes', 'client_id']);
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
        $this->client_id = $domain->client_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            Domain::findOrFail($this->editingId)->update($data);
            $message = 'Domain updated.';
        } else {
            Domain::create($data);
            $message = 'Domain added.';
        }

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function archive(int $id): void
    {
        Domain::findOrFail($id)->update(['archived_at' => now()]);
        session()->flash('success', 'Domain removed.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'registrar', 'expires_at', 'auto_renew',
            'dns_provider', 'ssl_expires_at', 'ssl_issuer', 'notes', 'client_id']);
        $this->resetValidation();
    }

    public function render()
    {
        $query = Domain::query()
            ->with('client')
            ->active()
            ->search($this->search);

        if ($this->filterStatus === 'expiring') {
            $query->expiringSoon();
        } elseif ($this->filterStatus === 'expired') {
            $query->where('expires_at', '<', now());
        } elseif ($this->filterStatus === 'active') {
            $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()->addDays(30)));
        }

        $domains = $query->orderBy('expires_at')->paginate(20);

        $stats = [
            'total' => Domain::active()->count(),
            'expiring' => Domain::active()->expiringSoon()->count(),
            'expired' => Domain::active()->where('expires_at', '<', now())->count(),
        ];

        return view('livewire.domains.index', compact('domains', 'stats'))
            ->layout('components.layouts.app', ['header' => 'Domains & Certificates']);
    }
}
