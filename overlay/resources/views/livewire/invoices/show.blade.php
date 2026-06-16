<div class="flex gap-6">
    {{-- Invoice body --}}
    <div class="flex-1 min-w-0 space-y-4">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <x-ui.card>
            {{-- Invoice header --}}
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 font-mono">{{ $invoice->invoice_number }}</h2>
                    <div class="mt-1 flex items-center gap-2">
                        <x-ui.badge :color="$invoice->status_color">{{ ucfirst($invoice->status) }}</x-ui.badge>
                        <span class="text-xs text-slate-400">{{ $invoice->issue_date->format('d M Y') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                       class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                        <x-ui.icon name="arrow-down-tray" class="h-4 w-4" /> PDF
                    </a>
                    <a href="{{ route('invoices.index') }}" class="text-xs text-slate-500 hover:text-brand-700" wire:navigate>← Back</a>
                </div>
            </div>

            {{-- Bill to --}}
            <div class="mb-6">
                <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Bill To</p>
                <p class="font-semibold text-slate-900">{{ $invoice->client->name }}</p>
                @if ($invoice->contact)
                    <p class="text-sm text-slate-600">{{ $invoice->contact->name }}</p>
                    @if ($invoice->contact->email)
                        <p class="text-sm text-slate-500">{{ $invoice->contact->email }}</p>
                    @endif
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6 text-sm">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Issue Date</p>
                    <p class="font-medium text-slate-800">{{ $invoice->issue_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Due Date</p>
                    <p class="font-medium {{ $invoice->status === 'overdue' ? 'text-red-600' : 'text-slate-800' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Currency</p>
                    <p class="font-medium text-slate-800">{{ $invoice->currency }}</p>
                </div>
            </div>

            {{-- Line items --}}
            <table class="w-full text-sm mb-6">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="pb-2 pr-4">Description</th>
                        <th class="pb-2 pr-4 w-16 text-right">Qty</th>
                        <th class="pb-2 pr-4 w-28 text-right">Unit Price</th>
                        <th class="pb-2 pr-4 w-16 text-right">Tax</th>
                        <th class="pb-2 w-28 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="py-2.5 pr-4 text-slate-800">{{ $item->description }}</td>
                            <td class="py-2.5 pr-4 text-right text-slate-600">{{ $item->quantity }}</td>
                            <td class="py-2.5 pr-4 text-right text-slate-600">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 pr-4 text-right text-slate-400">{{ $item->tax_rate }}%</td>
                            <td class="py-2.5 text-right font-medium text-slate-800">${{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="flex justify-end">
                <div class="w-56 space-y-1 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span><span>${{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tax</span><span>${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900 text-base border-t border-slate-200 pt-1">
                        <span>Total</span><span>${{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Paid</span><span class="text-green-600">${{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold pt-1 border-t border-slate-200 {{ $invoice->amount_due > 0 ? 'text-red-600' : 'text-green-700' }}">
                        <span>Amount Due</span><span>${{ number_format($invoice->amount_due, 2) }}</span>
                    </div>
                </div>
            </div>

            @if ($invoice->notes)
                <div class="mt-6 pt-4 border-t border-slate-100 text-sm text-slate-600">
                    <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Notes</p>
                    <p>{{ $invoice->notes }}</p>
                </div>
            @endif
            @if ($invoice->terms)
                <div class="mt-3 text-sm text-slate-500">
                    <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Terms</p>
                    <p>{{ $invoice->terms }}</p>
                </div>
            @endif
        </x-ui.card>

        {{-- Payments --}}
        @if ($payments->isNotEmpty())
            <x-ui.card title="Payments">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="pb-2 pr-4">Date</th>
                            <th class="pb-2 pr-4">Method</th>
                            <th class="pb-2 pr-4">Reference</th>
                            <th class="pb-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($payments as $payment)
                            <tr>
                                <td class="py-2 pr-4 text-slate-600">{{ $payment->paid_at->format('d M Y') }}</td>
                                <td class="py-2 pr-4 capitalize text-slate-600">{{ str_replace('_', ' ', $payment->method) }}</td>
                                <td class="py-2 pr-4 text-slate-400 font-mono text-xs">{{ $payment->reference ?? '—' }}</td>
                                <td class="py-2 text-right font-medium text-green-700">${{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.card>
        @endif
    </div>

    {{-- Sidebar actions --}}
    <div class="w-52 shrink-0 space-y-3">
        <x-ui.card>
            <div class="space-y-2">
                @if ($invoice->status !== 'void' && $invoice->amount_due > 0)
                    <x-ui.button wire:click="openPaymentModal" class="w-full justify-center">
                        Record Payment
                    </x-ui.button>
                @endif
                @if (in_array($invoice->status, ['draft', 'sent']))
                    <a href="{{ route('invoices.edit', $invoice) }}" wire:navigate class="block">
                        <x-ui.button variant="secondary" class="w-full justify-center">Edit Invoice</x-ui.button>
                    </a>
                @endif
                @if ($invoice->status !== 'void')
                    <button wire:click="markVoid" wire:confirm="Void this invoice? This cannot be undone."
                        class="w-full text-sm text-red-500 hover:text-red-700 text-center py-1">
                        Void Invoice
                    </button>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card title="Info">
            <dl class="space-y-2 text-xs">
                <div><dt class="text-slate-400">Client</dt><dd class="font-medium text-slate-700">{{ $invoice->client->name }}</dd></div>
                <div><dt class="text-slate-400">Payments</dt><dd class="font-medium text-slate-700">{{ $payments->count() }}</dd></div>
                @if ($invoice->sent_at)
                    <div><dt class="text-slate-400">Sent</dt><dd class="text-slate-600">{{ $invoice->sent_at->format('d M Y') }}</dd></div>
                @endif
                @if ($invoice->paid_at)
                    <div><dt class="text-slate-400">Paid</dt><dd class="text-green-700 font-medium">{{ $invoice->paid_at->format('d M Y') }}</dd></div>
                @endif
            </dl>
        </x-ui.card>
    </div>

    {{-- Record Payment Modal --}}
    <x-ui.modal :show="$showPaymentModal" title="Record Payment" wire:close="$set('showPaymentModal', false)">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <x-ui.input wire:model="paymentForm.amount" type="number" step="0.01" />
                    @error('paymentForm.amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <x-ui.input wire:model="paymentForm.paid_at" type="date" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Method</label>
                    <select wire:model="paymentForm.method" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="check">Check</option>
                        <option value="cash">Cash</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reference</label>
                    <x-ui.input wire:model="paymentForm.reference" type="text" placeholder="e.g. TRN-1234" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <x-ui.input wire:model="paymentForm.notes" type="text" />
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showPaymentModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="recordPayment">Record Payment</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
