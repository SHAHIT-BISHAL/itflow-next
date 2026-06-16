<?php

namespace App\Livewire\Locations;

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
    public ?string $address = null;

    #[Validate('nullable|string|max:255')]
    public ?string $city = null;

    #[Validate('nullable|string|max:255')]
    public ?string $state = null;

    #[Validate('nullable|string|max:50')]
    public ?string $zip = null;

    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;

    public bool $is_primary = false;

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $location = $this->client->locations()->findOrFail($id);

        $this->editingId = $location->id;
        $this->name = $location->name;
        $this->address = $location->address;
        $this->city = $location->city;
        $this->state = $location->state;
        $this->zip = $location->zip;
        $this->phone = $location->phone;
        $this->is_primary = $location->is_primary;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;
        $data['is_primary'] = $this->is_primary;

        if ($this->is_primary) {
            // Only one primary location per client
            $this->client->locations()->update(['is_primary' => false]);
        }

        if ($this->editingId) {
            $this->client->locations()->findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Location updated.');
        } else {
            $this->client->locations()->create($data);
            session()->flash('success', 'Location created.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $this->client->locations()->findOrFail($id)->update(['archived_at' => now()]);
        session()->flash('success', 'Location archived.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'address', 'city', 'state', 'zip', 'phone', 'is_primary']);
    }

    public function render()
    {
        return view('livewire.locations.manager', [
            'locations' => $this->client->locations()->active()->orderByDesc('is_primary')->orderBy('name')->get(),
        ]);
    }
}
