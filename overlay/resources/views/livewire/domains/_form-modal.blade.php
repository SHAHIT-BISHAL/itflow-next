<x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Domain' : 'Add Domain'">
    <form wire:submit="save" class="space-y-4">
        <x-ui.input name="name" label="Domain Name" wire:model="name" placeholder="example.com" :error="$errors->first('name')" />

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Client</label>
            <select wire:model="client_id" name="client_id"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                <option value="">No client</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
            @error('client_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-ui.input name="registrar" label="Registrar" wire:model="registrar" :error="$errors->first('registrar')" />
            <x-ui.input name="dns_provider" label="DNS Provider" wire:model="dns_provider" :error="$errors->first('dns_provider')" />
            <x-ui.input name="expires_at" type="date" label="Domain Expires" wire:model="expires_at" :error="$errors->first('expires_at')" />

            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="auto_renew" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                    <span class="text-sm text-slate-700">Auto-renew</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-ui.input name="ssl_expires_at" type="date" label="SSL Expires" wire:model="ssl_expires_at" :error="$errors->first('ssl_expires_at')" />
            <x-ui.input name="ssl_issuer" label="SSL Issuer" wire:model="ssl_issuer" :error="$errors->first('ssl_issuer')" />
        </div>

        <x-ui.input name="notes" type="textarea" label="Notes" wire:model="notes" rows="2" :error="$errors->first('notes')" />

        <div class="flex justify-end gap-2 pt-2">
            <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
            <x-ui.button type="submit">Save</x-ui.button>
        </div>
    </form>
</x-ui.modal>
