<div>
    {{-- Stats bar --}}
    <div class="mb-4 grid grid-cols-3 gap-4">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Open Deals</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $openCount }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Pipeline Value</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($totalValue, 0) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Pipelines</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $pipelines->count() }}</p>
        </x-ui.card>
    </div>

    {{-- Toolbar --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-2 flex-wrap">
            {{-- View toggle --}}
            <div class="flex rounded-lg border border-slate-200 overflow-hidden">
                <button wire:click="$set('view','list')"
                    class="px-3 py-1.5 text-sm {{ $view === 'list' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    List
                </button>
                <button wire:click="$set('view','kanban')"
                    class="px-3 py-1.5 text-sm {{ $view === 'kanban' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    Kanban
                </button>
            </div>

            @if ($view === 'list')
                <div class="relative flex-1 max-w-xs">
                    <x-ui.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search deals…"
                        class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-brand-500 focus:outline-none" />
                </div>
                <select wire:model.live="status"
                    class="rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="open">Open</option>
                    <option value="won">Won</option>
                    <option value="lost">Lost</option>
                    <option value="">All</option>
                </select>
            @else
                {{-- Pipeline selector for kanban --}}
                @foreach ($pipelines as $pipeline)
                    <button wire:click="$set('pipelineId', {{ $pipeline->id }})"
                        class="rounded-lg px-3 py-1.5 text-sm border transition
                        {{ $pipelineId === $pipeline->id ? 'bg-brand-600 text-white border-brand-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                        {{ $pipeline->name }}
                    </button>
                @endforeach
            @endif
        </div>
        <x-ui.button wire:click="openModal">
            <x-ui.icon name="plus" class="h-4 w-4 mr-1" /> New Deal
        </x-ui.button>
    </div>

    {{-- List view --}}
    @if ($view === 'list')
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="pb-3 pr-4">Deal</th>
                            <th class="pb-3 pr-4">Client</th>
                            <th class="pb-3 pr-4">Stage</th>
                            <th class="pb-3 pr-4">Value</th>
                            <th class="pb-3 pr-4">Status</th>
                            <th class="pb-3 pr-4">Assigned</th>
                            <th class="pb-3">Close Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($deals as $deal)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('deals.show', $deal) }}" class="font-medium text-brand-700 hover:underline" wire:navigate>
                                        {{ $deal->name }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4 text-slate-600">{{ $deal->client?->name ?? '—' }}</td>
                                <td class="py-3 pr-4">
                                    <x-ui.badge :color="$deal->stage->color ?? 'gray'">{{ $deal->stage->name }}</x-ui.badge>
                                </td>
                                <td class="py-3 pr-4 font-medium text-slate-900">${{ $deal->formatted_value }}</td>
                                <td class="py-3 pr-4">
                                    <x-ui.badge :color="$deal->status_color">{{ ucfirst($deal->status) }}</x-ui.badge>
                                </td>
                                <td class="py-3 pr-4 text-slate-600">{{ $deal->assignee?->name ?? '—' }}</td>
                                <td class="py-3 text-slate-400 text-xs">
                                    {{ $deal->expected_close_date?->format('d M Y') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">No deals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($deals->hasPages())
                <div class="mt-4">{{ $deals->links() }}</div>
            @endif
        </x-ui.card>

    {{-- Kanban view --}}
    @else
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach ($activePipeline?->stages ?? [] as $stage)
                @php
                    $stageDeals = $kanbanDeals[$stage->id] ?? collect();
                    $stageValue = $stageDeals->sum('value');
                @endphp
                <div class="w-72 shrink-0">
                    <div class="mb-2 flex items-center justify-between px-1">
                        <div class="flex items-center gap-2">
                            <x-ui.badge :color="$stage->color">{{ $stage->name }}</x-ui.badge>
                            <span class="text-xs text-slate-400">{{ $stageDeals->count() }}</span>
                        </div>
                        <span class="text-xs font-medium text-slate-500">${{ number_format($stageValue, 0) }}</span>
                    </div>
                    <div class="space-y-2 rounded-lg bg-slate-50 p-2 min-h-32">
                        @foreach ($stageDeals as $deal)
                            <div class="rounded-lg bg-white border border-slate-200 p-3 shadow-sm hover:shadow-md transition">
                                <a href="{{ route('deals.show', $deal) }}" class="font-medium text-slate-900 hover:text-brand-700 text-sm block mb-1" wire:navigate>
                                    {{ $deal->name }}
                                </a>
                                <p class="text-xs text-slate-500 mb-2">{{ $deal->client?->name ?? 'No client' }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-slate-700">${{ $deal->formatted_value }}</span>
                                    @if ($deal->assignee)
                                        <span class="h-6 w-6 rounded-full bg-brand-100 text-brand-700 text-xs font-bold flex items-center justify-center">
                                            {{ strtoupper(substr($deal->assignee->name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                @if ($deal->expected_close_date)
                                    <p class="text-xs text-slate-400 mt-1">Close: {{ $deal->expected_close_date->format('d M Y') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- New Deal Modal --}}
    <x-ui.modal :show="$showModal" title="New Deal" wire:close="$set('showModal', false)">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Deal Name <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="form.name" type="text" placeholder="e.g. Acme — Network Upgrade" />
                @error('form.name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Client</label>
                    <select wire:model="form.client_id" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">No client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Assign to</label>
                    <select wire:model="form.assigned_to" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Unassigned</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pipeline <span class="text-red-500">*</span></label>
                    <select wire:model.live="form.pipeline_id" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Select pipeline</option>
                        @foreach ($pipelines as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('form.pipeline_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stage <span class="text-red-500">*</span></label>
                    <select wire:model="form.stage_id" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Select stage</option>
                        @foreach ($stageOptions as $stage)
                            <option value="{{ $stage['id'] }}">{{ $stage['name'] }}</option>
                        @endforeach
                    </select>
                    @error('form.stage_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Value ($)</label>
                    <x-ui.input wire:model="form.value" type="number" placeholder="0.00" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Expected Close</label>
                    <x-ui.input wire:model="form.expected_close_date" type="date" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea wire:model="form.notes" rows="3"
                    class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none"></textarea>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="save">Create Deal</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
