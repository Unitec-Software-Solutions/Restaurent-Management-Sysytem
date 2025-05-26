<div class="dashboard-card bg-white rounded-xl shadow p-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
            <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $value }}</h3>
            <p class="text-sm text-{{ $trend === 'up' ? 'green' : 'red' }}-500 mt-2">
                <i class="fas fa-arrow-{{ $trend === 'up' ? 'up' : 'down' }} mr-1"></i> {{ $change }}
            </p>
        </div>
        <div class="bg-{{ $color }}-100 p-3 rounded-full">
            <i class="fas fa-{{ $icon }} text-{{ $color }}-600 text-xl"></i>
        </div>
    </div>
</div>