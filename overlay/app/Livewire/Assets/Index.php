<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
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
    public string $filterType = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:100')]
    public string $asset_type = 'Hardware';

    #[Validate('nullable|string|max:255')]
    public ?string $manufacturer = null;

    #[Validate('nullable|string|max:255')]
    public ?string $model = null;

    #[Validate('nullable|string|max:255')]
    public ?string $serial_number = null;

    #[Validate('nullable|ip')]
    public ?string $ip_address = null;

    #[Validate('nullable|string|max:50')]
    public ?string $mac_address = null;

    #[Validate('nullable|string|max:100')]
    public ?string $os = null;

    #[Validate('nullable|string|max:100')]
    public ?string $os_version = null;

    #[Validate('nullable|date')]
    public ?string $purchased_at = null;

    #[Validate('nullable|date')]
    public ?string $warranty_expires_at = null;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    #[Validate('required|integer')]
    public ?int $client_id = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'asset_type', 'manufacturer', 'model',
            'serial_number', 'ip_address', 'mac_address', 'os', 'os_version',
            'purchased_at', 'warranty_expires_at', 'notes', 'client_id']);
        $this->asset_type = 'Hardware';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $asset = Asset::findOrFail($id);
        abort_if(! $asset->client || ! Auth::user()->canAccessClient($asset->client), 404);

        $this->editingId = $asset->id;
        $this->name = $asset->name;
        $this->asset_type = $asset->asset_type;
        $this->manufacturer = $asset->manufacturer;
        $this->model = $asset->model;
        $this->serial_number = $asset->serial_number;
        $this->ip_address = $asset->ip_address;
        $this->mac_address = $asset->mac_address;
        $this->os = $asset->os;
        $this->os_version = $asset->os_version;
        $this->purchased_at = $asset->purchased_at?->format('Y-m-d');
        $this->warranty_expires_at = $asset->warranty_expires_at?->format('Y-m-d');
        $this->notes = $asset->notes;
        $this->client_id = $asset->client_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $client = Client::active()->visibleTo(Auth::user())->find($this->client_id);

        if (! $client) {
            $this->addError('client_id', 'Select an accessible client.');
            return;
        }

        $data['client_id'] = $client->id;

        if ($this->editingId) {
            $asset = Asset::findOrFail($this->editingId);
            abort_if(! $asset->client || ! Auth::user()->canAccessClient($asset->client), 404);

            $before = AuditLogger::snapshot($asset);
            $asset->update($data);
            AuditLogger::record('asset.updated', $asset, 'Asset updated.', $before, AuditLogger::snapshot($asset));
            $message = 'Asset updated.';
        } else {
            $asset = Asset::create($data);
            AuditLogger::record('asset.created', $asset, 'Asset created.', null, AuditLogger::snapshot($asset));
            $message = 'Asset created.';
        }

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function archive(int $id): void
    {
        $asset = Asset::findOrFail($id);
        abort_if(! $asset->client || ! Auth::user()->canAccessClient($asset->client), 404);

        $before = AuditLogger::snapshot($asset);
        $asset->update(['archived_at' => now()]);
        AuditLogger::record('asset.archived', $asset, 'Asset archived.', $before, AuditLogger::snapshot($asset));
        session()->flash('success', 'Asset archived.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'asset_type', 'manufacturer', 'model',
            'serial_number', 'ip_address', 'mac_address', 'os', 'os_version',
            'purchased_at', 'warranty_expires_at', 'notes', 'client_id']);
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $assets = Asset::query()
            ->with('client')
            ->active()
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->search($this->search)
            ->when($this->filterType, fn ($q) => $q->where('asset_type', $this->filterType))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.assets.index', [
            'assets' => $assets,
            'clients' => Client::active()->visibleTo($user)->orderBy('name')->get(['id', 'name']),
        ])
            ->layout('components.layouts.app', ['header' => 'Assets']);
    }
}
