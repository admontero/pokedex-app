@props([
    'base',
    'fallback',
    'width',
    'height',
    'alt' => '',
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
    'decoding' => 'async',
])

@php
    $srcset = function (string $format) use ($base, $width): string {
        return collect([1, 2])
            ->map(fn (int $density): string => asset($base.'-'.($width * $density).'.'.$format).' '.$density.'x')
            ->implode(', ');
    };
@endphp

<picture class="d-block">
    <source type="image/avif" srcset="{{ $srcset('avif') }}">
    <source type="image/webp" srcset="{{ $srcset('webp') }}">

    <img
        src="{{ asset($fallback) }}"
        alt="{{ $alt }}"
        width="{{ $width }}"
        height="{{ $height }}"
        loading="{{ $loading }}"
        fetchpriority="{{ $fetchpriority }}"
        decoding="{{ $decoding }}"
        {{ $attributes }}
    >
</picture>
