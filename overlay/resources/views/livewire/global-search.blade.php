<div class="relative" x-data="{ focused: false }" @click.outside="$wire.close()">
    <div class="relative">
        <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
        <input
            wire:model.live.debounce.200ms="query"
            type="search"
            placeholder="Search anything..."
            @focus="focused = true"
            @keydown.escape="$wire.close()"
            class="w-72 rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-8 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none"
        />
        <svg wire:loading wire:target="query" class="absolute right-3 top-2.5 h-4 w-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    @if($open)
    <div class="absolute left-0 top-full z-50 mt-1 w-96 rounded-xl border border-slate-200 bg-white shadow-xl overflow-hidden">
        @if(!$hasResults)
            <div class="px-4 py-6 text-center text-sm text-slate-500">No results for "{{ $query }}"</div>
        @else
            @if($results['clients']->isNotEmpty())
                <div class="border-b border-slate-100">
                    <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Clients</div>
                    @foreach($results['clients'] as $client)
                        <a href="{{ route('clients.show', $client) }}" wire:navigate class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-sm text-slate-700">
                            <x-ui.icon name="building-office" class="h-4 w-4 text-slate-400 shrink-0" />
                            {{ $client->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if($results['tickets']->isNotEmpty())
                <div class="border-b border-slate-100">
                    <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Tickets</div>
                    @foreach($results['tickets'] as $ticket)
                        <a href="{{ route('tickets.show', $ticket) }}" wire:navigate class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-sm text-slate-700">
                            <x-ui.icon name="ticket" class="h-4 w-4 text-slate-400 shrink-0" />
                            <span class="truncate">{{ $ticket->subject }}</span>
                            <x-ui.badge :color="$ticket->priority_color" class="ml-auto shrink-0">{{ $ticket->status }}</x-ui.badge>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($results['invoices']->isNotEmpty())
                <div class="border-b border-slate-100">
                    <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Invoices</div>
                    @foreach($results['invoices'] as $invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-sm text-slate-700">
                            <x-ui.icon name="document-text" class="h-4 w-4 text-slate-400 shrink-0" />
                            {{ $invoice->invoice_number }}
                            <span class="ml-auto text-slate-500">${{ number_format($invoice->total, 0) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($results['deals']->isNotEmpty())
                <div>
                    <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Deals</div>
                    @foreach($results['deals'] as $deal)
                        <a href="{{ route('deals.show', $deal) }}" wire:navigate class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-sm text-slate-700">
                            <x-ui.icon name="funnel" class="h-4 w-4 text-slate-400 shrink-0" />
                            <span class="truncate">{{ $deal->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
    @endif
</div>
