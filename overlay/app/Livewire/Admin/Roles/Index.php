<?php

namespace App\Livewire\Admin\Roles;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    /** @var array<int, int> */
    public array $selectedPermissions = [];

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $role = Role::findById($id);

        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->editingId)],
        ]);

        if ($this->editingId) {
            $role = Role::findById($this->editingId);
            $role->update(['name' => $this->name]);
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
        }

        $role->syncPermissions(Permission::whereIn('id', $this->selectedPermissions)->get());

        $this->closeModal();
        session()->flash('success', 'Role saved.');
    }

    public function delete(int $id): void
    {
        $role = Role::findById($id);

        if ($role->name === 'Administrator') {
            session()->flash('error', 'The Administrator role cannot be deleted.');
            return;
        }

        $role->delete();
        session()->flash('success', 'Role deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'selectedPermissions']);
    }

    public function render()
    {
        return view('livewire.admin.roles.index', [
            'roles' => Role::with('permissions')->withCount('users')->orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get()->groupBy(fn ($p) => str($p->name)->before(' ')->title()),
        ])->layout('components.layouts.app', ['header' => 'Roles & Permissions']);
    }
}
