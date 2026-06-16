<div class="flex gap-6">
    {{-- Reply thread (left / main column) --}}
    <div class="flex-1 min-w-0 space-y-4">
        {{-- Ticket header --}}
        <x-ui.card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $ticket->subject }}</h2>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span>#{{ $ticket->id }}</span>
                        <span>·</span>
                        <x-ui.badge :color="$ticket->status_color">{{ ucfirst($ticket->status) }}</x-ui.badge>
                        <x-ui.badge :color="$ticket->priority_color">{{ ucfirst($ticket->priority) }}</x-ui.badge>
                        @if ($ticket->client)
                            <span>·</span>
                            <a href="{{ route('clients.show', $ticket->client) }}" class="text-brand-700 hover:underline" wire:navigate>{{ $ticket->client->name }}</a>
                        @endif
                        <span>·</span>
                        <span>{{ $ticket->created_at->format('d M Y H:i') }}</span>
                        <span>·</span>
                        <span>Source: {{ ucfirst($ticket->source) }}</span>
                    </div>
                </div>
                <a href="{{ route('tickets.index') }}" class="text-xs text-slate-500 hover:text-brand-700 shrink-0" wire:navigate>← Back</a>
            </div>
        </x-ui.card>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        {{-- Replies --}}
        @foreach ($replies as $reply)
            @php
                $isStaff    = $reply->is_staff;
                $isInternal = $reply->is_internal;
            @endphp
            <div class="flex gap-3 {{ $isStaff ? '' : 'flex-row-reverse' }}">
                {{-- Avatar --}}
                <div class="shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold
                    {{ $isInternal ? 'bg-yellow-100 text-yellow-700' : ($isStaff ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-700') }}">
                    {{ strtoupper(substr($reply->author_name, 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 {{ $isStaff ? '' : 'flex-row-reverse' }}">
                        <span class="text-sm font-medium text-slate-900">{{ $reply->author_name }}</span>
                        @if ($isInternal)
                            <x-ui.badge color="yellow">Internal note</x-ui.badge>
                        @endif
                        <span class="text-xs text-slate-400">{{ $reply->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="rounded-lg px-4 py-3 text-sm leading-relaxed
                        {{ $isInternal ? 'bg-yellow-50 border border-yellow-200 text-yellow-900'
                           : ($isStaff ? 'bg-brand-50 border border-brand-100 text-slate-800'
                                       : 'bg-white border border-slate-200 text-slate-800') }}">
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

        {{-- Reply composer --}}
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
                    class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500
                    {{ $isInternal ? 'bg-yellow-50' : '' }}"
                    placeholder="{{ $isInternal ? 'Add an internal note (not visible to the client)…' : 'Type your reply…' }}"></textarea>
                @error('replyBody') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                <div class="mt-3 flex justify-end">
                    <x-ui.button wire:click="sendReply" loading="sendReply">
                        {{ $isInternal ? 'Save Note' : 'Send Reply' }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        @else
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 text-center">
                This ticket is closed. <button wire:click="$set('editStatus', 'open')" class="text-brand-700 hover:underline">Reopen it</button> to add a reply.
            </div>
        @endif
    </div>

    {{-- Sidebar (right column) --}}
    <div class="w-64 shrink-0 space-y-4">
        <x-ui.card title="Ticket Details">
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                    <select wire:model="editStatus" class="w-full rounded-lg border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Priority</label>
                    <select wire:model="editPriority" class="w-full rounded-lg border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
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
                <x-ui.button wire:click="updateMeta" loading="updateMeta" class="w-full justify-center">Save Changes</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card title="Info">
            <dl class="space-y-2 text-xs">
                <div>
                    <dt class="text-slate-500">Type</dt>
                    <dd class="font-medium text-slate-700 capitalize">{{ $ticket->type }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Source</dt>
                    <dd class="font-medium text-slate-700 capitalize">{{ $ticket->source }}</dd>
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
    </div>
</div>
