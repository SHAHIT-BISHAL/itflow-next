<div>
    <div class="mb-4 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Contact
        </x-ui.button>
    </div>

    <x-ui.card>
        <x-ui.table>
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <x-ui.th>Name</x-ui.th>
                        <x-ui.th>Title</x-ui.th>
                        <x-ui.th>Email</x-ui.th>
                        <x-ui.th>Phone</x-ui.th>
                        <x-ui.th>Flags</x-ui.th>
                        <x-ui.th />
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contacts as $contact)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-medium text-slate-900">{{ $contact->name }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $contact->title ?: '—' }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $contact->email ?: '—' }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $contact->phone ?: '—' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex gap-1">
                                    @if ($contact->is_primary)<x-ui.badge color="brand">Primary</x-ui.badge>@endif
                                    @if ($contact->is_billing)<x-ui.badge color="green">Billing</x-ui.badge>@endif
                                    @if ($contact->is_technical)<x-ui.badge color="blue">Technical</x-ui.badge>@endif
                                </div>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $contact->id }})" class="text-slate-400 hover:text-brand-600">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="delete({{ $contact->id }})" wire:confirm="Archive this contact?" class="text-slate-400 hover:text-red-600">
                                        <x-ui.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-slate-500">No contacts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
        </x-ui.table>
    </x-ui.card>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Contact' : 'New Contact'">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="name" label="Name" wire:model="name" :error="$errors->first('name')" />
                <x-ui.input name="title" label="Title" wire:model="title" :error="$errors->first('title')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="email" type="email" label="Email" wire:model="email" :error="$errors->first('email')" />
                <x-ui.input name="phone" label="Phone" wire:model="phone" :error="$errors->first('phone')" />
            </div>

            <x-ui.input name="location_id" type="select" label="Location" wire:model="location_id">
                <option value="">— None —</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </x-ui.input>

            <div class="flex gap-6">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="is_primary" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Primary
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="is_billing" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Billing
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="is_technical" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Technical
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
