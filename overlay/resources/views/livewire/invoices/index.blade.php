<div>
    {{-- Stats --}}
    <div class="mb-4 grid grid-cols-3 gap-4">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Paid</p>
            <p class="mt-1 text-2xl font-semibold text-green-700">${{ number_format($totalPaid, 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Outstanding</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($totalOwed, 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Overdue</p>
            <p class="mt-1 text-2xl font-semibold {{ $overdueCount > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ $overdueCount }}</p>
        </x-ui.card>
    </div>

    {{-- Toolbar --}}
    <div class="mb-4 flex gap-3 items-center justify-between">
        <div class="flex gap-2 flex-1">
            <div class="relative max-w-xs flex-1">
                <x-ui.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by number or client…"
                    class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-brand-500 focus:outline-none" />
            </div>
            <select wire:model.live="status"
                class="rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
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
            <x-ui.button><x-ui.icon name="plus" class="h-4 w-4 mr-1" /> New Invoice</x-ui.button>
        </a>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="pb-3 pr-4">Number</th>
                        <th class="pb-3 pr-4">Client</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3 pr-4">Issue Date</th>
                        <th class="pb-3 pr-4">Due Date</th>
                        <th class="pb-3 pr-4 text-right">Total</th>
                        <th class="pb-3 text-right">Amount Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 pr-4">
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-mono font-medium text-brand-700 hover:underline" wire:navigate>
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="py-3 pr-4 text-slate-700">{{ $invoice->client->name }}</td>
                            <td class="py-3 pr-4">
                                <x-ui.badge :color="$invoice->status_color">{{ ucfirst($invoice->status) }}</x-ui.badge>
                            </td>
                            <td class="py-3 pr-4 text-slate-500 text-xs">{{ $invoice->issue_date->format('d M Y') }}</td>
                            <td class="py-3 pr-4 text-xs {{ $invoice->status === 'overdue' ? 'text-red-600 font-medium' : 'text-slate-500' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </td>
                            <td class="py-3 pr-4 text-right font-medium text-slate-900">${{ number_format($invoice->total, 2) }}</td>
                            <td class="py-3 text-right {{ $invoice->amount_due > 0 ? 'font-semibold text-slate-900' : 'text-slate-400' }}">
                                ${{ number_format($invoice->amount_due, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No invoices found.</td>
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
