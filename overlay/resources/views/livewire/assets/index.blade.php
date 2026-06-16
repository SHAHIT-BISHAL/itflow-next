<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-72">
                <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search assets..."
                       class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm focus:border-brand-500 focus:ring-brand-500" />
            </div>
            <select wire:model.live="filterType"
                    class="rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-8 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All types</option>
                @foreach (['Hardware', 'Software', 'Network', 'Other'] as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Asset
        </x-ui.button>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Client</th>
                        <th class="px-3 py-2">Make / Model</th>
                        <th class="px-3 py-2">Serial</th>
                        <th class="px-3 py-2">IP</th>
                        <th class="px-3 py-2">Warranty</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assets as $asset)
                        @php
                            $status = $asset->warranty_status;
                            $warrantyClass = match($status) {
                                'expired'  => 'text-red-600',
                                'expiring' => 'text-yellow-600',
                                default    => 'text-slate-600',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-medium text-slate-900">{{ $asset->name }}</td>
                            <td class="px-3 py-3">
                                <x-ui.badge :color="match($asset->asset_type) {
                                    'Hardware' => 'blue',
                                    'Software' => 'purple',
                                    'Network'  => 'green',
                                    default    => 'gray',
                                }">{{ $asset->asset_type }}</x-ui.badge>
                            </td>
                            <td class="px-3 py-3 text-slate-600">
                                @if ($asset->client)
                                    <a href="{{ route('clients.show', $asset->client) }}" class="text-brand-700 hover:underline">
                                        {{ $asset->client->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-600">
                                {{ implode(' ', array_filter([$asset->manufacturer, $asset->model])) ?: '—' }}
                            </td>
                            <td class="px-3 py-3 font-mono text-xs text-slate-600">{{ $asset->serial_number ?: '—' }}</td>
                            <td class="px-3 py-3 font-mono text-xs text-slate-600">{{ $asset->ip_address ?: '—' }}</td>
                            <td class="px-3 py-3 text-xs {{ $warrantyClass }}">
                                @if ($asset->warranty_expires_at)
                                    {{ $asset->warranty_expires_at->format('d M Y') }}
                                    @if ($status === 'expired') <span>(expired)</span>
                                    @elseif ($status === 'expiring') <span>(soon)</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $asset->id }})" class="text-slate-400 hover:text-brand-600">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="archive({{ $asset->id }})" wire:confirm="Archive this asset?" class="text-slate-400 hover:text-red-600">
                                        <x-ui.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-slate-500">No assets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assets->links() }}</div>
    </x-ui.card>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Asset' : 'New Asset'">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="name" label="Name" wire:model="name" :error="$errors->first('name')" class="col-span-2" />

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                    <select wire:model="asset_type" name="asset_type"
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        @foreach (['Hardware', 'Software', 'Network', 'Other'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <x-ui.input name="manufacturer" label="Manufacturer" wire:model="manufacturer" :error="$errors->first('manufacturer')" />
                <x-ui.input name="model" label="Model" wire:model="model" :error="$errors->first('model')" />
                <x-ui.input name="serial_number" label="Serial Number" wire:model="serial_number" :error="$errors->first('serial_number')" />
                <x-ui.input name="ip_address" label="IP Address" wire:model="ip_address" :error="$errors->first('ip_address')" />
                <x-ui.input name="mac_address" label="MAC Address" wire:model="mac_address" :error="$errors->first('mac_address')" />
                <x-ui.input name="os" label="OS" wire:model="os" :error="$errors->first('os')" />
                <x-ui.input name="os_version" label="OS Version" wire:model="os_version" :error="$errors->first('os_version')" />
                <x-ui.input name="purchased_at" type="date" label="Purchase Date" wire:model="purchased_at" :error="$errors->first('purchased_at')" />
                <x-ui.input name="warranty_expires_at" type="date" label="Warranty Expires" wire:model="warranty_expires_at" :error="$errors->first('warranty_expires_at')" />
            </div>

            <x-ui.input name="notes" type="textarea" label="Notes" wire:model="notes" rows="2" :error="$errors->first('notes')" />

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
