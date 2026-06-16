<div>
    <div class="mb-4 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            Add Domain
        </x-ui.button>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-3 py-2">Domain</th>
                        <th class="px-3 py-2">Registrar</th>
                        <th class="px-3 py-2">Expires</th>
                        <th class="px-3 py-2">SSL Expires</th>
                        <th class="px-3 py-2">Auto-renew</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($domains as $domain)
                        @php
                            $expStatus = $domain->expiry_status;
                            $sslStatus = $domain->ssl_expiry_status;
                            $expiryClass = match($expStatus) {
                                'expired'  => 'text-red-600 font-medium',
                                'expiring' => 'text-yellow-600 font-medium',
                                default    => 'text-slate-600',
                            };
                            $sslClass = match($sslStatus) {
                                'expired'  => 'text-red-600 font-medium',
                                'expiring' => 'text-yellow-600 font-medium',
                                default    => 'text-slate-600',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-medium text-slate-900">{{ $domain->name }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $domain->registrar ?: '—' }}</td>
                            <td class="px-3 py-3 text-xs {{ $expiryClass }}">
                                {{ $domain->expires_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-xs {{ $sslClass }}">
                                {{ $domain->ssl_expires_at?->format('d M Y') ?? '—' }}
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
                            <td colspan="6" class="px-3 py-8 text-center text-slate-500">No domains yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @include('livewire.domains._form-modal')
</div>
