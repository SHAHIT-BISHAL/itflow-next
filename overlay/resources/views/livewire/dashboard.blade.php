<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.metric-card label="Active Clients" :value="$clientCount" icon="building-office" color="sky" :meta="$leadCount . ' leads in pipeline'" />
        <x-ui.metric-card label="Contacts" :value="$contactCount" icon="user-group" color="slate" :meta="$assetCount . ' documented assets'" />
        <x-ui.metric-card label="Open Tickets" :value="$openTickets" icon="ticket" :color="$urgentTickets > 0 ? 'red' : 'amber'" :meta="$urgentTickets > 0 ? $urgentTickets . ' urgent tickets' : 'No urgent tickets'" />
        <x-ui.metric-card label="Revenue MTD" :value="'$' . number_format($revenueMtd, 0)" icon="banknotes" color="emerald" :meta="$overdueInvoices > 0 ? $overdueInvoices . ' overdue invoices' : 'No overdue invoices'" />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-ui.card title="Ticket Queue" class="xl:col-span-2">
            <div class="divide-y divide-slate-100">
                @forelse ($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" wire:navigate class="flex items-center justify-between gap-4 py-3.5 hover:bg-slate-50 -mx-5 px-5">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-semibold text-slate-950">{{ $ticket->subject }}</p>
                                @if ($ticket->source === 'email')
                                    <x-ui.icon name="envelope" class="h-3.5 w-3.5 shrink-0 text-slate-400" />
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $ticket->client?->name ?? 'No client' }} - {{ $ticket->assignee?->name ?? 'Unassigned' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <x-ui.badge :color="$ticket->priority_color">{{ ucfirst($ticket->priority) }}</x-ui.badge>
                            <span class="hidden text-xs text-slate-400 sm:inline">{{ $ticket->updated_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="py-10 text-center">
                        <x-ui.icon name="check-circle" class="mx-auto h-8 w-8 text-emerald-500" />
                        <p class="mt-2 text-sm font-medium text-slate-700">No open tickets</p>
                        <p class="text-sm text-slate-500">The support queue is clear.</p>
                    </div>
                @endforelse
            </div>

            @if ($openTickets > 5)
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <a href="{{ route('tickets.index') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-950">View all {{ $openTickets }} open tickets</a>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Pipeline Health">
            <div class="space-y-4">
                <div class="rounded-lg bg-slate-950 p-4 text-white">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Open Deal Value</p>
                    <p class="mt-2 text-3xl font-semibold">${{ number_format($pipelineValue, 0) }}</p>
                    <p class="mt-1 text-sm text-slate-300">{{ $openDeals }} active deals</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('deals.index') }}" class="rounded-lg border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                        Deals
                    </a>
                    <a href="{{ route('invoices.index') }}" class="rounded-lg border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                        Invoices
                    </a>
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card title="Recently Added Clients">
            <div class="divide-y divide-slate-100">
                @forelse ($recentClients as $client)
                    <a href="{{ route('clients.show', $client) }}" wire:navigate class="flex items-center justify-between gap-4 py-3 hover:bg-slate-50 -mx-5 px-5">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sm font-semibold text-sky-700">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-950">{{ $client->name }}</p>
                                <p class="text-xs text-slate-500">{{ $client->type ?: 'Client' }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $client->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <div class="py-8 text-center text-sm text-slate-500">
                        No clients yet.
                    </div>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Domain And SSL Watch">
            <div class="divide-y divide-slate-100">
                @forelse ($expiringDomains as $domain)
                    @php $status = $domain->expiry_status; @endphp
                    <a href="{{ route('domains.index') }}" class="flex items-center justify-between gap-4 py-3 hover:bg-slate-50 -mx-5 px-5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-950">{{ $domain->name }}</p>
                            <p class="text-xs text-slate-500">{{ $domain->client?->name ?? 'No client' }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <x-ui.badge :color="$status === 'expired' ? 'red' : 'yellow'">
                                {{ $domain->expires_at?->format('d M Y') }}
                            </x-ui.badge>
                            <p class="mt-1 text-xs text-slate-400">{{ $domain->expires_at?->diffForHumans() }}</p>
                        </div>
                    </a>
                @empty
                    <div class="py-10 text-center">
                        <x-ui.icon name="shield-check" class="mx-auto h-8 w-8 text-emerald-500" />
                        <p class="mt-2 text-sm font-medium text-slate-700">No renewals due soon</p>
                        <p class="text-sm text-slate-500">Domains and certificates look current.</p>
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>
