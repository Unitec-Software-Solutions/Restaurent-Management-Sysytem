@props([
    'title' => '',
    'value' => '',
    'trend' => '',
    'trendDirection' => 'up', // 'up' or 'down'
    'icon' => 'fas fa-chart-line',
    'color' => 'indigo', // limited to Tailwind's default colors
])

@php
    // Define color mappings for safety
    $colorClasses = [
        'indigo' => [
            'bg' => 'bg-indigo-100',
            'text' => 'text-indigo-600',
        ],
        'blue' => [
            'bg' => 'bg-blue-100',
            'text' => 'text-blue-600',
        ],
        'purple' => [
            'bg' => 'bg-purple-100',
            'text' => 'text-purple-600',
        ],
        'green' => [
            'bg' => 'bg-green-100',
            'text' => 'text-green-600',
        ],
        'red' => [
            'bg' => 'bg-red-100',
            'text' => 'text-red-600',
        ],
    ];

    $trendColor = $trendDirection === 'up' ? 'text-green-500' : 'text-red-500';
    $trendIcon = $trendDirection === 'up' ? 'fa-arrow-up' : 'fa-arrow-down';
@endphp

<div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-all duration-300" role="region"
    aria-label="{{ $title }} statistics">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm">{{ $title }}</p>
            <h3 class="text-2xl font-bold mt-1">{{ $value }}</h3>
            @if ($trend)
                <p class="{{ $trendColor }} text-xs mt-1 flex items-center">
                    <i class="fas {{ $trendIcon }} mr-1"></i> {{ $trend }}
                </p>
            @endif
        </div>
        <div class="{{ $colorClasses[$color]['bg'] }} p-3 rounded-full">
            <i class="{{ $icon }} {{ $colorClasses[$color]['text'] }} text-xl"></i>
        </div>
    </div>
</div>
