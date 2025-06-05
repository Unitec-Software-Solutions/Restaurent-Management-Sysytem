@php
    $presetColors = [
        'default' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'ring' => 'ring-gray-500/10'],
        'warning' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-800', 'ring' => 'ring-yellow-600/20'],
        'success' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'ring' => 'ring-green-600/20'],
        'info'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'ring' => 'ring-blue-700/10'],
        'danger'  => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'ring' => 'ring-red-600/10'],
    ];

    // Use color override if given, else map by status
    $resolved = $presetColors[$status] ?? $presetColors['default'];

    $bgClass = $color ? "bg-{$color}-50" : $resolved['bg'];
    $textColorClass = $color ? "text-{$color}-700" : $resolved['text']; // Renamed variable
    $ringClass = $color ? "ring-{$color}-600/10" : $resolved['ring'];
@endphp

<span class="inline-flex items-center rounded-md {{ $bgClass }} px-2 py-1 text-xs font-medium {{ $textColorClass }} ring-1 {{ $ringClass }} ring-inset">
    {{ $showText ? $text : '' }} {{-- This remains unchanged --}}
</span>