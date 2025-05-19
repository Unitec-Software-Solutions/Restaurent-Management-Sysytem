@extends('layouts.main')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Edit Reservation (Admin)</h1>
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

                <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
                    @csrf
                    @method('PUT')

                    <!-- Show reservation details for admin reference -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Reservation Details (Read Only)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reservation ID</label>
                                <input type="text" value="{{ $reservation->id }}" class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                                <input type="text" value="{{ $reservation->created_at }}" class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <input type="text" value="{{ ucfirst($reservation->status) }}" class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                <input type="text" value="{{ $reservation->branch_id }}" class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Reservation Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name', $reservation->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $reservation->phone) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ old('email', $reservation->email) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" 
                                       name="date" 
                                       id="date" 
                                       value="{{ old('date', $reservation->date ? \Carbon\Carbon::parse($reservation->date)->format('Y-m-d') : null) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <input type="time" 
                                       name="start_time" 
                                       id="start_time" 
                                       value="{{ old('start_time', $reservation->start_time ? \Carbon\Carbon::parse($reservation->start_time)->format('H:i') : null) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input type="time" 
                                       name="end_time" 
                                       id="end_time" 
                                       value="{{ old('end_time', $reservation->end_time ? \Carbon\Carbon::parse($reservation->end_time)->format('H:i') : null) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="number_of_people" class="block text-sm font-medium text-gray-700 mb-1">Number of People</label>
                                <input type="number" 
                                       name="number_of_people" 
                                       id="number_of_people" 
                                       value="{{ old('number_of_people', $reservation->number_of_people) }}"
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
                                    $isAssigned = in_array($table->id, $assignedTableIds ?? []);
                                @endphp
                                <label class="cursor-pointer">
                                    <input type="checkbox"
                                           name="assigned_table_ids[]"
                                           value="{{ $table->id }}"
                                           class="hidden peer"
                                           {{ $isAssigned ? 'checked' : '' }}
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

                    <!-- Reservation Status -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Reservation Status</h2>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" 
                                    id="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <option value="pending" {{ $reservation->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $reservation->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ $reservation->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Email/SMS Notification -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Notification Options</h2>
                        <div>
                            <label for="send_notification" class="block text-sm font-medium text-gray-700 mb-1">Send Notification</label>
                            <select name="send_notification" 
                                    id="send_notification" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="none">None</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Update Reservation
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