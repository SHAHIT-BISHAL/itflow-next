@props(['show' => false, 'title' => null, 'maxWidth' => 'lg'])

@php
    $maxWidths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ];

    $showExpression = is_bool($show) ? ($show ? 'true' : 'false') : $show;
    $closeAction = $attributes->get('wire:close') ?: 'closeModal';
@endphp

<div
    x-show="{{ $showExpression }}"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" wire:click="{{ $closeAction }}"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full {{ $maxWidths[$maxWidth] ?? $maxWidths['lg'] }} overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-black/5">
            @if ($title)
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ $title }}</h3>
                    <button type="button" wire:click="{{ $closeAction }}" class="rounded-md p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-700">
                        <x-ui.icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>
            @endif

            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
