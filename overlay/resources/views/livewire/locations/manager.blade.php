<div>
    <div class="mb-4 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Location
        </x-ui.button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($locations as $location)
            <x-ui.card>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">
                            {{ $location->name }}
                            @if ($location->is_primary)
                                <x-ui.badge color="brand" class="ml-1">Primary</x-ui.badge>
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $location->address }}<br>
                            {{ trim(implode(', ', array_filter([$location->city, $location->state, $location->zip]))) }}
                        </p>
                        @if ($location->phone)
                            <p class="mt-1 text-sm text-slate-500">{{ $location->phone }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="edit({{ $location->id }})" class="text-slate-400 hover:text-brand-600">
                            <x-ui.icon name="pencil" class="h-4 w-4" />
                        </button>
                        <button wire:click="delete({{ $location->id }})" wire:confirm="Archive this location?" class="text-slate-400 hover:text-red-600">
                            <x-ui.icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <p class="col-span-full text-center text-slate-500">No locations yet.</p>
        @endforelse
    </div>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Location' : 'New Location'">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="Name" wire:model="name" :error="$errors->first('name')" />
            <x-ui.input name="address" label="Address" wire:model="address" :error="$errors->first('address')" />

            <div class="grid grid-cols-3 gap-4">
                <x-ui.input name="city" label="City" wire:model="city" :error="$errors->first('city')" />
                <x-ui.input name="state" label="State" wire:model="state" :error="$errors->first('state')" />
                <x-ui.input name="zip" label="Zip" wire:model="zip" :error="$errors->first('zip')" />
            </div>

            <x-ui.input name="phone" label="Phone" wire:model="phone" :error="$errors->first('phone')" />

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="is_primary" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Primary location
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
