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
                $organization_id = old('organization_id', $input['organization_id'] ?? '');
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
                        <!-- Organization Selection -->
                        <div class="mb-4">
                            <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Restaurant <span class="text-red-500">*</span>
                            </label>
                            <select name="organization_id" id="organization_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                    required>
                                <option value="" disabled selected>Select a restaurant</option>
                                @foreach($organizations as $organization)
                                    <option value="{{ $organization['id'] }}" 
                                            {{ old('organization_id', $organization_id) == $organization['id'] ? 'selected' : '' }}>
                                        {{ $organization['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('organization_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Branch Selection -->
                        <div class="mb-4">
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Branch <span class="text-red-500">*</span>
                            </label>
                            <select name="branch_id" id="branch_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                    required disabled>
                                <option value="" disabled selected>First select a restaurant</option>
                            </select>
                            <div id="branch-hours-text" class="mt-2 text-sm text-blue-700 flex items-center">
                                Select a branch to view hours
                            </div>
                            @error('branch_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
    // Configuration - Fix the API endpoint
    const CONFIG = {
        API_ENDPOINTS: {
            BRANCHES: '/api/organizations/{organizationId}/branches', // Fixed endpoint
            AVAILABILITY: '/api/branches/{branchId}/availability'
        },
        CACHE_EXPIRY: 5 * 60 * 1000, // 5 minutes
        DEBOUNCE_DELAY: 300,
        MIN_RESERVATION_DURATION: 30, // minutes
        MAX_RESERVATION_DURATION: 240 // 4 hours
    };

    // State management
    const state = {
        branchCache: new Map(),
        currentOrganizationId: null,
        currentBranchId: null,
        isLoading: false,
        formValidation: {
            name: false,
            phone: false,
            organization_id: false,
            branch_id: false,
            date: false,
            start_time: false,
            end_time: false,
            number_of_people: false
        }
    };

    // DOM elements
    const elements = {
        form: document.getElementById('reservationForm'),
        organizationSelect: document.getElementById('organization_id'),
        branchSelect: document.getElementById('branch_id'),
        branchHoursText: document.getElementById('branch-hours-text'),
        errorContainer: document.getElementById('errorContainer'),
        errorList: document.getElementById('errorList'),
        submitButton: document.getElementById('submitButton'),
        
        // Form fields
        name: document.getElementById('name'),
        email: document.getElementById('email'),
        phone: document.getElementById('phone'),
        date: document.getElementById('date'),
        startTime: document.getElementById('start_time'),
        endTime: document.getElementById('end_time'),
        numberOfPeople: document.getElementById('number_of_people'),
        comments: document.getElementById('comments')
    };

    // Validation patterns
    const VALIDATION_PATTERNS = {
        name: /^[a-zA-Z\s]{2,50}$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^[\+]?[\d\s\-\(\)]{10,15}$/
    };

    /**
     * Utility functions
     */
    const utils = {
        // Debounce function for input validation
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Format time to 12-hour format
        formatTime(timeString) {
            if (!timeString || !timeString.includes(':')) return timeString;
            
            try {
                const [hours, minutes] = timeString.split(':');
                const hour = parseInt(hours, 10);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${minutes.padStart(2, '0')} ${ampm}`;
            } catch (e) {
                console.error('Error formatting time:', timeString, e);
                return timeString;
            }
        },

        // Calculate time difference in minutes
        getTimeDifference(startTime, endTime) {
            if (!startTime || !endTime) return 0;
            
            const start = new Date(`2000-01-01T${startTime}`);
            const end = new Date(`2000-01-01T${endTime}`);
            
            if (end <= start) {
                end.setDate(end.getDate() + 1); // Next day
            }
            
            return (end - start) / (1000 * 60); // Minutes
        },

        // Show loading spinner
        showLoading(element, message = 'Loading...') {
            const spinner = `
                <div class="flex items-center justify-center py-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
                    <span class="ml-2 text-sm text-gray-600">${message}</span>
                </div>
            `;
            element.innerHTML = spinner;
        },

        // Get CSRF token
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }
    };

    /**
     * Validation functions
     */
    const validation = {
        // Validate individual field
        validateField(fieldName, value) {
            const field = elements[fieldName];
            if (!field) return false;

            let isValid = false;
            let errorMessage = '';

            switch (fieldName) {
                case 'name':
                    isValid = VALIDATION_PATTERNS.name.test(value.trim());
                    errorMessage = 'Name must be 2-50 characters and contain only letters and spaces';
                    break;

                case 'email':
                    isValid = !value || VALIDATION_PATTERNS.email.test(value.trim());
                    errorMessage = 'Please enter a valid email address';
                    break;

                case 'phone':
                    isValid = VALIDATION_PATTERNS.phone.test(value.replace(/\s/g, ''));
                    errorMessage = 'Please enter a valid phone number (10-15 digits)';
                    break;

                case 'date':
                    const selectedDate = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    isValid = selectedDate >= today;
                    errorMessage = 'Please select a future date';
                    break;

                case 'startTime':
                case 'endTime':
                    isValid = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value);
                    errorMessage = 'Please select a valid time';
                    break;

                case 'numberOfPeople':
                    const num = parseInt(value);
                    isValid = num >= 1 && num <= 20;
                    errorMessage = 'Number of people must be between 1 and 20';
                    break;

                default:
                    isValid = value.trim().length > 0;
                    errorMessage = 'This field is required';
            }

            this.updateFieldValidation(field, isValid, errorMessage);
            state.formValidation[fieldName] = isValid;
            return isValid;
        },

        // Update field visual validation state
        updateFieldValidation(field, isValid, errorMessage = '') {
            const container = field.closest('div');
            const existingError = container.querySelector('.field-error');

            // Remove existing error
            if (existingError) {
                existingError.remove();
            }

            // Update field styling
            if (isValid) {
                field.classList.remove('border-red-500', 'focus:ring-red-500');
                field.classList.add('border-gray-300', 'focus:ring-indigo-500');
            } else if (field.value.trim()) {
                field.classList.remove('border-gray-300', 'focus:ring-indigo-500');
                field.classList.add('border-red-500', 'focus:ring-red-500');

                // Add error message
                if (errorMessage) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error mt-1 text-sm text-red-600 flex items-center';
                    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${errorMessage}`;
                    container.appendChild(errorDiv);
                }
            }
        },

        // Validate time range
        validateTimeRange() {
            const startTime = elements.startTime.value;
            const endTime = elements.endTime.value;

            if (!startTime || !endTime) return true;

            const duration = utils.getTimeDifference(startTime, endTime);
            
            if (duration < CONFIG.MIN_RESERVATION_DURATION) {
                this.showTimeError('Minimum reservation duration is 30 minutes');
                return false;
            }

            if (duration > CONFIG.MAX_RESERVATION_DURATION) {
                this.showTimeError('Maximum reservation duration is 4 hours');
                return false;
            }

            this.clearTimeError();
            return true;
        },

        // Show time validation error
        showTimeError(message) {
            const timeContainer = elements.endTime.closest('.grid');
            let errorDiv = timeContainer.querySelector('.time-error');
            
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'time-error col-span-2 mt-2 text-sm text-red-600 flex items-center';
                timeContainer.appendChild(errorDiv);
            }
            
            errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
        },

        // Clear time validation error
        clearTimeError() {
            const timeContainer = elements.endTime.closest('.grid');
            const errorDiv = timeContainer.querySelector('.time-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        },

        // Validate entire form
        validateForm() {
            const requiredFields = ['name', 'phone', 'date', 'startTime', 'endTime', 'numberOfPeople'];
            let isFormValid = true;

            requiredFields.forEach(fieldName => {
                const field = elements[fieldName];
                if (field && !this.validateField(fieldName, field.value)) {
                    isFormValid = false;
                }
            });

            // Additional validations
            if (!state.currentOrganizationId) {
                isFormValid = false;
                this.showFormError('Please select a restaurant');
            }

            if (!state.currentBranchId) {
                isFormValid = false;
                this.showFormError('Please select a branch');
            }

            if (!this.validateTimeRange()) {
                isFormValid = false;
            }

            this.updateSubmitButton(isFormValid);
            return isFormValid;
        },

        // Show form-level error
        showFormError(message) {
            if (!elements.errorContainer || !elements.errorList) return;
            
            elements.errorList.innerHTML = `<li>${message}</li>`;
            elements.errorContainer.classList.remove('hidden');
        },

        // Clear form errors
        clearFormErrors() {
            if (elements.errorContainer) {
                elements.errorContainer.classList.add('hidden');
            }
            if (elements.errorList) {
                elements.errorList.innerHTML = '';
            }
        },

        // Update submit button state
        updateSubmitButton(isValid) {
            if (!elements.submitButton) return;

            if (isValid && !state.isLoading) {
                elements.submitButton.disabled = false;
                elements.submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                elements.submitButton.classList.add('hover:from-blue-700', 'hover:to-indigo-700');
            } else {
                elements.submitButton.disabled = true;
                elements.submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                elements.submitButton.classList.remove('hover:from-blue-700', 'hover:to-indigo-700');
            }
        }
    };

    /**
     * Branch management functions
     */
    const branchManager = {
        // Clear branch dropdown
        clearBranchDropdown() {
            elements.branchSelect.innerHTML = '<option value="" disabled selected>First select a restaurant</option>';
            elements.branchSelect.disabled = true;
            state.currentBranchId = null;
            
            if (elements.branchHoursText) {
                elements.branchHoursText.innerHTML = `
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span>Select a branch to view hours</span>
                `;
                elements.branchHoursText.className = 'mt-2 text-sm text-blue-700 flex items-center';
            }
        },

        // Show loading state
        showBranchLoading() {
            elements.branchSelect.innerHTML = '<option value="">Loading branches...</option>';
            elements.branchSelect.disabled = true;
            state.isLoading = true;
            
            if (elements.branchHoursText) {
                elements.branchHoursText.innerHTML = `
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-gray-600 mr-2"></div>
                        <span>Loading branch information...</span>
                    </div>
                `;
                elements.branchHoursText.className = 'mt-2 text-sm text-gray-600';
            }
        },

        // Show error state
        showBranchError(message = 'Failed to load branches') {
            elements.branchSelect.innerHTML = `<option value="" disabled selected>${message}</option>`;
            elements.branchSelect.disabled = true;
            state.isLoading = false;
            
            if (elements.branchHoursText) {
                elements.branchHoursText.innerHTML = `
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    <span>Error loading branch information</span>
                `;
                elements.branchHoursText.className = 'mt-2 text-sm text-red-600 flex items-center';
            }
        },

        // Populate branch dropdown
        populateBranches(branches) {
            state.isLoading = false;
            
            if (!branches || branches.length === 0) {
                elements.branchSelect.innerHTML = '<option value="" disabled selected>No branches available</option>';
                elements.branchSelect.disabled = true;
                
                if (elements.branchHoursText) {
                    elements.branchHoursText.innerHTML = `
                        <i class="fas fa-info-circle text-orange-600 mr-2"></i>
                        <span>No branches available for this restaurant</span>
                    `;
                    elements.branchHoursText.className = 'mt-2 text-sm text-orange-600 flex items-center';
                }
                return;
            }

            // Clear and populate options
            elements.branchSelect.innerHTML = '<option value="" disabled selected>Select a branch</option>';
            
            branches.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.id;
                option.textContent = `${branch.name}${branch.address ? ' - ' + branch.address : ''}`;
                
                // Store branch data
                option.dataset.opening = branch.opening_time || '';
                option.dataset.closing = branch.closing_time || '';
                option.dataset.address = branch.address || '';
                option.dataset.phone = branch.phone || '';
                
                elements.branchSelect.appendChild(option);
            });

            elements.branchSelect.disabled = false;
            
            if (elements.branchHoursText) {
                elements.branchHoursText.innerHTML = `
                    <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                    <span>Select a branch to view details and hours</span>
                `;
                elements.branchHoursText.className = 'mt-2 text-sm text-blue-700 flex items-center';
            }
            
            console.log(`Successfully loaded ${branches.length} branches`);
        },

        // Fetch branches for organization
        async fetchBranches(organizationId) {
            // Check cache first
            const cacheKey = `org_${organizationId}`;
            if (state.branchCache.has(cacheKey)) {
                const cached = state.branchCache.get(cacheKey);
                if (Date.now() - cached.timestamp < CONFIG.CACHE_EXPIRY) {
                    console.log('Using cached branches for organization:', organizationId);
                    this.populateBranches(cached.data);
                    return;
                }
            }

            this.showBranchLoading();
            
            try {
                const url = CONFIG.API_ENDPOINTS.BRANCHES.replace('{organizationId}', organizationId);
                console.log('Fetching branches from URL:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': utils.getCsrfToken()
                    }
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success && Array.isArray(data.branches)) {
                    // Cache the results
                    state.branchCache.set(cacheKey, {
                        data: data.branches,
                        timestamp: Date.now()
                    });
                    
                    this.populateBranches(data.branches);
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
                
            } catch (error) {
                console.error('Error fetching branches:', error);
                
                // Show appropriate error message
                if (error.message.includes('NetworkError') || error.name === 'TypeError') {
                    this.showBranchError('Network error. Please check your connection.');
                } else if (error.message.includes('404')) {
                    this.showBranchError('Restaurant not found');
                } else if (error.message.includes('500')) {
                    this.showBranchError('Server error. Please try again later.');
                } else {
                    this.showBranchError('Failed to load branches. Please try again.');
                }
                
                validation.showFormError(`Error loading branches: ${error.message}`);
            }
        },

        // Update branch details display
        updateBranchDetails(branchOption) {
            if (!branchOption || !elements.branchHoursText) return;

            const opening = branchOption.dataset.opening;
            const closing = branchOption.dataset.closing;
            const address = branchOption.dataset.address;

            let hoursHtml = '';
            let className = 'mt-2 text-sm flex items-center space-x-2';

            if (opening && closing) {
                const formattedOpening = utils.formatTime(opening);
                const formattedClosing = utils.formatTime(closing);
                
                hoursHtml = `
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-clock mr-1"></i>
                            <span class="font-medium">${formattedOpening} - ${formattedClosing}</span>
                        </div>
                `;
                
                if (address) {
                    hoursHtml += `
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <span class="text-xs">${address}</span>
                        </div>
                    `;
                }
                
                hoursHtml += '</div>';
                className = 'mt-2 text-sm';
            } else {
                hoursHtml = `
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    <span class="text-yellow-700">Branch hours not available</span>
                `;
                className = 'mt-2 text-sm text-yellow-600 flex items-center';
            }

            elements.branchHoursText.innerHTML = hoursHtml;
            elements.branchHoursText.className = className;
        }
    };

    /**
     * Event handlers
     */
    const eventHandlers = {
        // Handle organization selection
        handleOrganizationChange(event) {
            const organizationId = parseInt(event.target.value);
            console.log('Organization changed to:', organizationId);
            
            // Clear previous selections
            branchManager.clearBranchDropdown();
            state.currentOrganizationId = organizationId;
            validation.clearFormErrors();
            
            if (!organizationId || isNaN(organizationId)) {
                console.log('No valid organization selected');
                return;
            }
            
            // Fetch branches
            branchManager.fetchBranches(organizationId);
        },

        // Handle branch selection
        handleBranchChange(event) {
            const branchId = parseInt(event.target.value);
            const selectedOption = event.target.options[event.target.selectedIndex];
            
            console.log('Branch changed to:', branchId);
            
            state.currentBranchId = branchId;
            
            if (selectedOption && selectedOption.value) {
                branchManager.updateBranchDetails(selectedOption);
            }
            
            validation.validateForm();
        },

        // Handle form input with debounced validation
        handleInputChange: utils.debounce(function(event) {
            const fieldName = event.target.name;
            const value = event.target.value;
            
            if (fieldName in state.formValidation) {
                validation.validateField(fieldName, value);
                validation.validateForm();
            }
        }, CONFIG.DEBOUNCE_DELAY),

        // Handle time input changes
        handleTimeChange(event) {
            validation.validateField(event.target.name, event.target.value);
            validation.validateTimeRange();
            validation.validateForm();
        },

        // Handle form submission
        handleFormSubmit(event) {
            event.preventDefault();
            
            validation.clearFormErrors();
            
            if (!validation.validateForm()) {
                validation.showFormError('Please correct the errors below before proceeding.');
                return false;
            }
            
            // Show loading state
            elements.submitButton.innerHTML = `
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    <span>Processing...</span>
                </div>
            `;
            elements.submitButton.disabled = true;
            
            // Submit form
            event.target.submit();
        }
    };

    /**
     * Initialize the form
     */
    function initializeForm() {
        // Check if required elements exist
        if (!elements.organizationSelect || !elements.branchSelect) {
            console.error('Required form elements not found');
            return;
        }

        console.log('Form elements found:', {
            organizationSelect: !!elements.organizationSelect,
            branchSelect: !!elements.branchSelect,
            branchHoursText: !!elements.branchHoursText
        });

        // Add event listeners
        elements.organizationSelect.addEventListener('change', eventHandlers.handleOrganizationChange);
        elements.branchSelect.addEventListener('change', eventHandlers.handleBranchChange);
        
        // Add input validation listeners
        Object.keys(elements).forEach(key => {
            const element = elements[key];
            if (element && element.tagName && ['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName)) {
                if (element.type === 'time') {
                    element.addEventListener('change', eventHandlers.handleTimeChange);
                } else {
                    element.addEventListener('input', eventHandlers.handleInputChange);
                    element.addEventListener('change', eventHandlers.handleInputChange);
                }
            }
        });

        // Add form submission handler
        if (elements.form) {
            elements.form.addEventListener('submit', eventHandlers.handleFormSubmit);
        }

        // Initialize with pre-selected values
        if (elements.organizationSelect.value) {
            console.log('Pre-selected organization detected:', elements.organizationSelect.value);
            setTimeout(() => {
                eventHandlers.handleOrganizationChange({ target: elements.organizationSelect });
            }, 100);
        }

        // Clean up cache periodically
        setInterval(() => {
            if (state.branchCache.size > 10) {
                const oldestKey = state.branchCache.keys().next().value;
                state.branchCache.delete(oldestKey);
            }
        }, CONFIG.CACHE_EXPIRY);

        console.log('Reservation form initialized successfully');
    }

    // Initialize the form
    initializeForm();

    // Add this to your JavaScript for debugging
    window.debugBranchSelection = function() {
        console.log('=== Debug Branch Selection ===');
        console.log('Organization Select:', elements.organizationSelect);
        console.log('Branch Select:', elements.branchSelect);
        console.log('Current Organization ID:', state.currentOrganizationId);
        console.log('Current Branch ID:', state.currentBranchId);
        console.log('Branch Cache:', state.branchCache);
        
        // Test API endpoint
        if (state.currentOrganizationId) {
            const testUrl = `/api/organizations/${state.currentOrganizationId}/branches`;
            console.log('Testing API URL:', testUrl);
            
            fetch(testUrl)
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data);
                })
                .catch(error => {
                    console.error('API Error:', error);
                });
        }
    }
});
</script>
@endpush
