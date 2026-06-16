<?php

namespace App\Livewire\Contacts;

use App\Models\Client;
use App\Models\Contact;
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
    public ?string $title = null;

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;

    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;

    #[Validate('nullable|integer')]
    public ?int $location_id = null;

    public bool $is_primary = false;

    public bool $is_billing = false;

    public bool $is_technical = false;

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $contact = $this->client->contacts()->findOrFail($id);

        $this->editingId = $contact->id;
        $this->name = $contact->name;
        $this->title = $contact->title;
        $this->email = $contact->email;
        $this->phone = $contact->phone;
        $this->location_id = $contact->location_id;
        $this->is_primary = $contact->is_primary;
        $this->is_billing = $contact->is_billing;
        $this->is_technical = $contact->is_technical;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;
        $data['is_primary'] = $this->is_primary;
        $data['is_billing'] = $this->is_billing;
        $data['is_technical'] = $this->is_technical;

        if ($this->editingId) {
            $this->client->contacts()->findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Contact updated.');
        } else {
            $this->client->contacts()->create($data);
            session()->flash('success', 'Contact created.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $this->client->contacts()->findOrFail($id)->update(['archived_at' => now()]);
        session()->flash('success', 'Contact archived.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'title', 'email', 'phone', 'location_id', 'is_primary', 'is_billing', 'is_technical']);
    }

    public function render()
    {
        return view('livewire.contacts.manager', [
            'contacts' => $this->client->contacts()->active()->orderByDesc('is_primary')->orderBy('name')->get(),
            'locations' => $this->client->locations()->active()->get(),
        ]);
    }
}
