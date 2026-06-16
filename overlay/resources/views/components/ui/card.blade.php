@props(['title' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200/80 bg-white shadow-sm ring-1 ring-black/[0.015]']) }}>
    @if ($title || $actions)
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
            @if ($title)
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">{{ $title }}</h3>
            @endif
            @if ($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endif
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>
</div>
