@props(['name'])

@php
    // Small, dependency-free icon set. The paths follow the Lucide/Feather
    // 24x24 grid, so they align with the rest of the UI.
    $icons = [
        'arrow-left' => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
        'arrow-left-long' => '<path d="M21 12H3"/><path d="m8 17-5-5 5-5"/>',
        'arrow-right-long' => '<path d="M3 12h18"/><path d="m16 17 5-5-5-5"/>',
        'xmark' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    ];
@endphp

<svg
    {{ $attributes->class('pk-icon') }}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
>
    {!! $icons[$name] ?? '' !!}
</svg>
