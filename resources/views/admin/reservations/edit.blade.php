@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Edit Reservation (Admin)</h1>
            </div>

            <div class="p-6">
                <!-- AJAX Messages Container -->
                <div id="ajax-messages" class="fixed top-4 right-4 z-50 space-y-2"></div>
                
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
                                <input type="text" value="{{ $reservation->branch->name }}" class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100" readonly>
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
                                       value="{{ old('date', $reservation->date ? $reservation->date->format('Y-m-d') : null) }}"
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

                    <!-- Steward Assignment Section -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Steward Assignment</h2>
                        <!-- Current Steward Display -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Steward</label>
                            <div class="flex items-center">
                                <span id="current-steward" class="px-3 py-2 bg-gray-100 rounded-md">
                                    @if($reservation->steward)
                                        {{ $reservation->steward->name }}
                                    @else
                                        Not assigned
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <!-- Assign Steward Form -->
                        <div class="mb-4">
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Steward</label>
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    @php
                                        $stewards = \App\Models\Employee::where('role', 'steward')
                                            ->orWhere('role', 'waiter')
                                            ->get();
                                    @endphp
                                    <select name="steward_id" id="steward-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Steward</option>
                                        @foreach($stewards as $steward)
                                            <option value="{{ $steward->id }}" {{ $reservation->steward_id == $steward->id ? 'selected' : '' }}>
                                                {{ $steward->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button type="button" id="assign-steward-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Assign
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in/Check-out Section -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Check-in/Check-out</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Time</label>
                                <div class="flex items-center gap-2">
                                    <input id="check-in-display" type="text"
                                           value="{{ $reservation->check_in_time ? $reservation->check_in_time->format('Y-m-d H:i:s') : 'Not checked in' }}"
                                           class="px-3 py-2 border border-gray-200 rounded-md bg-gray-100 flex-1"
                                           readonly>
                                    @if(!$reservation->check_in_time)
                                        <button type="button" id="check-in-btn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                                            Check In
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Time</label>
                                <div class="flex items-center gap-2">
                                    <input id="check-out-display" type="text"
                                           value="{{ $reservation->check_out_time ? $reservation->check_out_time->format('Y-m-d H:i:s') : 'Not checked out' }}"
                                           class="px-3 py-2 border border-gray-200 rounded-md bg-gray-100 flex-1"
                                           readonly>
                                    @if($reservation->check_in_time && !$reservation->check_out_time)
                                        <button type="button" id="check-out-btn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                                            Check Out
                                        </button>
                                    @endif
                                </div>
                            </div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // AJAX setup for CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Function to show flash messages
        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            messageDiv.textContent = message;
            document.getElementById('ajax-messages').appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        // Assign Steward Button
        $('#assign-steward-btn').on('click', function() {
            const stewardId = $('#steward-select').val();
            
            if (!stewardId) {
                showMessage('Please select a steward', 'error');
                return;
            }
            
            $.ajax({
                url: "{{ route('admin.reservations.assign-steward', $reservation) }}",
                method: 'POST',
                data: {
                    steward_id: stewardId
                },
                success: function(response) {
                    if (response.success) {
                        // Update the current steward display
                        $('#current-steward').text(response.steward_name);
                        showMessage('Steward assigned successfully', 'success');
                    } else {
                        showMessage(response.message || 'Failed to assign steward', 'error');
                    }
                },
                error: function(xhr) {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Error:', xhr.responseText);
                }
            });
        });

        // Check-in Button
        $('#check-in-btn').on('click', function() {
            $.ajax({
                url: "{{ route('admin.reservations.check-in', $reservation) }}",
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        // Update the check-in display and hide the button
                        $('#check-in-display').val(response.check_in_time);
                        $('#check-in-btn').remove();
                        
                        // Show the check-out button if needed
                        if (!$('#check-out-btn').length && !response.check_out_time) {
                            $('#check-out-display').parent().append(`
                                <button type="button" id="check-out-btn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                                    Check Out
                                </button>
                            `);
                            
                            // Add event listener to the new button
                            $('#check-out-btn').on('click', checkOutHandler);
                        }
                        
                        showMessage('Reservation checked in successfully', 'success');
                    } else {
                        showMessage(response.message || 'Failed to check in', 'error');
                    }
                },
                error: function(xhr) {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Error:', xhr.responseText);
                }
            });
        });

        // Check-out Button Handler
        function checkOutHandler() {
            $.ajax({
                url: "{{ route('admin.reservations.check-out', $reservation) }}",
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        // Update the check-out display and hide the button
                        $('#check-out-display').val(response.check_out_time);
                        $('#check-out-btn').remove();
                        showMessage('Reservation checked out successfully', 'success');
                    } else {
                        showMessage(response.message || 'Failed to check out', 'error');
                    }
                },
                error: function(xhr) {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        // Add event listener to existing check-out button
        $('#check-out-btn').on('click', checkOutHandler);
    });
</script>
@endsection