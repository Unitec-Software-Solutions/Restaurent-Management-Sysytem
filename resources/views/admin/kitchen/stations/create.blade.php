{{-- filepath: resources/views/admin/kitchen/stations/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Kitchen Station')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.kitchen.stations.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Kitchen Station</h1>
                <p class="text-gray-600">Set up a new kitchen workstation</p>
            </div>
        </div>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
            <h3 class="font-medium mb-2">Please fix the following errors:</h3>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.kitchen.stations.store') }}" class="space-y-6">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Station Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Station Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g., Main Kitchen, Grill Station">
                </div>

                <!-- Branch -->
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                    <select name="branch_id" id="branch_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Station Type -->
                <div>
                    <label for="station_type" class="block text-sm font-medium text-gray-700 mb-1">Station Type</label>
                    <select name="station_type" id="station_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Type</option>
                        <option value="hot_kitchen" {{ old('station_type') === 'hot_kitchen' ? 'selected' : '' }}>Hot Kitchen</option>
                        <option value="cold_kitchen" {{ old('station_type') === 'cold_kitchen' ? 'selected' : '' }}>Cold Kitchen</option>
                        <option value="grill" {{ old('station_type') === 'grill' ? 'selected' : '' }}>Grill Station</option>
                        <option value="prep" {{ old('station_type') === 'prep' ? 'selected' : '' }}>Prep Station</option>
                        <option value="dessert" {{ old('station_type') === 'dessert' ? 'selected' : '' }}>Dessert Station</option>
                        <option value="serving" {{ old('station_type') === 'serving' ? 'selected' : '' }}>Serving Station</option>
                        <option value="other" {{ old('station_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g., Ground Floor, Kitchen Area">
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Brief description of the station's purpose">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Capacity & Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Capacity & Settings</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Staff Capacity</label>
                    <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 2) }}" min="1" max="20"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Priority Order -->
                <div>
                    <label for="priority_order" class="block text-sm font-medium text-gray-700 mb-1">Priority Order</label>
                    <input type="number" name="priority_order" id="priority_order" value="{{ old('priority_order', 1) }}" min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Lower numbers get higher priority</p>
                </div>

                <!-- Status -->
                <div class="flex items-center pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Station is active</span>
                    </label>
                </div>
            </div>

            <!-- Equipment -->
            <div class="mt-6">
                <label for="equipment" class="block text-sm font-medium text-gray-700 mb-1">Equipment Available</label>
                <textarea name="equipment" id="equipment" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="e.g., Gas stoves, Oven, Fryer, Grills">{{ old('equipment') }}</textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.kitchen.stations.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center">
                    <i class="fas fa-save mr-2"></i> Create Station
                </button>
            </div>
        </div>
    </form>
</div>
@endsection