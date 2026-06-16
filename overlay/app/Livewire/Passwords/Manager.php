<?php

namespace App\Livewire\Passwords;

use App\Models\Password;
use App\Models\Client;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Manager extends Component
{
    public Client $client;

    public bool $showModal = false;
    public ?int $editingId = null;

    // Track which rows have the password revealed
    public array $revealed = [];

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $username = null;

    #[Validate('nullable|string|max:1000')]
    public ?string $password = null;

    #[Validate('nullable|url|max:255')]
    public ?string $url = null;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'username', 'password', 'url', 'notes']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $entry = Password::findOrFail($id);
        $this->editingId = $entry->id;
        $this->name = $entry->name;
        $this->username = $entry->username;
        $this->password = $entry->decrypted_password;
        $this->url = $entry->url;
        $this->notes = $entry->notes;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;

        if ($this->editingId) {
            $entry = Password::findOrFail($this->editingId);
            // If password field left blank on edit, preserve existing encrypted value
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $entry->update($data);
        } else {
            Password::create($data);
        }

        $this->closeModal();
    }

    public function toggleReveal(int $id): void
    {
        if (in_array($id, $this->revealed)) {
            $this->revealed = array_values(array_diff($this->revealed, [$id]));
        } else {
            $this->revealed[] = $id;
        }
    }

    public function archive(int $id): void
    {
        Password::findOrFail($id)->update(['archived_at' => now()]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'username', 'password', 'url', 'notes']);
        $this->resetValidation();
    }

    public function render()
    {
        $passwords = Password::query()
            ->where('client_id', $this->client->id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('livewire.passwords.manager', ['passwords' => $passwords]);
    }
}
