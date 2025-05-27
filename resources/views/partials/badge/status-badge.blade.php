@props([
    'status' => 'success', // success, warning, danger
    'showText' => true,
    'customClass' => ''
])

@php
    $baseClasses = 'px-2 py-1 text-xs font-medium rounded-full inline-flex items-center';

    $statusClasses = [
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        'primary' => 'bg-indigo-100 text-indigo-800'
    ][$status] ?? 'bg-gray-100 text-gray-800';

    $iconClasses = [
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'danger' => 'fas fa-times-circle',
        'info' => 'fas fa-info-circle',
        'primary' => 'fas fa-circle'
    ][$status] ?? 'fas fa-circle';
@endphp

<span class="{{ $baseClasses }} {{ $statusClasses }} {{ $customClass }}">
    <i class="{{ $iconClasses }} mr-1"></i>
    @if($showText)
        {{ ucfirst($status) }}
    @endif
</span>