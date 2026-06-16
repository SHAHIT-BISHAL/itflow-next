<div>
    {{-- Toolbar --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-2">
            <div class="relative flex-1 max-w-xs">
                <x-ui.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search tickets…"
                    class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
            </div>
            <select wire:model.live="status"
                class="rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                <option value="">All statuses</option>
                <option value="open">Open</option>
                <option value="pending">Pending</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
            <select wire:model.live="priority"
                class="rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                <option value="">All priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
        <x-ui.button wire:click="openModal">
            <x-ui.icon name="plus" class="h-4 w-4 mr-1" /> New Ticket
        </x-ui.button>
    </div>

    {{-- Ticket table --}}
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="pb-3 pr-4">#</th>
                        <th class="pb-3 pr-4">Subject</th>
                        <th class="pb-3 pr-4">Client</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3 pr-4">Priority</th>
                        <th class="pb-3 pr-4">Assigned</th>
                        <th class="pb-3">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-slate-50 cursor-pointer" wire:click="$dispatch('navigate', {url: '{{ route('tickets.show', $ticket) }}'})">
                            <td class="py-3 pr-4 text-slate-400 font-mono text-xs">#{{ $ticket->id }}</td>
                            <td class="py-3 pr-4">
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-slate-900 hover:text-brand-700" wire:navigate>
                                    {{ $ticket->subject }}
                                </a>
                                @if ($ticket->source === 'email')
                                    <x-ui.icon name="envelope" class="inline h-3 w-3 text-slate-400 ml-1" />
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-slate-600">{{ $ticket->client?->name ?? '—' }}</td>
                            <td class="py-3 pr-4">
                                <x-ui.badge :color="$ticket->status_color">{{ ucfirst($ticket->status) }}</x-ui.badge>
                            </td>
                            <td class="py-3 pr-4">
                                <x-ui.badge :color="$ticket->priority_color">{{ ucfirst($ticket->priority) }}</x-ui.badge>
                            </td>
                            <td class="py-3 pr-4 text-slate-600">{{ $ticket->assignee?->name ?? '—' }}</td>
                            <td class="py-3 text-slate-400 text-xs">{{ $ticket->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tickets->hasPages())
            <div class="mt-4">{{ $tickets->links() }}</div>
        @endif
    </x-ui.card>

    {{-- New Ticket Modal --}}
    <x-ui.modal :show="$showModal" title="New Ticket" wire:close="$set('showModal', false)">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="form.subject" type="text" placeholder="Brief description of the issue" />
                @error('form.subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                    <label class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                    <select wire:model="form.priority" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                    <select wire:model="form.type" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="general">General</option>
                        <option value="technical">Technical</option>
                        <option value="billing">Billing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea wire:model="form.body" rows="5"
                    class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                    placeholder="Describe the issue…"></textarea>
                @error('form.body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="save" wire:loading.attr="disabled">Create Ticket</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
