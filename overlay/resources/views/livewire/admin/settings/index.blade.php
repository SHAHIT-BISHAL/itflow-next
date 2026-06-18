<div class="space-y-5">
    @if (session('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="saveCompanySettings" class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="space-y-5">
            <x-ui.card title="Company Profile">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Company Name" wire:model="companyName" :error="$errors->first('companyName')" />
                    <x-ui.input label="Email" type="email" wire:model="email" :error="$errors->first('email')" />
                    <x-ui.input label="Phone" wire:model="phone" :error="$errors->first('phone')" />
                    <x-ui.input label="Website" type="url" wire:model="website" :error="$errors->first('website')" />
                    <x-ui.input label="Address" wire:model="address" :error="$errors->first('address')" />
                    <x-ui.input label="City" wire:model="city" :error="$errors->first('city')" />
                    <x-ui.input label="State" wire:model="state" :error="$errors->first('state')" />
                    <x-ui.input label="Postal Code" wire:model="zip" :error="$errors->first('zip')" />
                    <x-ui.input label="Country" wire:model="country" :error="$errors->first('country')" />
                </div>
            </x-ui.card>

            <x-ui.card title="Operational Defaults">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Timezone" type="select" wire:model="timezone" :error="$errors->first('timezone')">
                        @foreach ($timezones as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </x-ui.input>
                    <x-ui.input label="Currency" wire:model="defaultCurrency" maxlength="3" :error="$errors->first('defaultCurrency')" />
                    <x-ui.input label="Default Tax Rate (%)" type="number" step="0.01" wire:model="taxRate" :error="$errors->first('taxRate')" />
                    <x-ui.input label="Default Net Terms" type="number" wire:model="defaultNetTerms" :error="$errors->first('defaultNetTerms')" />
                    <x-ui.input label="Ticket SLA Hours" type="number" wire:model="ticketSlaHours" :error="$errors->first('ticketSlaHours')" />
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-5">
            <x-ui.card title="Outbound Identity">
                <div class="space-y-4">
                    <x-ui.input label="Email From Name" wire:model="emailFromName" :error="$errors->first('emailFromName')" />
                    <x-ui.input label="Email From Address" type="email" wire:model="emailFromAddress" :error="$errors->first('emailFromAddress')" />
                    <x-ui.input label="Portal Name" wire:model="portalName" :error="$errors->first('portalName')" />
                    <x-ui.input label="Portal URL" type="url" wire:model="portalUrl" :error="$errors->first('portalUrl')" />
                </div>
            </x-ui.card>

            <div class="flex justify-end">
                <x-ui.button type="submit" loading="saveCompanySettings">
                    Save Settings
                </x-ui.button>
            </div>
        </div>
    </form>

    <form wire:submit="saveNumbering">
        <x-ui.card title="Numbering">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3">Type</th>
                            <th class="px-3 py-3">Prefix</th>
                            <th class="px-3 py-3">Next</th>
                            <th class="px-3 py-3">Padding</th>
                            <th class="px-3 py-3">Suffix</th>
                            <th class="px-3 py-3">Preview</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($numbering as $type => $row)
                            <tr>
                                <td class="px-3 py-3 font-semibold text-slate-800">{{ $row['label'] }}</td>
                                <td class="px-3 py-3">
                                    <input wire:model="numbering.{{ $type }}.prefix" class="w-24 rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" />
                                    @error("numbering.$type.prefix") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-3">
                                    <input wire:model="numbering.{{ $type }}.next_number" type="number" min="1" class="w-28 rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" />
                                    @error("numbering.$type.next_number") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-3">
                                    <input wire:model="numbering.{{ $type }}.padding" type="number" min="1" max="12" class="w-20 rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" />
                                    @error("numbering.$type.padding") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-3">
                                    <input wire:model="numbering.{{ $type }}.suffix" class="w-24 rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" />
                                    @error("numbering.$type.suffix") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-3 font-mono text-xs font-semibold text-slate-600">{{ $row['preview'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                <x-ui.button type="submit" loading="saveNumbering">
                    Save Numbering
                </x-ui.button>
            </div>
        </x-ui.card>
    </form>
</div>
