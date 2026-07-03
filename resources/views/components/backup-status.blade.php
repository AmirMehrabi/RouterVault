@props(['status'])

@php
    $labels = [
        'pending' => 'Pending',
        'running' => 'Running',
        'success' => 'Successful',
        'partial_success' => 'Partial success',
        'failed' => 'Failed',
    ];
@endphp

<x-ui.badge :status="$status" {{ $attributes }}>
    {{ $labels[$status] ?? ucfirst($status) }}
</x-ui.badge>
