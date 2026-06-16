<div class="flex gap-6">
    {{-- Pipeline list --}}
    <div class="w-64 shrink-0">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Pipelines</h3>
            <x-ui.button wire:click="openPipeline()" variant="secondary">
                <x-ui.icon name="plus" class="h-4 w-4" />
            </x-ui.button>
        </div>
        <div class="space-y-1">
            @foreach ($pipelines as $pipeline)
                <div class="flex items-center gap-2 rounded-lg px-3 py-2 cursor-pointer transition
                    {{ $activePipeline?->id === $pipeline->id ? 'bg-brand-50 border border-brand-200' : 'hover:bg-slate-50 border border-transparent' }}"
                    wire:click="selectPipeline({{ $pipeline->id }})">
                    <span class="flex-1 text-sm font-medium text-slate-900">{{ $pipeline->name }}</span>
                    @if ($pipeline->is_default)
                        <x-ui.badge color="brand">Default</x-ui.badge>
                    @endif
                    <span class="text-xs text-slate-400">{{ $pipeline->deals_count }}</span>
                    <button wire:click.stop="openPipeline({{ $pipeline->id }})" class="text-slate-400 hover:text-brand-700">
                        <x-ui.icon name="pencil" class="h-3.5 w-3.5" />
                    </button>
                    <button wire:click.stop="deletePipeline({{ $pipeline->id }})"
                        wire:confirm="Delete this pipeline? All deals in it will be removed."
                        class="text-slate-400 hover:text-red-500">
                        <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Stages for active pipeline --}}
    <div class="flex-1 min-w-0">
        @if ($activePipeline)
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Stages — {{ $activePipeline->name }}</h3>
                <x-ui.button wire:click="openStage()" variant="secondary">
                    <x-ui.icon name="plus" class="h-4 w-4 mr-1" /> Add Stage
                </x-ui.button>
            </div>
            <x-ui.card>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="pb-3 pr-4">Stage</th>
                            <th class="pb-3 pr-4">Color</th>
                            <th class="pb-3 pr-4">Probability</th>
                            <th class="pb-3 pr-4">Deals</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($activePipeline->stages as $stage)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 pr-4 font-medium text-slate-900">{{ $stage->name }}</td>
                                <td class="py-3 pr-4">
                                    <x-ui.badge :color="$stage->color">{{ ucfirst($stage->color) }}</x-ui.badge>
                                </td>
                                <td class="py-3 pr-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-slate-100 rounded-full h-1.5">
                                            <div class="bg-brand-500 h-1.5 rounded-full" style="width: {{ $stage->probability }}%"></div>
                                        </div>
                                        <span class="text-slate-600">{{ $stage->probability }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-slate-500">{{ $stage->deals->count() }}</td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <button wire:click="openStage({{ $stage->id }})" class="text-slate-400 hover:text-brand-700">
                                            <x-ui.icon name="pencil" class="h-4 w-4" />
                                        </button>
                                        <button wire:click="deleteStage({{ $stage->id }})"
                                            wire:confirm="Delete this stage?"
                                            class="text-slate-400 hover:text-red-500">
                                            <x-ui.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.card>
        @else
            <x-ui.card>
                <p class="py-8 text-center text-slate-400">Select a pipeline to manage its stages.</p>
            </x-ui.card>
        @endif
    </div>

    {{-- Pipeline Modal --}}
    <x-ui.modal :show="$showPipelineModal" title="{{ $editingPipelineId ? 'Edit Pipeline' : 'New Pipeline' }}" wire:close="$set('showPipelineModal', false)">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="pipelineForm.name" type="text" placeholder="e.g. Sales Pipeline" />
            </div>
            <div class="flex items-center gap-2">
                <input wire:model="pipelineForm.is_default" type="checkbox" id="is_default" class="rounded border-slate-300">
                <label for="is_default" class="text-sm text-slate-700">Default pipeline</label>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showPipelineModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="savePipeline">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Stage Modal --}}
    <x-ui.modal :show="$showStageModal" title="{{ $editingStageId ? 'Edit Stage' : 'New Stage' }}" wire:close="$set('showStageModal', false)">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="stageForm.name" type="text" placeholder="e.g. Qualified" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Color</label>
                    <select wire:model="stageForm.color" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="gray">Gray</option>
                        <option value="blue">Blue</option>
                        <option value="purple">Purple</option>
                        <option value="yellow">Yellow</option>
                        <option value="green">Green</option>
                        <option value="red">Red</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Win Probability (%)</label>
                    <x-ui.input wire:model="stageForm.probability" type="number" placeholder="0-100" />
                </div>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showStageModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="saveStage">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
