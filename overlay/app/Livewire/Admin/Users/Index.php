<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|min:8')]
    public string $password = '';

    #[Validate('required|exists:roles,id')]
    public ?int $role_id = null;

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role_id = $user->roles->first()?->id;

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($this->editingId ?? 'NULL') . ',id',
            'role_id' => 'required|exists:roles,id',
            'password' => $this->editingId ? 'nullable|string|min:8' : 'required|string|min:8',
        ];

        $data = $this->validate($rules);
        $role = Role::findById($data['role_id']);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);
            if (! empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }
            $message = 'User updated.';
        } else {
            $user = User::create([
                'company_id' => auth()->user()->company_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $message = 'User created.';
        }

        $user->syncRoles([$role]);

        $this->closeModal();
        session()->flash('success', $message);
    }

    public function archive(int $id): void
    {
        User::findOrFail($id)->update(['archived_at' => now(), 'status' => 'archived']);
        session()->flash('success', 'User archived.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'role_id']);
    }

    public function render()
    {
        return view('livewire.admin.users.index', [
            'users' => User::active()->with('roles')->orderBy('name')->paginate(15),
            'roles' => Role::orderBy('name')->get(),
        ])->layout('components.layouts.app', ['header' => 'Users']);
    }
}
