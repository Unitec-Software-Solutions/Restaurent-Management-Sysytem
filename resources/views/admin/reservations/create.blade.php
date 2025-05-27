@extends('layouts.admin')

@php
    use App\Models\Employee;
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
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input type="time" 
                                       name="end_time" 
                                       id="end_time" 
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
                                @foreach(Employee::all() as $steward)
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Time</label>
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                           value="{{ $reservation->check_in_time ? $reservation->check_in_time->format('Y-m-d H:i:s') : 'Not checked in' }}"
                                           class="px-3 py-2 border border-gray-200 rounded-md bg-gray-100 flex-1"
                                           readonly>
                                    @if(!$reservation->check_in_time)
                                        <form method="POST" action="{{ route('admin.reservations.check-in', $reservation) }}">
                                            @csrf
                                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                                                Check In
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Time</label>
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                           value="{{ $reservation->check_out_time ? $reservation->check_out_time->format('Y-m-d H:i:s') : 'Not checked out' }}"
                                           class="px-3 py-2 border border-gray-200 rounded-md bg-gray-100 flex-1"
                                           readonly>
                                    @if($reservation->check_in_time && !$reservation->check_out_time)
                                        <form method="POST" action="{{ route('admin.reservations.check-out', $reservation) }}">
                                            @csrf
                                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                                                Check Out
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
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
@endsection