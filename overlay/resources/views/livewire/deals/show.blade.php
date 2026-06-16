<div class="flex gap-6">
    {{-- Main column: progress bar + activities --}}
    <div class="flex-1 min-w-0 space-y-4">
        {{-- Header --}}
        <x-ui.card>
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $deal->name }}</h2>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <x-ui.badge :color="$deal->status_color">{{ ucfirst($deal->status) }}</x-ui.badge>
                        <span>·</span>
                        <span class="font-semibold text-slate-700">${{ $deal->formatted_value }}</span>
                        @if ($deal->client)
                            <span>·</span>
                            <a href="{{ route('clients.show', $deal->client) }}" class="text-brand-700 hover:underline" wire:navigate>{{ $deal->client->name }}</a>
                        @endif
                        @if ($deal->expected_close_date)
                            <span>·</span>
                            <span>Close: {{ $deal->expected_close_date->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('deals.index') }}" class="text-xs text-slate-500 hover:text-brand-700 shrink-0" wire:navigate>← Back</a>
            </div>

            {{-- Pipeline stage progress --}}
            <div class="mt-4">
                <div class="flex gap-1">
                    @foreach ($stages as $stage)
                        <button wire:click="$set('editStage', '{{ $stage->id }}')"
                            class="flex-1 h-2 rounded-full transition-all
                            {{ $editStage == $stage->id ? 'bg-brand-500' : ($deal->stage->sort_order > $stage->sort_order ? 'bg-brand-200' : 'bg-slate-200') }}"
                            title="{{ $stage->name }}">
                        </button>
                    @endforeach
                </div>
                <div class="mt-1 flex justify-between text-xs text-slate-400">
                    <span>{{ $stages->first()?->name }}</span>
                    <span class="font-medium text-brand-700">{{ $deal->stage->name }}</span>
                    <span>{{ $stages->last()?->name }}</span>
                </div>
            </div>
        </x-ui.card>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        {{-- Activities timeline --}}
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Activity</h3>
            <x-ui.button wire:click="openActivityModal" variant="secondary">
                <x-ui.icon name="plus" class="h-4 w-4 mr-1" /> Log Activity
            </x-ui.button>
        </div>

        @if ($activities->isEmpty())
            <x-ui.card>
                <p class="py-4 text-center text-sm text-slate-400">No activity yet — log a call, email, meeting, or note.</p>
            </x-ui.card>
        @else
            <div class="relative space-y-3 pl-6">
                <div class="absolute left-2 top-0 bottom-0 w-px bg-slate-200"></div>
                @foreach ($activities as $activity)
                    <div class="relative">
                        <div class="absolute -left-6 mt-1 h-4 w-4 rounded-full border-2 border-white flex items-center justify-center
                            {{ $activity->is_completed ? 'bg-green-400' : 'bg-brand-400' }}">
                        </div>
                        <x-ui.card>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <x-ui.badge :color="$activity->type_color">{{ ucfirst($activity->type) }}</x-ui.badge>
                                        <span class="font-medium text-sm text-slate-900">{{ $activity->subject }}</span>
                                    </div>
                                    @if ($activity->description)
                                        <p class="text-sm text-slate-600 mb-1">{{ $activity->description }}</p>
                                    @endif
                                    @if ($activity->outcome)
                                        <p class="text-xs text-slate-500 italic">Outcome: {{ $activity->outcome }}</p>
                                    @endif
                                    <p class="text-xs text-slate-400 mt-1">
                                        {{ $activity->user?->name ?? 'Unknown' }} · {{ $activity->created_at->format('d M Y H:i') }}
                                        @if ($activity->due_at && ! $activity->is_completed)
                                            · Due {{ $activity->due_at->format('d M Y') }}
                                        @endif
                                    </p>
                                </div>
                                @if (! $activity->is_completed && $activity->type === 'task')
                                    <button wire:click="completeActivity({{ $activity->id }})"
                                        class="shrink-0 text-xs text-slate-400 hover:text-green-600 border border-slate-200 rounded px-2 py-1">
                                        Complete
                                    </button>
                                @endif
                            </div>
                        </x-ui.card>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Notes --}}
        @if ($deal->notes)
            <x-ui.card title="Notes">
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $deal->notes }}</p>
            </x-ui.card>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="w-64 shrink-0 space-y-4">
        <x-ui.card title="Deal Details">
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Stage</label>
                    <select wire:model="editStage" class="w-full rounded-lg border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none">
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                    <select wire:model="editStatus" class="w-full rounded-lg border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="open">Open</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Assigned to</label>
                    <select wire:model="editAssignee" class="w-full rounded-lg border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Unassigned</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.button wire:click="updateMeta" class="w-full justify-center">Save Changes</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card title="Info">
            <dl class="space-y-2 text-xs">
                <div>
                    <dt class="text-slate-500">Pipeline</dt>
                    <dd class="font-medium text-slate-700">{{ $deal->pipeline->name }}</dd>
                </div>
                @if ($deal->contact)
                    <div>
                        <dt class="text-slate-500">Contact</dt>
                        <dd class="font-medium text-slate-700">{{ $deal->contact->name }}</dd>
                    </div>
                @endif
                @if ($deal->closed_at)
                    <div>
                        <dt class="text-slate-500">Closed</dt>
                        <dd class="font-medium text-slate-700">{{ $deal->closed_at->format('d M Y') }}</dd>
                    </div>
                @endif
                @if ($deal->lost_reason)
                    <div>
                        <dt class="text-slate-500">Lost reason</dt>
                        <dd class="text-red-600">{{ $deal->lost_reason }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-slate-500">Activities</dt>
                    <dd class="font-medium text-slate-700">{{ $activities->count() }}</dd>
                </div>
            </dl>
        </x-ui.card>
    </div>

    {{-- Log Activity Modal --}}
    <x-ui.modal :show="$showActivityModal" title="Log Activity" wire:close="$set('showActivityModal', false)">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                    <select wire:model="activityForm.type" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="note">Note</option>
                        <option value="call">Call</option>
                        <option value="email">Email</option>
                        <option value="meeting">Meeting</option>
                        <option value="task">Task</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Due Date</label>
                    <x-ui.input wire:model="activityForm.due_at" type="date" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="activityForm.subject" type="text" placeholder="e.g. Discovery call with Jane" />
                @error('activityForm.subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea wire:model="activityForm.description" rows="3"
                    class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Outcome</label>
                <x-ui.input wire:model="activityForm.outcome" type="text" placeholder="What was the result?" />
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showActivityModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="saveActivity">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
