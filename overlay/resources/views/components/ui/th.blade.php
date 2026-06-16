@props(['align' => 'left'])

@php
    $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : '');
@endphp

<th {{ $attributes->merge(['class' => trim("px-3 py-2 {$alignClass}")]) }}>{{ $slot }}</th>
