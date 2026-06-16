@props(['variant' => 'primary', 'tag' => 'button', 'loading' => null])

@php
    $base = 'inline-flex min-h-9 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

    $variants = [
        'primary' => 'bg-slate-950 text-white shadow-sm hover:bg-slate-800 focus:ring-slate-500',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 focus:ring-slate-400',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700 focus:ring-red-500',
        'ghost' => 'text-slate-600 hover:bg-slate-100 focus:ring-slate-400',
        'brand' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700 focus:ring-brand-500',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);

    // When a `loading` target is supplied, auto-disable the button and show a
    // spinner while that Livewire action is in flight.
    $loadingAttrs = $loading
        ? ['wire:loading.attr' => 'disabled', 'wire:target' => $loading]
        : [];
@endphp

<{{ $tag }} {{ $attributes->merge($loadingAttrs)->merge(['class' => $classes, 'type' => $tag === 'button' ? 'button' : null]) }}>
    @if ($loading)
        <svg wire:loading wire:target="{{ $loading }}" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    @endif
    {{ $slot }}
</{{ $tag }}>
