<div class="space-y-5">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.metric-card label="Total Paid" :value="'$' . number_format($totalPaid, 2)" icon="banknotes" color="emerald" />
        <x-ui.metric-card label="Outstanding" :value="'$' . number_format($totalOwed, 2)" icon="document-text" color="sky" />
        <x-ui.metric-card label="Overdue" :value="$overdueCount" icon="calendar" :color="$overdueCount > 0 ? 'red' : 'slate'" />
    </div>

    <x-ui.toolbar>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                <div class="relative max-w-xs flex-1">
                    <x-ui.icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by number or client..."
                        class="w-full rounded-md border border-slate-200 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500" />
                </div>
                <select wire:model.live="status"
                    class="rounded-md border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                    <option value="">All statuses</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="void">Void</option>
                </select>
            </div>
            <a href="{{ route('invoices.create') }}" wire:navigate>
                <x-ui.button><x-ui.icon name="plus" class="h-4 w-4" /> New Invoice</x-ui.button>
            </a>
        </div>
    </x-ui.toolbar>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Number</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Issue Date</th>
                        <th class="px-4 py-3">Due Date</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Amount Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-mono font-semibold text-brand-700 hover:underline" wire:navigate>
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $invoice->client->name }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :color="$invoice->status_color">{{ ucfirst($invoice->status) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $invoice->issue_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs {{ $invoice->status === 'overdue' ? 'text-red-600 font-medium' : 'text-slate-500' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-900">${{ number_format($invoice->total, 2) }}</td>
                            <td class="px-4 py-3 text-right {{ $invoice->amount_due > 0 ? 'font-semibold text-slate-900' : 'text-slate-400' }}">
                                ${{ number_format($invoice->amount_due, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="mt-4">{{ $invoices->links() }}</div>
        @endif
    </x-ui.card>
</div>
