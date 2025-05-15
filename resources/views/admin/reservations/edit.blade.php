@extends('layouts.admin')

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
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ old('email', $reservation->email) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Assign Tables -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Assign Tables</h2>
                        <div>
                            <label for="assigned_table_ids" class="block text-sm font-medium text-gray-700 mb-1">Select Tables</label>
                            <select name="assigned_table_ids[]" 
                                    id="assigned_table_ids" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    multiple>
                                @foreach ($tables as $table)
                                    <option value="{{ $table->id }}" 
                                            {{ in_array($table->id, json_decode($reservation->assigned_table_ids ?? '[]')) ? 'selected' : '' }}>
                                        Table {{ $table->id }} (Capacity: {{ $table->capacity }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Hold down Ctrl (Windows) or Command (Mac) to select multiple tables.</p>
                        </div>
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