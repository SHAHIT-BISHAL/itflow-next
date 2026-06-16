<div>
    <div class="mb-6 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New User
        </x-ui.button>
    </div>

    <x-ui.card>
        <x-ui.table>
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <x-ui.th>Name</x-ui.th>
                        <x-ui.th>Email</x-ui.th>
                        <x-ui.th>Role</x-ui.th>
                        <x-ui.th>Status</x-ui.th>
                        <x-ui.th />
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $user->email }}</td>
                            <td class="px-3 py-3">
                                @foreach ($user->roles as $role)
                                    <x-ui.badge color="brand">{{ $role->name }}</x-ui.badge>
                                @endforeach
                            </td>
                            <td class="px-3 py-3">
                                <x-ui.badge :color="$user->status === 'active' ? 'green' : 'slate'">{{ ucfirst($user->status) }}</x-ui.badge>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $user->id }})" class="text-slate-400 hover:text-brand-600">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    @if ($user->id !== auth()->id())
                                        <button wire:click="archive({{ $user->id }})" wire:confirm="Archive this user?" class="text-slate-400 hover:text-red-600">
                                            <x-ui.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
        </x-ui.table>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit User' : 'New User'">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="Name" wire:model="name" :error="$errors->first('name')" />
            <x-ui.input name="email" type="email" label="Email" wire:model="email" :error="$errors->first('email')" />
            <x-ui.input name="password" type="password" :label="$editingId ? 'New Password (leave blank to keep current)' : 'Password'" wire:model="password" :error="$errors->first('password')" />

            <x-ui.input name="role_id" type="select" label="Role" wire:model="role_id" :error="$errors->first('role_id')">
                <option value="">— Select role —</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </x-ui.input>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
