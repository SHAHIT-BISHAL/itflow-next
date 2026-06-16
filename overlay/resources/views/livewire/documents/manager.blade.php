<div>
    <div class="mb-4 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Document
        </x-ui.button>
    </div>

    @if ($showViewer && $viewing)
        @php
            $reviewColors = [
                'due' => 'red',
                'upcoming' => 'yellow',
                'current' => 'green',
                'unscheduled' => 'slate',
            ];
        @endphp

        <x-ui.card>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-semibold text-slate-900">{{ $viewing->title }}</h2>
                        <x-ui.badge>{{ str($viewing->document_type)->headline() }}</x-ui.badge>
                        <x-ui.badge :color="$reviewColors[$viewing->review_status] ?? 'slate'">
                            Review {{ $viewing->review_status }}
                        </x-ui.badge>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        @if ($viewing->review_due_at)
                            Review due {{ $viewing->review_due_at->toFormattedDateString() }}
                        @else
                            No review scheduled
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <x-ui.button variant="secondary" wire:click="markReviewed({{ $viewing->id }})">
                        <x-ui.icon name="check" class="h-4 w-4" />
                        Reviewed
                    </x-ui.button>
                    <x-ui.button variant="secondary" wire:click="edit({{ $viewing->id }})">
                        <x-ui.icon name="pencil" class="h-4 w-4" />
                        Edit
                    </x-ui.button>
                    <button wire:click="closeViewer" class="text-slate-400 hover:text-slate-600">
                        <x-ui.icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="prose prose-sm max-w-none whitespace-pre-wrap text-slate-800">{{ $viewing->content ?: 'No content yet.' }}</div>

            @if ($viewing->relations->isNotEmpty())
                <div class="mt-6 border-t border-slate-100 pt-4">
                    <h3 class="text-sm font-semibold text-slate-900">Related Records</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($viewing->relations as $relation)
                            @php
                                $related = $relation->related;
                                $label = class_basename($relation->related_type);
                                $name = $related?->name ?? 'Missing record';
                            @endphp
                            <x-ui.badge color="blue">{{ $label }}: {{ $name }}</x-ui.badge>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($viewing->versions->isNotEmpty())
                <div class="mt-6 border-t border-slate-100 pt-4">
                    <h3 class="text-sm font-semibold text-slate-900">Version History</h3>
                    <ol class="mt-2 divide-y divide-slate-100 text-sm">
                        @foreach ($viewing->versions as $version)
                            <li class="flex items-center justify-between gap-4 py-2">
                                <div>
                                    <p class="font-medium text-slate-800">v{{ $version->version_number }} - {{ $version->change_summary }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $version->created_at->diffForHumans() }}
                                        @if ($version->createdBy) by {{ $version->createdBy->name }} @endif
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400">{{ strlen($version->content ?? '') }} chars</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            <p class="mt-4 text-xs text-slate-400">
                Last updated {{ $viewing->updated_at->diffForHumans() }}
                @if ($viewing->createdBy) - by {{ $viewing->createdBy->name }} @endif
                @if ($viewing->reviewedBy) - reviewed by {{ $viewing->reviewedBy->name }} @endif
            </p>
        </x-ui.card>
    @else
        <div class="space-y-2">
            @forelse ($documents as $doc)
                @php
                    $reviewColors = [
                        'due' => 'red',
                        'upcoming' => 'yellow',
                        'current' => 'green',
                        'unscheduled' => 'slate',
                    ];
                @endphp

                <div class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 hover:border-brand-200 hover:bg-brand-50"
                     wire:click="view({{ $doc->id }})">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium text-slate-900">{{ $doc->title }}</p>
                            <x-ui.badge>{{ str($doc->document_type)->headline() }}</x-ui.badge>
                            <x-ui.badge :color="$reviewColors[$doc->review_status] ?? 'slate'">
                                {{ $doc->review_status }}
                            </x-ui.badge>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Updated {{ $doc->updated_at->diffForHumans() }}
                            - {{ $doc->versions_count }} version(s)
                            - {{ $doc->relations_count }} related
                            @if ($doc->review_due_at) - review due {{ $doc->review_due_at->toFormattedDateString() }} @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2" x-on:click.stop>
                        <button wire:click="edit({{ $doc->id }})" class="text-slate-400 hover:text-brand-600">
                            <x-ui.icon name="pencil" class="h-4 w-4" />
                        </button>
                        <button wire:click="delete({{ $doc->id }})" wire:confirm="Delete this document?" class="text-slate-400 hover:text-red-600">
                            <x-ui.icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            @empty
                <x-ui.card>
                    <p class="py-6 text-center text-sm text-slate-500">No documents yet.</p>
                </x-ui.card>
            @endforelse
        </div>
    @endif

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Document' : 'New Document'" maxWidth="2xl">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="title" label="Title" wire:model="title" :error="$errors->first('title')" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="document_type" type="select" label="Type" wire:model="document_type" :error="$errors->first('document_type')">
                    <option value="general">General</option>
                    <option value="runbook">Runbook / SOP</option>
                    <option value="network">Network</option>
                    <option value="onboarding">Onboarding</option>
                    <option value="security">Security</option>
                    <option value="vendor">Vendor</option>
                </x-ui.input>
                <x-ui.input name="review_due_at" type="date" label="Review Due" wire:model="review_due_at" :error="$errors->first('review_due_at')" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Content</label>
                <textarea wire:model="content" name="content" rows="12"
                          class="block w-full rounded-lg border-slate-300 font-mono text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                          placeholder="Write your documentation here..."></textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">Related Assets</p>
                    <div class="max-h-36 space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3">
                        @forelse ($assets as $asset)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" wire:model="assetIds" value="{{ $asset->id }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                {{ $asset->name }}
                            </label>
                        @empty
                            <p class="text-xs text-slate-500">No assets yet.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">Related Domains</p>
                    <div class="max-h-36 space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3">
                        @forelse ($domains as $domain)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" wire:model="domainIds" value="{{ $domain->id }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                {{ $domain->name }}
                            </label>
                        @empty
                            <p class="text-xs text-slate-500">No domains yet.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">Related Passwords</p>
                    <div class="max-h-36 space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3">
                        @forelse ($passwords as $password)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" wire:model="passwordIds" value="{{ $password->id }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                {{ $password->name }}
                            </label>
                        @empty
                            <p class="text-xs text-slate-500">No passwords yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
