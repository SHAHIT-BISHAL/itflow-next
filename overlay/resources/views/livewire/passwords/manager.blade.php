<div>
    <div class="mb-4 flex items-center justify-between">
        <p class="text-xs text-slate-500 flex items-center gap-1">
            <x-ui.icon name="lock-closed" class="h-3.5 w-3.5" />
            Passwords are encrypted at rest.
        </p>
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Password
        </x-ui.button>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Username</th>
                        <th class="px-3 py-2">Password</th>
                        <th class="px-3 py-2">URL</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($passwords as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-medium text-slate-900">{{ $entry->name }}</td>
                            <td class="px-3 py-3 font-mono text-xs text-slate-600">{{ $entry->username ?: '—' }}</td>
                            <td class="px-3 py-3 font-mono text-xs">
                                @if (in_array($entry->id, $revealed))
                                    <span class="text-slate-900">{{ $entry->decrypted_password }}</span>
                                    <button wire:click="toggleReveal({{ $entry->id }})" class="ml-2 text-slate-400 hover:text-slate-600">
                                        <x-ui.icon name="eye-slash" class="h-4 w-4" />
                                    </button>
                                @else
                                    <span class="tracking-widest text-slate-400">••••••••</span>
                                    <button wire:click="toggleReveal({{ $entry->id }})" class="ml-2 text-slate-400 hover:text-brand-600">
                                        <x-ui.icon name="eye" class="h-4 w-4" />
                                    </button>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-slate-600">
                                @if ($entry->url)
                                    <a href="{{ $entry->url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-brand-700 hover:underline truncate max-w-[12rem] block">
                                        {{ $entry->url }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $entry->id }})" class="text-slate-400 hover:text-brand-600">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="archive({{ $entry->id }})" wire:confirm="Delete this password entry?" class="text-slate-400 hover:text-red-600">
                                        <x-ui.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-slate-500">No passwords stored yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Password' : 'New Password'">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="Name / Service" wire:model="name" :error="$errors->first('name')" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="username" label="Username" wire:model="username" :error="$errors->first('username')" />
                <x-ui.input name="password" type="password" label="Password {{ $editingId ? '(leave blank to keep)' : '' }}" wire:model="password" autocomplete="new-password" :error="$errors->first('password')" />
            </div>

            <x-ui.input name="url" label="URL" wire:model="url" :error="$errors->first('url')" />
            <x-ui.input name="notes" type="textarea" label="Notes" wire:model="notes" rows="2" :error="$errors->first('notes')" />

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
