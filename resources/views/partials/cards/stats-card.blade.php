@props([
    'title' => '',
    'value' => '',
    'trend' => '',
    'icon' => 'fas fa-chart-line',
    'color' => 'indigo'
])

<div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm">{{ $title }}</p>
            <h3 class="text-2xl font-bold mt-1">{{ $value }}</h3>
            <p class="text-green-500 text-xs mt-1 flex items-center">
                <i class="fas fa-arrow-up mr-1"></i> {{ $trend }}
            </p>
        </div>
        <div class="bg-{{ $color }}-100 p-3 rounded-full">
            <i class="{{ $icon }} text-{{ $color }}-600 text-xl"></i>
        </div>
    </div>
</div>