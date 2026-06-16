<div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Active Clients</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $clientCount }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Leads</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $leadCount }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Contacts</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $contactCount }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Assets</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $assetCount }}</p>
        </x-ui.card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card title="Recently added clients">
            <ul class="divide-y divide-slate-100">
                @forelse ($recentClients as $client)
                    <li class="flex items-center justify-between py-3">
                        <a href="{{ route('clients.show', $client) }}" class="font-medium text-brand-700 hover:underline">{{ $client->name }}</a>
                        <span class="text-sm text-slate-500">{{ $client->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="py-3 text-slate-500">No clients yet — <a href="{{ route('clients.index') }}" class="text-brand-700 hover:underline">add your first client</a>.</li>
                @endforelse
            </ul>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-slate-700">Expiring Domains / SSL</p>
                @if ($expiringCount > 0)
                    <x-ui.badge color="yellow">{{ $expiringCount }} expiring</x-ui.badge>
                @endif
            </div>
            @forelse ($expiringDomains as $domain)
                @php
                    $status = $domain->expiry_status;
                    $days = $domain->expires_at?->diffInDays(now());
                @endphp
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $domain->name }}</p>
                        <p class="text-xs text-slate-500">{{ $domain->client?->name ?? 'No client' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs {{ $status === 'expired' ? 'text-red-600 font-medium' : 'text-yellow-600 font-medium' }}">
                            {{ $domain->expires_at?->format('d M Y') }}
                        </p>
                        <p class="text-xs text-slate-400">{{ $domain->expires_at?->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="py-4 text-sm text-slate-500">No domains expiring in the next 30 days.</p>
            @endforelse
            @if ($expiringCount > 0)
                <div class="mt-3">
                    <a href="{{ route('domains.index') }}?filter=expiring" class="text-xs text-brand-700 hover:underline">View all expiring domains &rarr;</a>
                </div>
            @endif
        </x-ui.card>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Open Tickets</p>
            <p class="mt-2 text-3xl font-semibold text-slate-400">—</p>
            <p class="text-xs text-slate-400">Coming in Phase 3</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Revenue MTD</p>
            <p class="mt-2 text-3xl font-semibold text-slate-400">—</p>
            <p class="text-xs text-slate-400">Coming in Phase 5</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Open Deals</p>
            <p class="mt-2 text-3xl font-semibold text-slate-400">—</p>
            <p class="text-xs text-slate-400">Coming in Phase 4</p>
        </x-ui.card>
    </div>
</div>
