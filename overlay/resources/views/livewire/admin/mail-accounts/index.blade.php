<div>
    <div class="mb-4 flex justify-end">
        <x-ui.button wire:click="openCreate">
            <x-ui.icon name="plus" class="h-4 w-4 mr-1" /> Add Mail Account
        </x-ui.button>
    </div>

    <x-ui.card>
        @if ($accounts->isEmpty())
            <p class="py-8 text-center text-slate-400">No mail accounts configured. Add one to enable email-to-ticket.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="pb-3 pr-4">Name</th>
                        <th class="pb-3 pr-4">Host / User</th>
                        <th class="pb-3 pr-4">Encryption</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3 pr-4">Last Polled</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($accounts as $account)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 pr-4 font-medium text-slate-900">{{ $account->name }}</td>
                            <td class="py-3 pr-4 text-slate-600">
                                <div>{{ $account->host }}:{{ $account->port }}</div>
                                <div class="text-xs text-slate-400">{{ $account->username }}</div>
                            </td>
                            <td class="py-3 pr-4 uppercase text-slate-600">{{ $account->encryption }}</td>
                            <td class="py-3 pr-4">
                                <x-ui.badge :color="$account->is_active ? 'green' : 'gray'">
                                    {{ $account->is_active ? 'Active' : 'Inactive' }}
                                </x-ui.badge>
                            </td>
                            <td class="py-3 pr-4 text-xs text-slate-400">
                                {{ $account->last_polled_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <button wire:click="toggleActive({{ $account->id }})"
                                        class="text-xs text-slate-500 hover:text-brand-700">
                                        {{ $account->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                    <button wire:click="openEdit({{ $account->id }})"
                                        class="text-slate-400 hover:text-brand-700">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <button wire:click="delete({{ $account->id }})"
                                        wire:confirm="Delete this mail account?"
                                        class="text-slate-400 hover:text-red-500">
                                        <x-ui.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-ui.card>

    <x-ui.modal :show="$showModal" title="{{ $editingId ? 'Edit Mail Account' : 'Add Mail Account' }}" wire:close="$set('showModal', false)">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="form.name" type="text" placeholder="e.g. Support Inbox" />
                @error('form.name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">IMAP Host <span class="text-red-500">*</span></label>
                    <x-ui.input wire:model="form.host" type="text" placeholder="imap.gmail.com" />
                    @error('form.host') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Port</label>
                    <x-ui.input wire:model="form.port" type="number" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Encryption</label>
                    <select wire:model="form.encryption" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="ssl">SSL</option>
                        <option value="tls">TLS</option>
                        <option value="none">None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mailbox</label>
                    <x-ui.input wire:model="form.mailbox" type="text" placeholder="INBOX" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username <span class="text-red-500">*</span></label>
                <x-ui.input wire:model="form.username" type="text" placeholder="support@yourdomain.com" />
                @error('form.username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Password {{ $editingId ? '(leave blank to keep existing)' : '' }}
                </label>
                <x-ui.input wire:model="form.password" type="password" />
                @error('form.password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2">
                <input wire:model="form.is_active" type="checkbox" id="is_active" class="rounded border-slate-300">
                <label for="is_active" class="text-sm text-slate-700">Active (poll this mailbox)</label>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancel</x-ui.button>
            <x-ui.button wire:click="save">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
