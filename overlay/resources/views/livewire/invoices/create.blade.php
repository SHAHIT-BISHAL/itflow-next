<div>
    <div class="flex gap-6">
        {{-- Main form --}}
        <div class="flex-1 min-w-0 space-y-4">
            {{-- Header fields --}}
            <x-ui.card>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Client <span class="text-red-500">*</span></label>
                        <select wire:model.live="form.client_id" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">Select client…</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('form.client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Contact</label>
                        <select wire:model="form.contact_id" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">No contact</option>
                            @foreach ($contacts as $contact)
                                <option value="{{ $contact['id'] }}">{{ $contact['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Invoice # <span class="text-red-500">*</span></label>
                        <x-ui.input wire:model="form.invoice_number" type="text" />
                        @error('form.invoice_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
                        <select wire:model="form.currency" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="USD">USD</option>
                            <option value="AUD">AUD</option>
                            <option value="GBP">GBP</option>
                            <option value="EUR">EUR</option>
                            <option value="CAD">CAD</option>
                            <option value="NZD">NZD</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Issue Date <span class="text-red-500">*</span></label>
                        <x-ui.input wire:model="form.issue_date" type="date" />
                        @error('form.issue_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Due Date <span class="text-red-500">*</span></label>
                        <x-ui.input wire:model="form.due_date" type="date" />
                        @error('form.due_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-ui.card>

            {{-- Line items --}}
            <x-ui.card title="Line Items">
                <table class="w-full text-sm mb-3">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-100">
                            <th class="pb-2 pr-3">Description</th>
                            <th class="pb-2 pr-3 w-20">Qty</th>
                            <th class="pb-2 pr-3 w-28">Unit Price</th>
                            <th class="pb-2 pr-3 w-20">Tax %</th>
                            <th class="pb-2 pr-3 w-28 text-right">Amount</th>
                            <th class="pb-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $item)
                            <tr class="border-b border-slate-50">
                                <td class="py-2 pr-3">
                                    <input wire:model.live="items.{{ $i }}.description" type="text" placeholder="Description…"
                                        class="w-full rounded border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none" />
                                    @error("items.{$i}.description") <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                </td>
                                <td class="py-2 pr-3">
                                    <input wire:model.live="items.{{ $i }}.quantity" type="number" step="0.01" min="0"
                                        class="w-full rounded border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none" />
                                </td>
                                <td class="py-2 pr-3">
                                    <input wire:model.live="items.{{ $i }}.unit_price" type="number" step="0.01" min="0" placeholder="0.00"
                                        class="w-full rounded border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none" />
                                </td>
                                <td class="py-2 pr-3">
                                    <input wire:model.live="items.{{ $i }}.tax_rate" type="number" step="0.01" min="0" max="100" placeholder="0"
                                        class="w-full rounded border border-slate-200 py-1.5 px-2 text-sm focus:border-brand-500 focus:outline-none" />
                                </td>
                                <td class="py-2 pr-3 text-right font-medium text-slate-700">
                                    ${{ number_format((float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0), 2) }}
                                </td>
                                <td class="py-2">
                                    @if (count($items) > 1)
                                        <button wire:click="removeItem({{ $i }})" class="text-slate-300 hover:text-red-500">
                                            <x-ui.icon name="x-mark" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button wire:click="addItem" class="text-sm text-brand-700 hover:underline flex items-center gap-1">
                    <x-ui.icon name="plus" class="h-4 w-4" /> Add line item
                </button>

                {{-- Totals --}}
                <div class="mt-4 border-t border-slate-100 pt-4 space-y-1 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tax</span>
                        <span>${{ number_format($taxTotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-slate-900 pt-1 border-t border-slate-200">
                        <span>Total</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </x-ui.card>

            {{-- Notes / Terms --}}
            <x-ui.card>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea wire:model="form.notes" rows="3"
                            class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none"
                            placeholder="Thank you for your business…"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Terms</label>
                        <textarea wire:model="form.terms" rows="3"
                            class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none"
                            placeholder="Payment due within 30 days…"></textarea>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar actions --}}
        <div class="w-52 shrink-0 space-y-3">
            <x-ui.card>
                <div class="space-y-2">
                    <x-ui.button wire:click="save('send')" loading="save" class="w-full justify-center">
                        Save & Mark Sent
                    </x-ui.button>
                    <x-ui.button wire:click="save('draft')" loading="save" variant="secondary" class="w-full justify-center">
                        Save as Draft
                    </x-ui.button>
                    <a href="{{ route('invoices.index') }}" wire:navigate class="block text-center text-sm text-slate-500 hover:text-slate-700 pt-1">
                        Cancel
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
