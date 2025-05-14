@props(['status'])

@php
$colors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'confirmed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
    'completed' => 'bg-blue-100 text-blue-800',
    'rejected' => 'bg-gray-100 text-gray-800',
    'waitlisted' => 'bg-purple-100 text-purple-800'
];

$color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
    {{ ucfirst($status) }}
</span> 