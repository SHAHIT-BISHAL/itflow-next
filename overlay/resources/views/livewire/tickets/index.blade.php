<div class="space-y-5">
    <x-ui.toolbar>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:flex-wrap">
                <div class="relative max-w-sm flex-1">
                    <x-ui.icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search tickets..."
                        class="w-full rounded-md border border-slate-200 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500" />
                </div>
                <select wire:model.live="status" class="rounded-md border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                    <option value="">All statuses</option>
                    <option value="open">Open</option>
                    <option value="pending">Pending</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
                <select wire:model.live="priority" class="rounded-md border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                    <option value="">All priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <x-ui.button wire:click="openModal">
                <x-ui.icon name="plus" class="h-4 w-4" /> New Ticket
            </x-ui.button>
        </div>
    </x-ui.toolbar>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Ticket</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Assigned</th>
                        <th class="px-4 py-3 text-right">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-500">{{ $ticket->display_number }}</span>
                                    <div class="min-w-0">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="block truncate font-semibold text-slate-950 hover:text-brand-700" wire:navigate>
                                            {{ $ticket->subject }}
                                        </a>
                                        @if ($ticket->source === 'email')
                                            <span class="mt-1 inline-flex items-center gap-1 text-xs text-slate-400">
                                                <x-ui.icon name="envelope" class="h-3 w-3" /> Email
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $ticket->client?->name ?? 'No client' }}</td>
                            <td class="px-4 py-3"><x-ui.badge :color="$ticket->status_color">{{ ucfirst($ticket->status) }}</x-ui.badge></td>
                            <td class="px-4 py-3"><x-ui.badge :color="$ticket->priority_color">{{ ucfirst($ticket->priority) }}</x-ui.badge></td>
                            <td class="px-4 py-3 text-slate-600">{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-right text-xs text-slate-400">{{ $ticket->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tickets->hasPages())
            <div class="mt-4">{{ $tickets->links() }}</div>
        @endif
    </x-ui.card>

    <x-ui.modal :show="$showModal" title="New Ticket" wire:close="$set('showModal', false)">
        <div class="space-y-4">
            <x-ui.input wire:model="form.subject" type="text" label="Subject" placeholder="Brief description of the issue" :error="$errors->first('form.subject')" />

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
                <x-ui.input wire:model="form.priority" type="select" label="Priority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </x-ui.input>
                <x-ui.input wire:model="form.type" type="select" label="Type">
                    <option value="general">General</option>
                    <option value="technical">Technical</option>
                    <option value="billing">Billing</option>
                    <option value="other">Other</option>
                </x-ui.input>
            </div>

            <x-ui.input wire:model="form.body" type="textarea" label="Description" rows="5" placeholder="Describe the issue..." :error="$errors->first('form.body')" />
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="save" wire:loading.attr="disabled">Create Ticket</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
