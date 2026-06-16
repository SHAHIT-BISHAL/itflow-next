<div>
    <div class="mb-6 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Role
        </x-ui.button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($roles as $role)
            <x-ui.card>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $role->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $role->users_count }} user(s) · {{ $role->permissions->count() }} permission(s)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="edit({{ $role->id }})" class="text-slate-400 hover:text-brand-600">
                            <x-ui.icon name="pencil" class="h-4 w-4" />
                        </button>
                        @if ($role->name !== 'Administrator')
                            <button wire:click="delete({{ $role->id }})" wire:confirm="Delete this role?" class="text-slate-400 hover:text-red-600">
                                <x-ui.icon name="trash" class="h-4 w-4" />
                            </button>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Role' : 'New Role'" maxWidth="2xl">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="Role Name" wire:model="name" :error="$errors->first('name')" />

            <div>
                <p class="mb-2 text-sm font-medium text-slate-700">Permissions</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 max-h-72 overflow-y-auto pr-2">
                    @foreach ($permissions as $group => $perms)
                        <div>
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $group }}</p>
                            @foreach ($perms as $permission)
                                <label class="flex items-center gap-2 text-sm text-slate-700 py-0.5">
                                    <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    {{ $permission->name }}
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
