<?php

namespace App\Livewire\Passwords;

use App\Models\Client;
use App\Models\Password;
use App\Models\PasswordAccessLog;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
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

    public function mount(Client $client): void
    {
        abort_if(! Auth::user()->canAccessClient($client), 404);

        $this->client = $client;
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'username', 'password', 'url', 'notes']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $entry = Password::where('client_id', $this->client->id)->findOrFail($id);
        $this->editingId = $entry->id;
        $this->name = $entry->name;
        $this->username = $entry->username;
        $this->password = $entry->decrypted_password;
        $this->url = $entry->url;
        $this->notes = $entry->notes;
        $this->showModal = true;

        $this->recordAccess($entry, 'edit_reveal');
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;

        if ($this->editingId) {
            $entry = Password::where('client_id', $this->client->id)->findOrFail($this->editingId);
            $before = AuditLogger::snapshot($entry);
            // If password field left blank on edit, preserve existing encrypted value
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $entry->update($data);
            AuditLogger::record('password.updated', $entry, 'Password record updated.', $before, AuditLogger::snapshot($entry));
        } else {
            $entry = Password::create($data);
            AuditLogger::record('password.created', $entry, 'Password record created.', null, AuditLogger::snapshot($entry));
        }

        $this->closeModal();
    }

    public function toggleReveal(int $id): void
    {
        if (in_array($id, $this->revealed)) {
            $this->revealed = array_values(array_diff($this->revealed, [$id]));
        } else {
            $entry = Password::where('client_id', $this->client->id)->findOrFail($id);
            $this->revealed[] = $id;
            $this->recordAccess($entry, 'reveal');
        }
    }

    public function archive(int $id): void
    {
        $entry = Password::where('client_id', $this->client->id)->findOrFail($id);
        $before = AuditLogger::snapshot($entry);
        $entry->update(['archived_at' => now()]);
        AuditLogger::record('password.archived', $entry, 'Password record archived.', $before, AuditLogger::snapshot($entry));
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
            ->with('latestAccessLog.user')
            ->withCount('accessLogs')
            ->orderBy('name')
            ->get();

        return view('livewire.passwords.manager', ['passwords' => $passwords]);
    }

    protected function recordAccess(Password $entry, string $action): void
    {
        PasswordAccessLog::create([
            'company_id' => $entry->company_id,
            'client_id' => $entry->client_id,
            'password_id' => $entry->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => str(request()->userAgent() ?? '')->limit(1000)->toString(),
            'accessed_at' => now(),
        ]);

        AuditLogger::record("password.{$action}", $entry, 'Password access recorded.', null, null, [
            'client_id' => $entry->client_id,
            'access_action' => $action,
        ]);
    }
}
