@props(['label', 'value', 'icon' => 'chart-bar', 'color' => 'slate', 'meta' => null])

@php
    $colors = [
        'slate' => 'bg-slate-100 text-slate-700',
        'sky' => 'bg-sky-50 text-sky-700',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'red' => 'bg-red-50 text-red-700',
        'violet' => 'bg-violet-50 text-violet-700',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-black/[0.015]']) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $value }}</p>
        </div>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md {{ $colors[$color] ?? $colors['slate'] }}">
            <x-ui.icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>

    @if ($meta)
        <p class="mt-3 text-sm text-slate-500">{{ $meta }}</p>
    @endif
</div>
