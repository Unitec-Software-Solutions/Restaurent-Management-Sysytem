{{-- filepath: resources/views/admin/kitchen/stations/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kitchen Stations')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kitchen Stations</h1>
                <p class="text-gray-600">Manage kitchen stations and their configurations</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.kitchen.stations.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Station
                </a>
                <a href="{{ route('admin.kitchen.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Kitchen
                </a>
            </div>
        </div>
    </div>

    <!-- Stations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($kitchenStations ?? [] as $station)
            <div class="bg-white rounded-lg shadow-sm border {{ $station->is_active ? 'border-green-200' : 'border-gray-200' }}">
                <!-- Station Header -->
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full {{ $station->is_active ? 'bg-green-100' : 'bg-gray-100' }}">
                                <i class="fas fa-industry {{ $station->is_active ? 'text-green-600' : 'text-gray-600' }} text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $station->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $station->branch->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $station->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $station->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <!-- Station Details -->
                <div class="p-6">
                    @if($station->description)
                        <p class="text-gray-600 mb-4">{{ $station->description }}</p>
                    @endif
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Station Code:</span>
                            <span class="font-medium">{{ $station->station_code ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Created:</span>
                            <span class="font-medium">{{ $station->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-4 border-t bg-gray-50">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.kitchen.stations.edit', $station) }}" 
                           class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm text-center">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        <button onclick="toggleStationStatus({{ $station->id }}, {{ $station->is_active ? 'false' : 'true' }})" 
                                class="flex-1 {{ $station->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-3 py-2 rounded text-sm">
                            {{ $station->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-industry"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Kitchen Stations</h3>
                <p class="text-gray-500 mb-4">Create your first kitchen station to get started.</p>
                <a href="{{ route('admin.kitchen.stations.create') }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i> Create Station
                </a>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function toggleStationStatus(stationId, isActive) {
    const action = isActive === 'true' ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${action} this kitchen station?`)) {
        fetch(`/admin/kitchen/stations/${stationId}/toggle`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_active: isActive === 'true' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update station status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the station');
        });
    }
}
</script>
@endpush
@endsection