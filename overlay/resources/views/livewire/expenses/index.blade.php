<div>
    <div class="mb-4 grid grid-cols-3 gap-4">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">This Month</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($totalMonth, 2) }}</p>
        </x-ui.card>
    </div>

    <div class="mb-4 flex gap-3 items-center justify-between">
        <div class="flex gap-2 flex-1">
            <div class="relative max-w-xs flex-1">
                <x-ui.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search expenses…"
                    class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-brand-500 focus:outline-none" />
            </div>
            <select wire:model.live="category"
                class="rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                <option value="">All categories</option>
                <option value="general">General</option>
                <option value="software">Software</option>
                <option value="hardware">Hardware</option>
                <option value="travel">Travel</option>
                <option value="labour">Labour</option>
                <option value="other">Other</option>
            </select>
        </div>
        <x-ui.button wire:click="openCreate">
            <x-ui.icon name="plus" class="h-4 w-4 mr-1" /> Add Expense
        </x-ui.button>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="pb-3 pr-4">Date</th>
                        <th class="pb-3 pr-4">Description</th>
                        <th class="pb-3 pr-4">Category</th>
                        <th class="pb-3 pr-4">Vendor</th>
                        <th class="pb-3 pr-4">Client</th>
                        <th class="pb-3 pr-4">Billable</th>
                        <th class="pb-3 pr-4 text-right">Amount</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($expenses as $expense)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 pr-4 text-slate-500 text-xs">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-800">{{ $expense->description }}</td>
                            <td class="py-3 pr-4 capitalize text-slate-600">{{ $expense->category }}</td>
                            <td class="py-3 pr-4 text-slate-500">{{ $expense->vendor ?? '—' }}</td>
                            <td class="py-3 pr-4 text-slate-500">{{ $expense->client?->name ?? '—' }}</td>
                            <td class="py-3 pr-4">
                                @if ($expense->is_billable)
                                    <x-ui.badge color="blue">Billable</x-ui.badge>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-right font-semibold text-slate-900">${{ number_format($expense->amount, 2) }}</td>
                            <td class="py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <button wire:click="openEdit({{ $expense->id }})" class="text-slate-400 hover:text-brand-700">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="delete({{ $expense->id }})" wire:confirm="Delete this expense?"
                                        class="text-slate-400 hover:text-red-500">
                                        <x-ui.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">No expenses yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($expenses->hasPages())
            <div class="mt-4">{{ $expenses->links() }}</div>
        @endif
    </x-ui.card>

    <x-ui.modal :show="$showModal" title="{{ $editingId ? 'Edit Expense' : 'Add Expense' }}" wire:close="$set('showModal', false)">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="form.description" type="text" placeholder="What was purchased?" />
                @error('form.description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <x-ui.input wire:model="form.amount" type="number" step="0.01" placeholder="0.00" />
                    @error('form.amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <x-ui.input wire:model="form.expense_date" type="date" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select wire:model="form.category" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="general">General</option>
                        <option value="software">Software</option>
                        <option value="hardware">Hardware</option>
                        <option value="travel">Travel</option>
                        <option value="labour">Labour</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Vendor</label>
                    <x-ui.input wire:model="form.vendor" type="text" placeholder="e.g. AWS, Microsoft" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Client (if billable)</label>
                <select wire:model="form.client_id" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">No client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input wire:model="form.is_billable" type="checkbox" id="is_billable" class="rounded border-slate-300">
                <label for="is_billable" class="text-sm text-slate-700">Mark as billable (charge to client)</label>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="save">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
