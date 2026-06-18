<?php

namespace App\Livewire\Domains;

use App\Models\Client;
use App\Models\Domain;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
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

    #[Validate('nullable|integer')]
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
        $user = Auth::user();
        $domain = Domain::findOrFail($id);
        abort_if($domain->client ? ! $user->canAccessClient($domain->client) : $user->hasClientRestrictions(), 404);

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
        $user = Auth::user();

        if ($this->client_id) {
            $client = Client::active()->visibleTo($user)->find($this->client_id);

            if (! $client) {
                $this->addError('client_id', 'Select an accessible client.');
                return;
            }
        } elseif ($user->hasClientRestrictions()) {
            $this->addError('client_id', 'Select an accessible client.');
            return;
        }

        if ($this->editingId) {
            $domain = Domain::findOrFail($this->editingId);
            abort_if($domain->client ? ! $user->canAccessClient($domain->client) : $user->hasClientRestrictions(), 404);

            $before = AuditLogger::snapshot($domain);
            $domain->update($data);
            AuditLogger::record('domain.updated', $domain, 'Domain updated.', $before, AuditLogger::snapshot($domain));
            $message = 'Domain updated.';
        } else {
            $domain = Domain::create($data);
            AuditLogger::record('domain.created', $domain, 'Domain added.', null, AuditLogger::snapshot($domain));
            $message = 'Domain added.';
        }

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function archive(int $id): void
    {
        $user = Auth::user();
        $domain = Domain::findOrFail($id);
        abort_if($domain->client ? ! $user->canAccessClient($domain->client) : $user->hasClientRestrictions(), 404);

        $before = AuditLogger::snapshot($domain);
        $domain->update(['archived_at' => now()]);
        AuditLogger::record('domain.archived', $domain, 'Domain archived.', $before, AuditLogger::snapshot($domain));
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
        $user = Auth::user();

        $query = Domain::query()
            ->with('client')
            ->active()
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->search($this->search);

        if ($this->filterStatus === 'expiring') {
            $query->expiringSoon();
        } elseif ($this->filterStatus === 'expired') {
            $query->where('expires_at', '<', now());
        } elseif ($this->filterStatus === 'active') {
            $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()->addDays(30)));
        }

        $domains = $query->orderBy('expires_at')->paginate(20);

        $statsQuery = Domain::active()
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'expiring' => (clone $statsQuery)->expiringSoon()->count(),
            'expired' => (clone $statsQuery)->where('expires_at', '<', now())->count(),
        ];

        return view('livewire.domains.index', [
            'domains' => $domains,
            'stats' => $stats,
            'clients' => Client::active()->visibleTo($user)->orderBy('name')->get(['id', 'name']),
        ])
            ->layout('components.layouts.app', ['header' => 'Domains & Certificates']);
    }
}
