@props(['title' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title || $actions)
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            @if ($title)
                <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
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
