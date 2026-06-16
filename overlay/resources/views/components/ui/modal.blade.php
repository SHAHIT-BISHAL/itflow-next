@props(['show' => false, 'title' => null, 'maxWidth' => 'lg'])

@php
    $maxWidths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ];
@endphp

<div
    x-show="{{ $show }}"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <div class="fixed inset-0 bg-slate-900/50 transition-opacity" wire:click="closeModal"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full {{ $maxWidths[$maxWidth] ?? $maxWidths['lg'] }} rounded-xl bg-white shadow-xl">
            @if ($title)
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                    <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <x-ui.icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>
            @endif

            <div class="px-6 py-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
