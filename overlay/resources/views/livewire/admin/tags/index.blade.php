<div>
    <div class="mb-6 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Tag
        </x-ui.button>
    </div>

    <x-ui.card>
        <div class="flex flex-wrap gap-2">
            @forelse ($tags as $tag)
                <div class="group flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-sm">
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $tag->color }}"></span>
                    {{ $tag->name }}
                    <button wire:click="edit({{ $tag->id }})" class="text-slate-400 hover:text-brand-600">
                        <x-ui.icon name="pencil" class="h-3.5 w-3.5" />
                    </button>
                    <button wire:click="delete({{ $tag->id }})" wire:confirm="Delete this tag?" class="text-slate-400 hover:text-red-600">
                        <x-ui.icon name="x-mark" class="h-3.5 w-3.5" />
                    </button>
                </div>
            @empty
                <p class="text-slate-500">No tags yet.</p>
            @endforelse
        </div>
    </x-ui.card>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Tag' : 'New Tag'" maxWidth="sm">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="Name" wire:model="name" :error="$errors->first('name')" />

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Color</label>
                <input type="color" wire:model="color" class="h-10 w-20 rounded border border-slate-300" />
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="closeModal">Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
