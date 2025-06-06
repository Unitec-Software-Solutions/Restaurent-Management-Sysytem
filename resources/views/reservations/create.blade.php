@extends('layouts.app')

@section('content')
<div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl animate-fade-in overflow-hidden max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 sticky top-0 z-10">
            <div class="flex items-center">
                <i class="fas fa-calendar-check text-blue-600 mr-3 text-xl"></i>
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Make a Reservation</h2>
            </div>
            <button onclick="window.history.back();" class="text-gray-500 hover:text-red-600 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Success Message (Hidden by default) -->
        <div id="successMessage" class="hidden success-message text-white px-6 py-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-2xl mr-3"></i>
                <div>
                    <h3 class="font-bold text-lg">Reservation Confirmed!</h3>
                    <p id="confirmationDetails" class="text-sm opacity-90 mt-1"></p>
                </div>
            </div>
        </div>
        
        <!-- Form Content -->
        <div class="px-4 md:px-6 py-4 md:py-6 space-y-4">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="errorContainer" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <ul id="errorList"></ul>
            </div>

            @php
                $name = old('name', $input['name'] ?? '');
                $email = old('email', $input['email'] ?? '');
                $phone = old('phone', $input['phone'] ?? '');
                $branch_id = old('branch_id', $input['branch_id'] ?? '');
                $date = old('date', $input['date'] ?? '');
                $start_time = old('start_time', $input['start_time'] ?? '');
                $end_time = old('end_time', $input['end_time'] ?? '');
                $number_of_people = old('number_of_people', $input['number_of_people'] ?? '');
                $comments = old('comments', $input['comments'] ?? '');
            @endphp

            <form id="reservationForm" method="POST" action="{{ route('reservations.review') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-gray-700 text-sm font-medium mb-1 flex items-center">
                                <span>Your Name</span>
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="name" name="name" type="text" value="{{ $name }}" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none" placeholder="John Doe" required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-gray-700 text-sm font-medium mb-1">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="email" name="email" type="email" value="{{ $email }}" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none" placeholder="john@example.com">
                            </div>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-gray-700 text-sm font-medium mb-1 flex items-center">
                                <span>Phone Number</span>
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input id="phone" name="phone" type="tel" value="{{ $phone }}" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none" placeholder="+1 (555) 123-4567" required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="number_of_people" class="block text-gray-700 text-sm font-medium mb-1 flex items-center">
                                <span>Number of People</span>
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-users text-gray-400"></i>
                                </div>
                                <select id="number_of_people" name="number_of_people" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none appearance-none bg-white" required>
                                    <option value="" disabled selected>Select</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ $number_of_people == $i ? 'selected' : '' }}>
                                            {{ $i }} {{ $i == 1 ? 'person' : 'people' }}
                                        </option>
                                    @endfor
                                    <option value="11" {{ $number_of_people == 11 ? 'selected' : '' }}>11+ people</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reservation Details -->
                    <div class="space-y-4">
                        <div>
                            <label for="branch_id" class="block text-gray-700 text-sm font-medium mb-1 flex items-center">
                                <span>Select Branch</span>
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-store text-gray-400"></i>
                                </div>
                                <select id="branch_id" name="branch_id" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none appearance-none bg-white" required>
                                    <option value="" disabled selected>Select a branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" 
                                                data-opening="{{ \Carbon\Carbon::parse($branch->opening_time)->format('H:i') }}" 
                                                data-closing="{{ \Carbon\Carbon::parse($branch->closing_time)->format('H:i') }}"
                                                {{ $branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="date" class="block text-gray-700 text-sm font-medium mb-1 flex items-center">
                                <span>Date</span>
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-day text-gray-400"></i>
                                </div>
                                <input id="date" name="date" type="date" min="{{ now()->format('Y-m-d') }}" value="{{ $date }}" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none" required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_time" class="block text-gray-700 text-sm font-medium mb-1 flex items-center">
                                    <span>Start Time</span>
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                    <input id="start_time" name="start_time" type="time" value="{{ $start_time }}" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none" required>
                                </div>
                            </div>
                            
                            <div>
                                <label for="end_time" class="block text-gray-700 text-sm font-medium mb-1 flex items-center">
                                    <span>End Time</span>
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                    <input id="end_time" name="end_time" type="time" value="{{ $end_time }}" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="comments" class="block text-gray-700 text-sm font-medium mb-1">Special Requests</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3">
                            <i class="fas fa-comment-dots text-gray-400"></i>
                        </div>
                        <textarea id="comments" name="comments" rows="3" class="input-highlight w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none" placeholder="Any special requests, dietary restrictions, or occasion details...">{{ $comments }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">We'll do our best to accommodate your requests</p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200">
                    <button type="button" onclick="window.history.back();" class="flex items-center text-gray-600 hover:text-gray-800 mb-4 sm:mb-0 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i> Cancel
                    </button>
                    <button type="submit" id="submitButton" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 flex items-center">
                        <i class="fas fa-search mr-2"></i> Review Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }
    .animate-pulse {
        animation: pulse 1.5s infinite;
    }
    .input-highlight {
        transition: all 0.3s ease;
    }
    .input-highlight:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    .time-suggestion {
        transition: all 0.2s ease;
    }
    .time-suggestion:hover {
        background-color: #f3f4f6;
        transform: scale(1.02);
    }
    .success-message {
        background: linear-gradient(135deg, #4ade80, #3b82f6);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reservationForm');
        const branchSelect = document.getElementById('branch_id');
        const dateInput = document.getElementById('date');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const timeSuggestions = document.getElementById('timeSuggestions');
        const errorContainer = document.getElementById('errorContainer');
        const errorList = document.getElementById('errorList');
        const submitButton = document.getElementById('submitButton');
        const successMessage = document.getElementById('successMessage');
        const confirmationDetails = document.getElementById('confirmationDetails');

        // Format date to display in confirmation
        function formatDate(dateString) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        // Format time to 12-hour format
        function formatTime(timeString) {
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours, 10);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        }

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        dateInput.value = today;

        // Show time suggestions when date is selected
        dateInput.addEventListener('change', function() {
            if (dateInput.value) {
                timeSuggestions.classList.remove('hidden');
                
                // If date is today, set minimum start time to current time + 30 minutes
                if (dateInput.value === today) {
                    const now = new Date();
                    const minStart = new Date(now.getTime() + 30 * 60000);
                    startTimeInput.min = minStart.toTimeString().slice(0,5);
                    
                    // If current time is after 8 PM, suggest tomorrow
                    if (now.getHours() >= 20) {
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        dateInput.value = tomorrow.toISOString().split('T')[0];
                    }
                } else {
                    startTimeInput.min = '';
                }
            } else {
                timeSuggestions.classList.add('hidden');
            }
            validateTimeInputs();
        });

        // Time suggestion buttons
        document.querySelectorAll('.time-suggestion').forEach(button => {
            button.addEventListener('click', function() {
                startTimeInput.value = this.dataset.start;
                endTimeInput.value = this.dataset.end;
                validateTimeInputs();
            });
        });

        // Validate time inputs based on branch opening hours
        function validateTimeInputs() {
            const startTime = startTimeInput.value;
            const endTime = endTimeInput.value;
            const selectedBranch = branchSelect.options[branchSelect.selectedIndex];
            
            // Clear previous errors
            errorContainer.classList.add('hidden');
            errorList.innerHTML = '';
            
            if (startTime && endTime && selectedBranch.value) {
                const openingTime = selectedBranch.dataset.opening;
                const closingTime = selectedBranch.dataset.closing;
                
                let errors = [];
                
                if (startTime < openingTime) {
                    errors.push(`Start time must be after ${formatTime(openingTime)} (branch opening time)`);
                }
                
                if (endTime > closingTime) {
                    errors.push(`End time must be before ${formatTime(closingTime)} (branch closing time)`);
                }
                
                if (startTime >= endTime) {
                    errors.push('End time must be after start time');
                }
                
                // Check if reservation is at least 30 minutes
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                const duration = (end - start) / (1000 * 60); // in minutes
                
                if (duration < 30) {
                    errors.push('Reservation must be at least 30 minutes');
                }
                
                if (errors.length > 0) {
                    showErrors(errors);
                    return false;
                }
            }
            
            return true;
        }
        
        function showErrors(errors) {
            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                li.className = 'flex items-start';
                errorList.appendChild(li);
            });
            errorContainer.classList.remove('hidden');
            
            // Scroll to errors
            errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Validate on branch or time change
        branchSelect.addEventListener('change', validateTimeInputs);
        startTimeInput.addEventListener('change', validateTimeInputs);
        endTimeInput.addEventListener('change', validateTimeInputs);

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            
            // Validate all required fields
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            let errors = [];
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    errors.push(`${field.labels[0].textContent.trim().replace(' *', '')} is required`);
                    isValid = false;
                }
            });
            
            // Validate time inputs
            if (!validateTimeInputs()) {
                isValid = false;
            }
            
            if (!isValid) {
                if (errors.length > 0) {
                    showErrors(errors);
                }
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-search mr-2"></i> Review Reservation';
                return;
            }
            
            // Simulate API call delay
            setTimeout(() => {
                // Show success message
                const branchName = branchSelect.options[branchSelect.selectedIndex].text;
                const formattedDate = formatDate(dateInput.value);
                const formattedStartTime = formatTime(startTimeInput.value);
                const formattedEndTime = formatTime(endTimeInput.value);
                
                confirmationDetails.innerHTML = `
                    Your reservation at <strong>${branchName}</strong> is confirmed for 
                    <strong>${formattedDate}</strong> from <strong>${formattedStartTime}</strong> 
                    to <strong>${formattedEndTime}</strong>. A confirmation has been sent to your email.
                `;
                
                // Hide form and show success
                form.classList.add('hidden');
                successMessage.classList.remove('hidden');
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-search mr-2"></i> Review Reservation';
                
                // Add pulse animation to success message
                successMessage.classList.add('animate-pulse');
                setTimeout(() => {
                    successMessage.classList.remove('animate-pulse');
                    
                    // Actually submit the form after showing the success message
                    setTimeout(() => {
                        form.submit();
                    }, 3000);
                }, 3000);
                
            }, 1500);
        });
    });
</script>
@endpush