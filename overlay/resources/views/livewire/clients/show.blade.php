<div>
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('clients.index') }}" class="text-sm text-brand-700 hover:underline">&larr; Back to Clients</a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-6">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Type</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $client->type ?: '—' }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Contacts</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $client->contacts_count }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Locations</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $client->locations_count }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Assets</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $client->assets_count }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Documents</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $client->documents_count }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Domains</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $client->domains_count }}</p>
        </x-ui.card>
    </div>

    <div class="mb-4 border-b border-slate-200">
        <nav class="-mb-px flex gap-6 overflow-x-auto">
            @foreach ([
                'overview'  => 'Overview',
                'contacts'  => 'Contacts',
                'locations' => 'Locations',
                'assets'    => 'Assets',
                'documents' => 'Documents',
                'passwords' => 'Passwords',
                'domains'   => 'Domains',
            ] as $key => $label)
                <button wire:click="setTab('{{ $key }}')"
                        class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium {{ $tab === $key ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    @if ($tab === 'overview')
        <x-ui.card title="Client Details">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Website</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        @if ($client->website)
                            <a href="{{ $client->website }}" target="_blank" rel="noopener noreferrer" class="text-brand-700 hover:underline">{{ $client->website }}</a>
                        @else —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Net Terms</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $client->net_terms }} days</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Default Rate</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $client->rate ? number_format($client->rate, 2) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Tax ID</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $client->tax_id_number ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Notes</dt>
                    <dd class="mt-1 text-sm text-slate-900 whitespace-pre-line">{{ $client->notes ?: '—' }}</dd>
                </div>
            </dl>
        </x-ui.card>
    @elseif ($tab === 'contacts')
        @livewire('contacts.manager', ['client' => $client], key('contacts-' . $client->id))
    @elseif ($tab === 'locations')
        @livewire('locations.manager', ['client' => $client], key('locations-' . $client->id))
    @elseif ($tab === 'assets')
        @livewire('assets.manager', ['client' => $client], key('assets-' . $client->id))
    @elseif ($tab === 'documents')
        @livewire('documents.manager', ['client' => $client], key('documents-' . $client->id))
    @elseif ($tab === 'passwords')
        @livewire('passwords.manager', ['client' => $client], key('passwords-' . $client->id))
    @elseif ($tab === 'domains')
        @livewire('domains.manager', ['client' => $client], key('domains-' . $client->id))
    @endif
</div>
