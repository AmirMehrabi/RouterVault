@props([
    'href' => null,
    'icon' => 'eye',
    'tooltip' => '',
    'variant' => 'default',
    'method' => null,
    'confirm' => null,
    'as' => 'a',
])

@php
$icons = [
    'eye' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
    'edit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
    'trash' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
    'download' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'play' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'pause' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'document' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
];

$variants = [
    'default' => 'text-gray-400 hover:text-blue-600 hover:bg-blue-50',
    'danger' => 'text-gray-400 hover:text-red-600 hover:bg-red-50',
    'success' => 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50',
    'warning' => 'text-gray-400 hover:text-amber-600 hover:bg-amber-50',
    'primary' => 'text-gray-400 hover:text-blue-600 hover:bg-blue-50',
];

$variantClass = $variants[$variant] ?? $variants['default'];
@endphp

@if($method && $confirm)
    <form method="POST" action="{{ $href }}" class="inline" onsubmit="return confirm('{{ $confirm }}')">
        @csrf
        @method($method)
        <button type="submit" class="inline-flex items-center justify-center rounded-lg p-1.5 transition-colors {{ $variantClass }}" title="{{ $tooltip }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icons[$icon] !!}</svg>
        </button>
    </form>
@elseif($as === 'form')
    <form method="POST" action="{{ $href }}" class="inline">
        @csrf
        <button type="submit" class="inline-flex items-center justify-center rounded-lg p-1.5 transition-colors {{ $variantClass }}" title="{{ $tooltip }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icons[$icon] !!}</svg>
        </button>
    </form>
@elseif($href)
    <a href="{{ $href }}" class="inline-flex items-center justify-center rounded-lg p-1.5 transition-colors {{ $variantClass }}" title="{{ $tooltip }}">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icons[$icon] !!}</svg>
    </a>
@endif
