@props(['align' => 'left'])

@php
    $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : '');
@endphp

<th {{ $attributes->merge(['class' => trim("px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 {$alignClass}")]) }}>{{ $slot }}</th>
