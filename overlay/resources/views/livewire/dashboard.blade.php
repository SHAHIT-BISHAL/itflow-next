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

    <div class="mt-6">
        <x-ui.card title="Open Tickets">
            <ul class="divide-y divide-slate-100">
                @forelse ($recentTickets as $ticket)
                    <li class="flex items-center justify-between py-3 gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-brand-700 hover:underline truncate block">{{ $ticket->subject }}</a>
                            <p class="text-xs text-slate-500">{{ $ticket->client?->name ?? 'No client' }} · {{ $ticket->assignee?->name ?? 'Unassigned' }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-ui.badge :color="$ticket->priority_color">{{ ucfirst($ticket->priority) }}</x-ui.badge>
                            <span class="text-xs text-slate-400">{{ $ticket->updated_at->diffForHumans() }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-3 text-slate-500">No open tickets.</li>
                @endforelse
            </ul>
            @if ($openTickets > 5)
                <div class="mt-3">
                    <a href="{{ route('tickets.index') }}" class="text-xs text-brand-700 hover:underline">View all {{ $openTickets }} open tickets &rarr;</a>
                </div>
            @endif
        </x-ui.card>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('tickets.index') }}" class="block">
            <x-ui.card class="hover:border-brand-300 transition">
                <p class="text-xs uppercase tracking-wide text-slate-500">Open Tickets</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $openTickets }}</p>
                @if ($urgentTickets > 0)
                    <p class="text-xs text-red-500 font-medium mt-1">{{ $urgentTickets }} urgent</p>
                @endif
            </x-ui.card>
        </a>
        <a href="{{ route('invoices.index') }}" class="block">
            <x-ui.card class="hover:border-brand-300 transition">
                <p class="text-xs uppercase tracking-wide text-slate-500">Revenue MTD</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">${{ number_format($revenueMtd, 0) }}</p>
                @if ($overdueInvoices > 0)
                    <p class="text-xs text-red-500 mt-1">{{ $overdueInvoices }} overdue</p>
                @endif
            </x-ui.card>
        </a>
        <a href="{{ route('deals.index') }}" class="block">
            <x-ui.card class="hover:border-brand-300 transition">
                <p class="text-xs uppercase tracking-wide text-slate-500">Open Deals</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $openDeals }}</p>
                <p class="text-xs text-slate-500 mt-1">${{ number_format($pipelineValue, 0) }} pipeline</p>
            </x-ui.card>
        </a>
    </div>
</div>
