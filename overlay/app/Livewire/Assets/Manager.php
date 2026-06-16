<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
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

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'asset_type', 'manufacturer', 'model',
            'serial_number', 'ip_address', 'mac_address', 'os', 'os_version',
            'purchased_at', 'warranty_expires_at', 'notes']);
        $this->asset_type = 'Hardware';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $asset = Asset::findOrFail($id);
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
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;

        if ($this->editingId) {
            Asset::findOrFail($this->editingId)->update($data);
        } else {
            Asset::create($data);
        }

        $this->closeModal();
    }

    public function archive(int $id): void
    {
        Asset::findOrFail($id)->update(['archived_at' => now()]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'asset_type', 'manufacturer', 'model',
            'serial_number', 'ip_address', 'mac_address', 'os', 'os_version',
            'purchased_at', 'warranty_expires_at', 'notes']);
        $this->resetValidation();
    }

    public function render()
    {
        $assets = Asset::query()
            ->where('client_id', $this->client->id)
            ->active()
            ->orderBy('asset_type')
            ->orderBy('name')
            ->get();

        return view('livewire.assets.manager', ['assets' => $assets]);
    }
}
