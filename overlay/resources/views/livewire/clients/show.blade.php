<div class="space-y-6">
    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-slate-950 text-lg font-bold text-white">
                    {{ strtoupper(substr($client->name, 0, 1)) }}
                </span>
                <div>
                    <a href="{{ route('clients.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-950">Back to Clients</a>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <h2 class="text-2xl font-semibold text-slate-950">{{ $client->name }}</h2>
                        <x-ui.badge color="sky">{{ $client->type ?: 'Client' }}</x-ui.badge>
                        @if ($client->is_favorite)
                            <x-ui.badge color="yellow">Favorite</x-ui.badge>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ $client->website ?: 'No website on file' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:w-auto">
                <div class="rounded-lg bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Net Terms</p>
                    <p class="mt-1 text-sm font-semibold text-slate-950">{{ $client->net_terms }} days</p>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rate</p>
                    <p class="mt-1 text-sm font-semibold text-slate-950">{{ $client->rate ? '$' . number_format($client->rate, 2) : '-' }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lead</p>
                    <p class="mt-1 text-sm font-semibold text-slate-950">{{ $client->is_lead ? 'Yes' : 'No' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
        <x-ui.metric-card label="Contacts" :value="$client->contacts_count" icon="user-group" color="sky" />
        <x-ui.metric-card label="Locations" :value="$client->locations_count" icon="building-office" color="slate" />
        <x-ui.metric-card label="Assets" :value="$client->assets_count" icon="computer-desktop" color="violet" />
        <x-ui.metric-card label="Documents" :value="$client->documents_count" icon="document-text" color="emerald" />
        <x-ui.metric-card label="Passwords" :value="$client->passwords_count" icon="lock-closed" color="amber" />
        <x-ui.metric-card label="Domains" :value="$client->domains_count" icon="globe-alt" color="sky" />
    </div>

    <div class="rounded-lg border border-slate-200 bg-white px-3 shadow-sm">
        <nav class="flex gap-1 overflow-x-auto py-2">
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
                        class="whitespace-nowrap rounded-md px-3 py-2 text-sm font-semibold transition {{ $tab === $key ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' }}">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    @if ($tab === 'overview')
        <x-ui.card title="Client Details">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Website</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        @if ($client->website)
                            <a href="{{ $client->website }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-brand-700 hover:underline">{{ $client->website }}</a>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tax ID</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $client->tax_id_number ?: '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</dt>
                    <dd class="mt-1 rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-700 whitespace-pre-line">{{ $client->notes ?: 'No notes have been added for this client.' }}</dd>
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
