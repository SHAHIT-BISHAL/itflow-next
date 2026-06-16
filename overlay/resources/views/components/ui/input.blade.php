@props(['label' => null, 'name' => null, 'type' => 'text', 'error' => null])

<div>
    @if ($label)
        <label @if($name) for="{{ $name }}" @endif class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    @if ($type === 'textarea')
        <textarea {{ $attributes->merge(array_filter(['id' => $name, 'name' => $name]) + ['class' => 'block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm']) }}>{{ $slot }}</textarea>
    @elseif ($type === 'select')
        <select {{ $attributes->merge(array_filter(['id' => $name, 'name' => $name]) + ['class' => 'block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm']) }}>
            {{ $slot }}
        </select>
    @else
        <input type="{{ $type }}" {{ $attributes->merge(array_filter(['id' => $name, 'name' => $name]) + ['class' => 'block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm']) }} />
    @endif

    @if ($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
