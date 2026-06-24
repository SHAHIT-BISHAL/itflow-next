<div class="flex gap-6">
    <div class="min-w-0 flex-1 space-y-4">
        <x-ui.card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $ticket->subject }}</h2>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span class="font-mono font-semibold">{{ $ticket->display_number }}</span>
                        <span>-</span>
                        <x-ui.badge :color="$ticket->status_color">{{ ucfirst($ticket->status) }}</x-ui.badge>
                        <x-ui.badge :color="$ticket->priority_color">{{ ucfirst($ticket->priority) }}</x-ui.badge>
                        @if ($ticket->client)
                            <span>-</span>
                            <a href="{{ route('clients.show', $ticket->client) }}" class="text-brand-700 hover:underline" wire:navigate>{{ $ticket->client->name }}</a>
                        @endif
                        <span>-</span>
                        <span>{{ $ticket->created_at->format('d M Y H:i') }}</span>
                        <span>-</span>
                        <span>Source: {{ ucfirst($ticket->source) }}</span>
                    </div>
                </div>
                <a href="{{ route('tickets.index') }}" class="shrink-0 text-xs text-slate-500 hover:text-brand-700" wire:navigate>Back</a>
            </div>
        </x-ui.card>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @foreach ($replies as $reply)
            @php
                $isStaff = $reply->is_staff;
                $replyIsInternal = $reply->is_internal;
            @endphp
            <div class="flex gap-3 {{ $isStaff ? '' : 'flex-row-reverse' }}">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold
                    {{ $replyIsInternal ? 'bg-yellow-100 text-yellow-700' : ($isStaff ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-700') }}">
                    {{ strtoupper(substr($reply->author_name, 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="mb-1 flex items-center gap-2 {{ $isStaff ? '' : 'flex-row-reverse' }}">
                        <span class="text-sm font-medium text-slate-900">{{ $reply->author_name }}</span>
                        @if ($replyIsInternal)
                            <x-ui.badge color="yellow">Internal note</x-ui.badge>
                        @endif
                        <span class="text-xs text-slate-400">{{ $reply->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="rounded-lg px-4 py-3 text-sm leading-relaxed
                        {{ $replyIsInternal ? 'border border-yellow-200 bg-yellow-50 text-yellow-900'
                           : ($isStaff ? 'border border-brand-100 bg-brand-50 text-slate-800'
                                       : 'border border-slate-200 bg-white text-slate-800') }}">
                        {!! nl2br(e($reply->body)) !!}
                    </div>
                    @if ($reply->attachments->count())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($reply->attachments as $att)
                                <a href="{{ Storage::url($att->path) }}" target="_blank"
                                    class="flex items-center gap-1 rounded border border-slate-200 bg-white px-2 py-1 text-xs text-slate-600 hover:text-brand-700">
                                    <x-ui.icon name="document-text" class="h-3 w-3" />
                                    {{ $att->filename }}
                                    <span class="text-slate-400">({{ $att->formatted_size }})</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if (! in_array($ticket->status, ['closed']))
            <x-ui.card>
                <div class="mb-3 flex items-center gap-3">
                    <button wire:click="$set('isInternal', false)"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition
                        {{ ! $isInternal ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Reply
                    </button>
                    <button wire:click="$set('isInternal', true)"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition
                        {{ $isInternal ? 'bg-yellow-500 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Internal Note
                    </button>
                </div>

                <textarea wire:model="replyBody" rows="5"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500
                    {{ $isInternal ? 'bg-yellow-50' : '' }}"
                    placeholder="{{ $isInternal ? 'Add an internal note (not visible to the client)...' : 'Type your reply...' }}"></textarea>
                @error('replyBody') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                <div class="mt-3 flex justify-end">
                    <x-ui.button wire:click="sendReply" loading="sendReply">
                        {{ $isInternal ? 'Save Note' : 'Send Reply' }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        @else
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-center text-sm text-slate-500">
                This ticket is closed. <button wire:click="$set('editStatus', 'open')" class="text-brand-700 hover:underline">Reopen it</button> to add a reply.
            </div>
        @endif
    </div>

    <div class="w-64 shrink-0 space-y-4">
        <x-ui.card title="Ticket Details">
            <div class="space-y-3 text-sm">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                    <select wire:model="editStatus" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Priority</label>
                    <select wire:model="editPriority" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Assigned to</label>
                    <select wire:model="editAssignee" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Unassigned</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.button wire:click="updateMeta" loading="updateMeta" class="w-full justify-center">Save Changes</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card title="Info">
            <dl class="space-y-2 text-xs">
                <div>
                    <dt class="text-slate-500">Number</dt>
                    <dd class="font-mono font-medium text-slate-700">{{ $ticket->display_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Type</dt>
                    <dd class="font-medium capitalize text-slate-700">{{ $ticket->type }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Source</dt>
                    <dd class="font-medium capitalize text-slate-700">{{ $ticket->source }}</dd>
                </div>
                @if ($ticket->contact)
                    <div>
                        <dt class="text-slate-500">Contact</dt>
                        <dd class="font-medium text-slate-700">{{ $ticket->contact->name }}</dd>
                    </div>
                @endif
                @if ($ticket->resolved_at)
                    <div>
                        <dt class="text-slate-500">Resolved</dt>
                        <dd class="font-medium text-slate-700">{{ $ticket->resolved_at->format('d M Y') }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-slate-500">Replies</dt>
                    <dd class="font-medium text-slate-700">{{ $replies->count() }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Time Tracking">
            @php
                $totalMin = $timeEntries->sum('minutes');
                $billableMin = $timeEntries->where('is_billable', true)->sum('minutes');
                $fmt = fn ($m) => intdiv($m, 60) > 0 ? intdiv($m, 60) . 'h ' . ($m % 60) . 'm' : ($m % 60) . 'm';
            @endphp
            <div class="mb-3 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                <div>
                    <p class="font-semibold text-slate-900">{{ $fmt($totalMin) }}</p>
                    <p class="text-xs text-slate-500">total logged</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-emerald-600">{{ $fmt($billableMin) }}</p>
                    <p class="text-xs text-slate-500">billable</p>
                </div>
            </div>

            {{-- Quick log form --}}
            <div class="space-y-2 border-b border-slate-100 pb-3">
                <div class="flex gap-2">
                    <input type="number" min="1" wire:model="timeForm.minutes" placeholder="Min"
                        class="w-20 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none" />
                    <input type="date" wire:model="timeForm.performed_at"
                        class="flex-1 rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none" />
                </div>
                @error('timeForm.minutes') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                <input type="text" wire:model="timeForm.description" placeholder="What did you do?"
                    class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none" />
                @error('timeForm.description') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                <label class="flex items-center gap-2 text-xs text-slate-600">
                    <input type="checkbox" wire:model="timeForm.is_billable" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                    Billable
                </label>
                <x-ui.button wire:click="logTime" loading="logTime" class="w-full justify-center text-xs">Log Time</x-ui.button>
            </div>

            {{-- Entries --}}
            <div class="mt-3 space-y-2">
                @forelse ($timeEntries as $entry)
                    <div class="group flex items-start justify-between gap-2 text-xs">
                        <div class="min-w-0">
                            <p class="truncate text-slate-700">{{ $entry->description }}</p>
                            <p class="text-slate-400">
                                {{ $entry->performed_at->format('d M') }} · {{ $entry->user->name ?? 'Unknown' }}
                                @unless ($entry->is_billable) · <span class="text-slate-400">non-billable</span> @endunless
                                @if ($entry->invoice_id) · <span class="text-emerald-600">invoiced</span> @endif
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <span class="font-mono font-medium {{ $entry->is_billable ? 'text-slate-700' : 'text-slate-400' }}">{{ $entry->formatted_duration }}</span>
                            @unless ($entry->invoice_id)
                                <button wire:click="deleteTimeEntry({{ $entry->id }})" wire:confirm="Remove this time entry?"
                                    class="text-slate-300 opacity-0 transition group-hover:opacity-100 hover:text-red-500" aria-label="Remove time entry">
                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                </button>
                            @endunless
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">No time logged yet.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Timeline">
            <div class="space-y-3">
                @forelse ($events as $event)
                    <div class="border-l-2 border-slate-200 pl-3">
                        <p class="text-xs font-semibold text-slate-800">{{ $event->description ?? str_replace('_', ' ', $event->event_type) }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ $event->created_at->format('d M Y H:i') }}
                            @if ($event->actor)
                                by {{ $event->actor->name ?? class_basename($event->actor_type) }}
                            @endif
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">No timeline events yet.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>
