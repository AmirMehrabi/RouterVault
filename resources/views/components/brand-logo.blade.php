@props([
    'variant' => 'full',
    'tone' => 'color',
])

@php
    $source = match (true) {
        $variant === 'full' => 'assets/Images/Logos/routervault_full_color.png',
        $tone === 'white' => 'assets/Images/Logos/routervault_symbol_white.png',
        $tone === 'black' => 'assets/Images/Logos/routervault_symbol_black.png',
        default => 'assets/Images/Logos/routervault_symbol_color.png',
    };
@endphp

<img
    src="{{ asset($source) }}"
    alt="RouterVault"
    {{ $attributes->merge(['class' => 'block w-36 object-contain']) }}
>
