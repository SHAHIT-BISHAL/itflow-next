<div class="space-y-5">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.metric-card label="Open Deals" :value="$openCount" icon="funnel" color="violet" />
        <x-ui.metric-card label="Pipeline Value" :value="'$' . number_format($totalValue, 0)" icon="banknotes" color="emerald" />
        <x-ui.metric-card label="Pipelines" :value="$pipelines->count()" icon="chart-bar" color="sky" />
    </div>

    <x-ui.toolbar>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:flex-wrap">
                <div class="flex overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                    <button wire:click="$set('view','list')"
                        class="px-3 py-2 text-sm font-semibold {{ $view === 'list' ? 'bg-slate-950 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        List
                    </button>
                    <button wire:click="$set('view','kanban')"
                        class="border-l border-slate-200 px-3 py-2 text-sm font-semibold {{ $view === 'kanban' ? 'bg-slate-950 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Kanban
                    </button>
                </div>

                @if ($view === 'list')
                    <div class="relative max-w-sm flex-1">
                        <x-ui.icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search deals..."
                            class="w-full rounded-md border border-slate-200 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500" />
                    </div>
                    <select wire:model.live="status"
                        class="rounded-md border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                        <option value="open">Open</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
                        <option value="">All</option>
                    </select>
                @else
                    @foreach ($pipelines as $pipeline)
                        <button wire:click="$set('pipelineId', {{ $pipeline->id }})"
                            class="rounded-md border px-3 py-2 text-sm font-semibold shadow-sm transition
                            {{ $pipelineId === $pipeline->id ? 'border-slate-950 bg-slate-950 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                            {{ $pipeline->name }}
                        </button>
                    @endforeach
                @endif
            </div>
            <x-ui.button wire:click="openModal">
                <x-ui.icon name="plus" class="h-4 w-4" /> New Deal
            </x-ui.button>
        </div>
    </x-ui.toolbar>

    @if ($view === 'list')
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Deal</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Stage</th>
                            <th class="px-4 py-3">Value</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Assigned</th>
                            <th class="px-4 py-3 text-right">Close Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($deals as $deal)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3">
                                    <a href="{{ route('deals.show', $deal) }}" class="font-semibold text-slate-950 hover:text-brand-700" wire:navigate>
                                        {{ $deal->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $deal->client?->name ?? 'No client' }}</td>
                                <td class="px-4 py-3"><x-ui.badge :color="$deal->stage->color ?? 'gray'">{{ $deal->stage->name }}</x-ui.badge></td>
                                <td class="px-4 py-3 font-semibold text-slate-950">${{ $deal->formatted_value }}</td>
                                <td class="px-4 py-3"><x-ui.badge :color="$deal->status_color">{{ ucfirst($deal->status) }}</x-ui.badge></td>
                                <td class="px-4 py-3 text-slate-600">{{ $deal->assignee?->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-3 text-right text-xs text-slate-500">{{ $deal->expected_close_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400">No deals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($deals->hasPages())
                <div class="mt-4">{{ $deals->links() }}</div>
            @endif
        </x-ui.card>
    @else
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach ($activePipeline?->stages ?? [] as $stage)
                @php
                    $stageDeals = $kanbanDeals[$stage->id] ?? collect();
                    $stageValue = $stageDeals->sum('value');
                @endphp
                <section class="w-80 shrink-0 rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <x-ui.badge :color="$stage->color">{{ $stage->name }}</x-ui.badge>
                            <span class="text-xs font-semibold text-slate-400">{{ $stageDeals->count() }}</span>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">${{ number_format($stageValue, 0) }}</span>
                    </div>
                    <div class="min-h-40 space-y-3 bg-slate-50/70 p-3">
                        @forelse ($stageDeals as $deal)
                            <article class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <a href="{{ route('deals.show', $deal) }}" class="block text-sm font-semibold text-slate-950 hover:text-brand-700" wire:navigate>
                                    {{ $deal->name }}
                                </a>
                                <p class="mt-1 text-xs text-slate-500">{{ $deal->client?->name ?? 'No client' }}</p>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-slate-800">${{ $deal->formatted_value }}</span>
                                    @if ($deal->assignee)
                                        <span class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-950 text-xs font-bold text-white">
                                            {{ strtoupper(substr($deal->assignee->name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                @if ($deal->expected_close_date)
                                    <p class="mt-2 text-xs text-slate-400">Close: {{ $deal->expected_close_date->format('d M Y') }}</p>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 bg-white/70 p-6 text-center text-sm text-slate-400">
                                No deals in this stage.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    <x-ui.modal :show="$showModal" title="New Deal" wire:close="$set('showModal', false)">
        <div class="space-y-4">
            <x-ui.input wire:model="form.name" type="text" label="Deal Name" placeholder="e.g. Acme - Network Upgrade" :error="$errors->first('form.name')" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input wire:model="form.client_id" type="select" label="Client">
                    <option value="">No client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </x-ui.input>
                <x-ui.input wire:model="form.assigned_to" type="select" label="Assign to">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-ui.input>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input wire:model.live="form.pipeline_id" type="select" label="Pipeline" :error="$errors->first('form.pipeline_id')">
                    <option value="">Select pipeline</option>
                    @foreach ($pipelines as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </x-ui.input>
                <x-ui.input wire:model="form.stage_id" type="select" label="Stage" :error="$errors->first('form.stage_id')">
                    <option value="">Select stage</option>
                    @foreach ($stageOptions as $stage)
                        <option value="{{ $stage['id'] }}">{{ $stage['name'] }}</option>
                    @endforeach
                </x-ui.input>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input wire:model="form.value" type="number" label="Value ($)" placeholder="0.00" />
                <x-ui.input wire:model="form.expected_close_date" type="date" label="Expected Close" />
            </div>

            <x-ui.input wire:model="form.notes" type="textarea" label="Notes" rows="3" />
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="save">Create Deal</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
