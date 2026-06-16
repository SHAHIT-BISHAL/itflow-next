{{--
    Global toast container. Driven by Livewire/Alpine events.
    Fire from any Livewire component with:
        $this->dispatch('toast', message: 'Saved', type: 'success');
    Or across a redirect with a session flash:
        session()->flash('toast', ['message' => 'Saved', 'type' => 'success']);
    Types: success (default), error, info
--}}
<div
    x-data="{
        toasts: [],
        add(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({
                id,
                message: detail.message ?? '',
                type: detail.type ?? 'success',
            });
            setTimeout(() => this.remove(id), detail.timeout ?? 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
        init() {
            @if (session()->has('toast'))
                this.add(@js(session('toast')));
            @endif
        },
    }"
    @toast.window="add($event.detail)"
    class="pointer-events-none fixed inset-0 z-[100] flex flex-col items-end gap-2 px-4 py-6 sm:p-6"
    aria-live="assertive"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg"
            :class="{
                'border-green-200': toast.type === 'success',
                'border-red-200':   toast.type === 'error',
                'border-blue-200':  toast.type === 'info',
            }"
        >
            <div class="mt-0.5 shrink-0">
                <template x-if="toast.type === 'success'">
                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                </template>
            </div>
            <p class="flex-1 text-sm text-slate-700" x-text="toast.message"></p>
            <button type="button" @click="remove(toast.id)" class="shrink-0 text-slate-400 hover:text-slate-600" aria-label="Dismiss">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
