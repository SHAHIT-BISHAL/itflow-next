<div>
    <div class="mb-4 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Document
        </x-ui.button>
    </div>

    @if ($showViewer && $viewing)
        <x-ui.card>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ $viewing->title }}</h2>
                <div class="flex items-center gap-2">
                    <x-ui.button variant="secondary" wire:click="edit({{ $viewing->id }})">
                        <x-ui.icon name="pencil" class="h-4 w-4" />
                        Edit
                    </x-ui.button>
                    <button wire:click="closeViewer" class="text-slate-400 hover:text-slate-600">
                        <x-ui.icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div class="prose prose-sm max-w-none text-slate-800 whitespace-pre-wrap">{{ $viewing->content ?: 'No content yet.' }}</div>
            <p class="mt-4 text-xs text-slate-400">
                Last updated {{ $viewing->updated_at->diffForHumans() }}
                @if ($viewing->createdBy) · by {{ $viewing->createdBy->name }} @endif
            </p>
        </x-ui.card>
    @else
        <div class="space-y-2">
            @forelse ($documents as $doc)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 hover:border-brand-200 hover:bg-brand-50 cursor-pointer"
                     wire:click="view({{ $doc->id }})">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $doc->title }}</p>
                        <p class="text-xs text-slate-500">Updated {{ $doc->updated_at->diffForHumans() }}</p>
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
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Content</label>
                <textarea wire:model="content" name="content" rows="12"
                          class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm font-mono"
                          placeholder="Write your documentation here..."></textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
