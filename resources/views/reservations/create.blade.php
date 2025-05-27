@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Make a Reservation</h1>
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

                <form method="POST" action="{{ route('reservations.review') }}" id="reservationForm">
                    @csrf
                    <!-- Personal Information -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Personal Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ request('name', old('name')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       >
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ request('email', old('email')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ $request->phone ?? old('phone') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                                @if($errors->has('phone'))
                                    <p class="text-red-500 text-sm mt-1">{{ $errors->first('phone') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Reservation Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Select Branch</label>
                                <select name="branch_id" 
                                        id="branch_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Select a branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" 
                                                data-opening="{{ $branch->opening_time }}"
                                                data-closing="{{ $branch->closing_time }}"
                                                {{ request('branch_id', old('branch_id')) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" 
                                       name="date" 
                                       id="date" 
                                       min="{{ now()->format('Y-m-d') }}"
                                       value="{{ request('date', old('date')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <input type="time" 
                                       name="start_time" 
                                       id="start_time" 
                                       value="{{ request('start_time', old('start_time')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input type="time" 
                                       name="end_time" 
                                       id="end_time" 
                                       value="{{ request('end_time', old('end_time')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="number_of_people" class="block text-sm font-medium text-gray-700 mb-1">Number of People</label>
                            <input type="number" 
                                   name="number_of_people" 
                                   id="number_of_people" 
                                   min="1"
                                   value="{{ request('number_of_people', old('number_of_people')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                    </div>

                    <!-- Special Requests -->
                    <div class="mb-6">
                        <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Special Requests (Optional)</label>
                        <textarea name="comments" 
                                  id="comments" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ request('comments', old('comments')) }}</textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Review Reservation
                        </button>
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservationForm');
    const branchSelect = document.getElementById('branch_id');
    const phoneInput = document.getElementById('phone');
    const dateInput = document.getElementById('date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const peopleInput = document.getElementById('number_of_people');

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    // Validate time inputs
    function validateTimeInputs() {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        const selectedBranch = branchSelect.options[branchSelect.selectedIndex];
        
        if (startTime && endTime && selectedBranch.value) {
            const openingTime = selectedBranch.dataset.opening;
            const closingTime = selectedBranch.dataset.closing;
            
            // Check if times are within branch hours
            if (startTime < openingTime) {
                startTimeInput.setCustomValidity(`Start time must be after ${openingTime}`);
            } else if (endTime > closingTime) {
                endTimeInput.setCustomValidity(`End time must be before ${closingTime}`);
            } else if (startTime >= endTime) {
                endTimeInput.setCustomValidity('End time must be after start time');
            } else {
                startTimeInput.setCustomValidity('');
                endTimeInput.setCustomValidity('');
            }
        }
    }

    // Event listeners
    branchSelect.addEventListener('change', validateTimeInputs);
    startTimeInput.addEventListener('change', validateTimeInputs);
    endTimeInput.addEventListener('change', validateTimeInputs);
    dateInput.addEventListener('change', function() {
        if (dateInput.value === today) {
            const now = new Date();
            const minStart = new Date(now.getTime() + 30 * 60000);
            startTimeInput.min = minStart.toTimeString().slice(0,5);
        } else {
            startTimeInput.min = '';
        }
        validateTimeInputs();
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        validateTimeInputs();
        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
        }
    });
});
</script>
@endpush
@endsection