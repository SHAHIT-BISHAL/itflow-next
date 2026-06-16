<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:w-80">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search clients..."
                   class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm focus:border-brand-500 focus:ring-brand-500" />
        </div>

        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Client
        </x-ui.button>
    </div>

    <x-ui.card>
        <x-ui.table>
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <x-ui.th />
                        <x-ui.th>Name</x-ui.th>
                        <x-ui.th>Type</x-ui.th>
                        <x-ui.th>Contacts</x-ui.th>
                        <x-ui.th>Locations</x-ui.th>
                        <x-ui.th>Net Terms</x-ui.th>
                        <x-ui.th />
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3">
                                <button wire:click="toggleFavorite({{ $client->id }})" class="text-slate-300 hover:text-yellow-400 {{ $client->is_favorite ? 'text-yellow-400' : '' }}">
                                    <x-ui.icon name="star" class="h-5 w-5" fill="{{ $client->is_favorite ? 'currentColor' : 'none' }}" />
                                </button>
                            </td>
                            <td class="px-3 py-3">
                                <a href="{{ route('clients.show', $client) }}" class="font-medium text-brand-700 hover:underline">
                                    {{ $client->name }}
                                </a>
                            </td>
                            <td class="px-3 py-3 text-slate-600">{{ $client->type ?: '—' }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $client->contacts_count }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $client->locations_count }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $client->net_terms }} days</td>
                            <td class="px-3 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $client->id }})" class="text-slate-400 hover:text-brand-600">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="archive({{ $client->id }})" wire:confirm="Archive this client?" class="text-slate-400 hover:text-red-600">
                                        <x-ui.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-slate-500">No clients found.</td>
                        </tr>
                    @endforelse
                </tbody>
        </x-ui.table>

        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Client' : 'New Client'">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="Name" wire:model="name" :error="$errors->first('name')" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="type" label="Type" wire:model="type" placeholder="e.g. Customer, Lead" :error="$errors->first('type')" />
                <x-ui.input name="net_terms" type="number" label="Net Terms (days)" wire:model="net_terms" :error="$errors->first('net_terms')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="website" label="Website" wire:model="website" :error="$errors->first('website')" />
                <x-ui.input name="rate" type="number" label="Default Rate" wire:model="rate" step="0.01" :error="$errors->first('rate')" />
            </div>

            <x-ui.input name="notes" type="textarea" label="Notes" wire:model="notes" rows="3" :error="$errors->first('notes')" />

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
