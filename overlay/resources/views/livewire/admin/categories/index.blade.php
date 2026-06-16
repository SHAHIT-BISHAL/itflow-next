<div>
    <div class="mb-6 flex justify-end">
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" />
            New Category
        </x-ui.button>
    </div>

    <div class="space-y-6">
        @forelse ($categories as $type => $items)
            <x-ui.card :title="ucfirst($type) . ' categories'">
                <div class="flex flex-wrap gap-2">
                    @foreach ($items as $category)
                        <div class="group flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-sm">
                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $category->color ?? '#94a3b8' }}"></span>
                            {{ $category->name }}
                            @if ($category->parent)
                                <span class="text-xs text-slate-400">in {{ $category->parent->name }}</span>
                            @endif
                            <button wire:click="edit({{ $category->id }})" class="text-slate-400 hover:text-brand-600">
                                <x-ui.icon name="pencil" class="h-3.5 w-3.5" />
                            </button>
                            <button wire:click="delete({{ $category->id }})" wire:confirm="Archive this category?" class="text-slate-400 hover:text-red-600">
                                <x-ui.icon name="x-mark" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @empty
            <x-ui.card>
                <p class="text-slate-500">No categories yet.</p>
            </x-ui.card>
        @endforelse
    </div>

    <x-ui.modal show="$wire.showModal" :title="$editingId ? 'Edit Category' : 'New Category'" maxWidth="sm">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="Name" wire:model="name" :error="$errors->first('name')" />

            <x-ui.input name="type" type="select" label="Type" wire:model="type" :error="$errors->first('type')">
                <option value="ticket">Ticket</option>
                <option value="expense">Expense</option>
                <option value="asset">Asset</option>
                <option value="document">Document</option>
            </x-ui.input>

            <x-ui.input name="parent_id" type="select" label="Parent (optional)" wire:model="parent_id">
                <option value="">— None —</option>
                @foreach ($parents as $parent)
                    @if ($parent->id !== $editingId)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endif
                @endforeach
            </x-ui.input>

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
