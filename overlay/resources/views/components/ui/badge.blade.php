@props(['color' => 'slate'])

@php
    $colors = [
        'slate'  => 'bg-slate-100 text-slate-700 ring-slate-200',
        'gray'   => 'bg-slate-100 text-slate-700 ring-slate-200',
        'green'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'red'    => 'bg-red-50 text-red-700 ring-red-200',
        'yellow' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-200',
        'blue'   => 'bg-sky-50 text-sky-700 ring-sky-200',
        'purple' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'brand'  => 'bg-brand-50 text-brand-700 ring-brand-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset ' . ($colors[$color] ?? $colors['slate'])]) }}>
    {{ $slot }}
</span>
