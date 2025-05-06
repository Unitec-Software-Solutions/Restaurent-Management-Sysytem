@props([
    'type' => 'default',
    'size' => 'md'
])

@php
    $types = [
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        'default' => 'bg-gray-100 text-gray-800'
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-sm',
        'lg' => 'px-3 py-1 text-base'
    ];

    $classes = $types[$type] . ' ' . $sizes[$size] . ' inline-flex font-medium rounded-full';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>