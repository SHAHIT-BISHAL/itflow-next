<div>
    {{-- Stats bar --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <x-ui.card class="cursor-pointer {{ $filterStatus === '' ? 'ring-2 ring-brand-500' : '' }}" wire:click="$set('filterStatus', '')">
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Domains</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </x-ui.card>
        <x-ui.card class="cursor-pointer {{ $filterStatus === 'expiring' ? 'ring-2 ring-yellow-400' : '' }}" wire:click="$set('filterStatus', 'expiring')">
            <p class="text-xs uppercase tracking-wide text-slate-500">Expiring ≤ 30 days</p>
            <p class="mt-1 text-2xl font-bold {{ $stats['expiring'] > 0 ? 'text-yellow-600' : 'text-slate-900' }}">{{ $stats['expiring'] }}</p>
        </x-ui.card>
        <x-ui.card class="cursor-pointer {{ $filterStatus === 'expired' ? 'ring-2 ring-red-400' : '' }}" wire:click="$set('filterStatus', 'expired')">
            <p class="text-xs uppercase tracking-wide text-slate-500">Expired</p>
            <p class="mt-1 text-2xl font-bold {{ $stats['expired'] > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $stats['expired'] }}</p>
        </x-ui.card>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:w-72">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search domains..."
                   class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm focus:border-brand-500 focus:ring-brand-500" />
        </div>
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            Add Domain
        </x-ui.button>
    </div>

    <x-ui.card>
        <x-ui.table>
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <x-ui.th>Domain</x-ui.th>
                        <x-ui.th>Client</x-ui.th>
                        <x-ui.th>Registrar</x-ui.th>
                        <x-ui.th>Domain Expires</x-ui.th>
                        <x-ui.th>SSL Expires</x-ui.th>
                        <x-ui.th>Auto-renew</x-ui.th>
                        <x-ui.th />
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($domains as $domain)
                        @php
                            $expStatus = $domain->expiry_status;
                            $sslStatus = $domain->ssl_expiry_status;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-medium text-slate-900">{{ $domain->name }}</td>
                            <td class="px-3 py-3 text-slate-600">
                                @if ($domain->client)
                                    <a href="{{ route('clients.show', $domain->client) }}" class="text-brand-700 hover:underline">
                                        {{ $domain->client->name }}
                                    </a>
                                @else —
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-600">{{ $domain->registrar ?: '—' }}</td>
                            <td class="px-3 py-3 text-xs">
                                @if ($domain->expires_at)
                                    <span class="{{ match($expStatus) { 'expired' => 'text-red-600 font-medium', 'expiring' => 'text-yellow-600 font-medium', default => 'text-slate-600' } }}">
                                        {{ $domain->expires_at->format('d M Y') }}
                                        @if ($expStatus === 'expired') · expired
                                        @elseif ($expStatus === 'expiring') · {{ $domain->expires_at->diffForHumans() }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs">
                                @if ($domain->ssl_expires_at)
                                    <span class="{{ match($sslStatus) { 'expired' => 'text-red-600 font-medium', 'expiring' => 'text-yellow-600 font-medium', default => 'text-slate-600' } }}">
                                        {{ $domain->ssl_expires_at->format('d M Y') }}
                                        @if ($sslStatus === 'expired') · expired
                                        @elseif ($sslStatus === 'expiring') · {{ $domain->ssl_expires_at->diffForHumans() }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @if ($domain->auto_renew)
                                    <x-ui.badge color="green">Yes</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray">No</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $domain->id }})" class="text-slate-400 hover:text-brand-600">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="archive({{ $domain->id }})" wire:confirm="Remove this domain?" class="text-slate-400 hover:text-red-600">
                                        <x-ui.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-slate-500">No domains found.</td>
                        </tr>
                    @endforelse
                </tbody>
        </x-ui.table>
        <div class="mt-4">{{ $domains->links() }}</div>
    </x-ui.card>

    @include('livewire.domains._form-modal')
</div>
