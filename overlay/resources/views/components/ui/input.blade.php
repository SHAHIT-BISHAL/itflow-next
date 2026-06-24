@props(['label' => null, 'name' => null, 'type' => 'text', 'error' => null])

<div>
    @if ($label)
        <label @if($name) for="{{ $name }}" @endif class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif

    @if ($type === 'textarea')
        <textarea {{ $attributes->merge(array_filter(['id' => $name, 'name' => $name]) + ['class' => 'block w-full rounded-md border-slate-300 bg-white text-sm shadow-sm transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500']) }}>{{ $slot }}</textarea>
    @elseif ($type === 'select')
        <select {{ $attributes->merge(array_filter(['id' => $name, 'name' => $name]) + ['class' => 'block w-full rounded-md border-slate-300 bg-white text-sm shadow-sm transition focus:border-slate-500 focus:ring-slate-500']) }}>
            {{ $slot }}
        </select>
    @else
        <input type="{{ $type }}" {{ $attributes->merge(array_filter(['id' => $name, 'name' => $name]) + ['class' => 'block w-full rounded-md border-slate-300 bg-white text-sm shadow-sm transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500']) }} />
    @endif

    @if ($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
