@extends('layouts.admin')

@php
    use App\Models\Employee;
    // Calculate default times
    $now = now();
    $start_time = $now->format('H:i');
    $end_time = $now->copy()->addHours(2)->format('H:i');
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Create Reservation</h1>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.reservations.store') }}">
                    @csrf

                    <!-- Customer Information -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Customer Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name"
                                       value="{{ old('name', $defaultName ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $defaultPhone ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Reservation Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" 
                                       name="date" 
                                       id="date" 
                                       value="{{ old('date', $defaultDate ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <input type="time" 
                                       name="start_time" 
                                       id="start_time" 
                                       value="{{ old('start_time', $start_time) }}"
                                       step="900"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input type="time" 
                                       name="end_time" 
                                       id="end_time" 
                                       value="{{ old('end_time', $end_time) }}"
                                       step="900"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="number_of_people" class="block text-sm font-medium text-gray-700 mb-1">Number of People</label>
                                <input type="number" 
                                       name="number_of_people" 
                                       id="number_of_people" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Tables -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Assign Tables</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($tables as $table)
                                @php
                                    $isAvailable = in_array($table->id, $availableTableIds ?? []);
                                @endphp
                                <label class="cursor-pointer">
                                    <input type="checkbox"
                                           name="assigned_table_ids[]"
                                           value="{{ $table->id }}"
                                           class="hidden peer"
                                           {{ $isAvailable ? '' : 'disabled' }}>
                                    <div class="w-20 h-20 flex flex-col items-center justify-center border rounded-md text-xs p-2
                                        peer-checked:bg-blue-500 peer-checked:text-white
                                        {{ $isAvailable ? 'bg-white hover:bg-blue-100 cursor-pointer' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                                        <span>Table {{ $table->id }}</span>
                                        <span>Cap: {{ $table->capacity }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Unavailable tables are grayed out and cannot be selected.</p>
                    </div>

                    <!-- Steward Assignment Section -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Steward Assignment</h2>
                        <div>
                            <label for="steward_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Steward</label>
                            <select name="steward_id" id="steward_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Steward</option>
                                @foreach(App\Models\Employee::all() as $steward)
                                    <option value="{{ $steward->id }}" {{ old('steward_id') == $steward->id ? 'selected' : '' }}>
                                        {{ $steward->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Check-in/Check-out Section -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Check-in/Check-out</h2>
                        <div class="text-gray-500">
                            Check-in and check-out will be available after the reservation is created.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Create Reservation
                        </button>
                        <a href="{{ route('admin.reservations.index') }}" class="text-gray-600 hover:text-gray-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    function pad(n) {
        return n.toString().padStart(2, '0');
    }

    function setEndTimeFromStart() {
        if (startTimeInput && endTimeInput) {
            const [h, m] = startTimeInput.value.split(':').map(Number);
            if (!isNaN(h) && !isNaN(m)) {
                let endHour = h + 2;
                let endMinute = m;
                if (endHour >= 24) endHour -= 24;
                endTimeInput.value = pad(endHour) + ':' + pad(endMinute);
            }
        }
    }

    // Always set start time to local time on page load
    if (startTimeInput) {
        const now = new Date();
        startTimeInput.value = pad(now.getHours()) + ':' + pad(now.getMinutes());
    }

    // Set end time to 2 hours from start time on load
    setEndTimeFromStart();

    // Update end time whenever start time changes
    if (startTimeInput) {
        startTimeInput.addEventListener('change', setEndTimeFromStart);
    }
});
</script>
@endsection