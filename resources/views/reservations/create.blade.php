@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full mb-4 shadow-lg">
                <i class="fas fa-calendar-check text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Make a Reservation</h1>
            <p class="text-lg text-gray-600">Book your perfect dining experience with us</p>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-full text-sm font-medium">
                    1
                </div>
                <span class="ml-2 text-sm font-medium text-blue-600">Details</span>
            </div>
            <div class="w-16 h-1 bg-gray-200 mx-4"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 bg-gray-300 text-gray-600 rounded-full text-sm font-medium">
                    2
                </div>
                <span class="ml-2 text-sm font-medium text-gray-500">Review</span>
            </div>
            <div class="w-16 h-1 bg-gray-200 mx-4"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 bg-gray-300 text-gray-600 rounded-full text-sm font-medium">
                    3
                </div>
                <span class="ml-2 text-sm font-medium text-gray-500">Confirm</span>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-utensils text-white mr-3 text-xl"></i>
                        <h2 class="text-2xl font-bold text-white">Reservation Details</h2>
                    </div>
                    <button onclick="window.history.back();" class="text-white hover:text-red-200 transition-colors duration-200 p-2 rounded-full hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Success Message (Hidden by default) -->
            <div id="successMessage" class="hidden bg-gradient-to-r from-green-500 to-emerald-600 text-white px-8 py-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-bold text-lg">Reservation Confirmed!</h3>
                        <p id="confirmationDetails" class="text-sm opacity-90 mt-1"></p>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="px-8 py-8">
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-6 mb-8 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="errorContainer" class="hidden bg-red-50 border-l-4 border-red-400 p-6 mb-8 rounded-r-lg">
                    <ul id="errorList" class="text-red-700"></ul>
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

                <form id="reservationForm" method="POST" action="{{ route('reservations.review') }}" class="space-y-8">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Personal Information -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                <i class="fas fa-user text-blue-500 mr-2"></i>Personal Information
                            </h3>
                            
                            <!-- Name -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-semibold text-gray-700">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="name" id="name" value="{{ $name }}" 
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300" 
                                           placeholder="Enter your full name" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                </div>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-semibold text-gray-700">
                                    Email Address
                                </label>
                                <div class="relative">
                                    <input type="email" name="email" id="email" value="{{ $email }}" 
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300" 
                                           placeholder="your@email.com">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <label for="phone" class="block text-sm font-semibold text-gray-700">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="tel" name="phone" id="phone" value="{{ $phone }}" 
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300" 
                                           placeholder="+1 (555) 123-4567" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                </div>
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Number of People -->
                            <div class="space-y-2">
                                <label for="number_of_people" class="block text-sm font-semibold text-gray-700">
                                    Number of People <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="number_of_people" id="number_of_people" 
                                            class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 appearance-none cursor-pointer" 
                                            required>
                                        <option value="" disabled selected>👥 Select number of people</option>
                                        @for($i = 1; $i <= 20; $i++)
                                            <option value="{{ $i }}" {{ old('number_of_people', $number_of_people) == $i ? 'selected' : '' }}>
                                                {{ $i }} {{ $i == 1 ? 'person' : 'people' }}
                                            </option>
                                        @endfor
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                @error('number_of_people')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Reservation Details -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>Reservation Details
                            </h3>

                            <!-- Organization Selection -->
                            <div class="space-y-2">
                                <label for="organization_id" class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-utensils text-blue-500 mr-2"></i>
                                    Restaurant <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="organization_id" id="organization_id" 
                                            class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 appearance-none cursor-pointer hover:border-blue-300" 
                                            required>
                                        <option value="" disabled selected>🍽️ Select a restaurant</option>
                                        @foreach($organizations as $organization)
                                            <option value="{{ $organization['id'] }}" 
                                                    {{ old('organization_id', $organization_id) == $organization['id'] ? 'selected' : '' }}>
                                                {{ $organization['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                @error('organization_id')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Branch Selection -->
                            <div class="space-y-2">
                                <label for="branch_id" class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-store text-blue-500 mr-2"></i>
                                    Restaurant Branch <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="branch_id" id="branch_id" 
                                            class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 appearance-none cursor-pointer hover:border-blue-300 disabled:bg-gray-50 disabled:cursor-not-allowed" 
                                            required disabled>
                                        <option value="" disabled selected>🏢 First select a restaurant</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <div id="branch-hours-text" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    <span class="text-sm text-blue-700">Select a restaurant above to view available branches</span>
                                </div>
                                @error('branch_id')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div class="space-y-2">
                                <label for="date" class="block text-sm font-semibold text-gray-700">
                                    Reservation Date <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="date" id="date" value="{{ $date }}" 
                                           min="{{ date('Y-m-d') }}"
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300" 
                                           required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                @error('date')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Time Selection -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Start Time -->
                                <div class="space-y-2">
                                    <label for="start_time" class="block text-sm font-semibold text-gray-700">
                                        Start Time <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="time" name="start_time" id="start_time" value="{{ $start_time }}" 
                                               class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300" 
                                               required>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <i class="fas fa-clock text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('start_time')
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- End Time -->
                                <div class="space-y-2">
                                    <label for="end_time" class="block text-sm font-semibold text-gray-700">
                                        End Time <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="time" name="end_time" id="end_time" value="{{ $end_time }}" 
                                               class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300" 
                                               required>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <i class="fas fa-clock text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('end_time')
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Requests -->
                    <div class="space-y-2">
                        <label for="comments" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-comment-dots text-blue-500 mr-2"></i>Special Requests
                        </label>
                        <div class="relative">
                            <textarea name="comments" id="comments" rows="4" 
                                      class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 resize-none" 
                                      placeholder="Any special requests, dietary restrictions, or occasion details...">{{ $comments }}</textarea>
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-edit text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">We'll do our best to accommodate your requests</p>
                        @error('comments')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200">
                        <button type="button" onclick="window.history.back();" 
                                class="flex items-center text-gray-600 hover:text-gray-800 mb-4 sm:mb-0 transition-colors duration-200 px-4 py-2 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-arrow-left mr-2"></i> Cancel
                        </button>
                        <button type="submit" id="submitButton" 
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-100 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-search mr-2"></i> Review Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Features Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100 text-center hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-blue-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Quick Confirmation</h3>
                <p class="text-sm text-gray-600">Get instant confirmation for your reservation</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100 text-center hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-green-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Premium Experience</h3>
                <p class="text-sm text-gray-600">Enjoy exceptional dining with top-quality service</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100 text-center hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-edit text-purple-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Easy Modifications</h3>
                <p class="text-sm text-gray-600">Call us anytime to modify your reservation</p>
            </div>
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
        50% { transform: scale(1.02); }
    }
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
    .animate-pulse-subtle {
        animation: pulse 2s infinite;
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    
    /* Form animations */
    select:focus, input:focus, textarea:focus {
        transform: translateY(-1px);
    }
    
    /* Loading spinner */
    .loading-spinner {
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration
    const CONFIG = {
        API_ENDPOINTS: {
            BRANCHES: '/api/organizations/{organizationId}/branches'
        },
        CACHE_EXPIRY: 5 * 60 * 1000 // 5 minutes
    };

    // State management
    const state = {
        branchCache: new Map()
    };

    // DOM elements
    const elements = {
        organizationSelect: document.getElementById('organization_id'),
        branchSelect: document.getElementById('branch_id'),
        branchHoursText: document.getElementById('branch-hours-text')
    };

    // Branch management functions
    const branchManager = {
        clearBranchDropdown() {
            if (elements.branchSelect) {
                elements.branchSelect.innerHTML = '<option value="" disabled selected>🏢 First select a restaurant</option>';
                elements.branchSelect.disabled = true;
            }
            
            if (elements.branchHoursText) {
                elements.branchHoursText.innerHTML = `
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="text-sm text-blue-700">Select a restaurant above to view available branches</span>
                `;
            }
        },

        showBranchLoading() {
            if (elements.branchSelect) {
                elements.branchSelect.innerHTML = '<option value="">🔄 Loading branches...</option>';
                elements.branchSelect.disabled = true;
            }
        },

        showBranchError(message = 'Failed to load branches') {
            if (elements.branchSelect) {
                elements.branchSelect.innerHTML = `<option value="" disabled selected>❌ ${message}</option>`;
                elements.branchSelect.disabled = true;
            }
        },

        populateBranches(branches) {
            if (!elements.branchSelect) return;
            
            if (!branches || branches.length === 0) {
                elements.branchSelect.innerHTML = '<option value="" disabled selected>📍 No branches available</option>';
                elements.branchSelect.disabled = true;
                return;
            }

            // Clear and populate options
            elements.branchSelect.innerHTML = '<option value="" disabled selected>🏢 Select a restaurant branch</option>';
            
            branches.forEach((branch) => {
                const option = document.createElement('option');
                option.value = branch.id;
                
                // Enhanced option text
                let optionText = `🏢 ${branch.name}`;
                if (branch.address) {
                    const shortAddress = branch.address.length > 30 
                        ? branch.address.substring(0, 30) + '...' 
                        : branch.address;
                    optionText += ` - ${shortAddress}`;
                }
                option.textContent = optionText;
                
                // Store branch data
                option.dataset.opening = branch.opening_time || '';
                option.dataset.closing = branch.closing_time || '';
                option.dataset.address = branch.address || '';
                option.dataset.phone = branch.phone || '';
                
                elements.branchSelect.appendChild(option);
            });

            // Enable dropdown
            elements.branchSelect.disabled = false;
        },

        async fetchBranches(organizationId) {
            // Check cache first
            const cacheKey = `org_${organizationId}`;
            if (state.branchCache.has(cacheKey)) {
                const cached = state.branchCache.get(cacheKey);
                if (Date.now() - cached.timestamp < CONFIG.CACHE_EXPIRY) {
                    this.populateBranches(cached.data);
                    return;
                }
            }

            this.showBranchLoading();
            
            const url = `/api/organizations/${organizationId}/branches`;
            
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && Array.isArray(data.branches)) {
                        // Cache the result
                        state.branchCache.set(cacheKey, {
                            data: data.branches,
                            timestamp: Date.now()
                        });
                        this.populateBranches(data.branches);
                    } else {
                        this.showBranchError('Invalid response from server');
                    }
                } else {
                    this.showBranchError(`Server error: ${response.status}`);
                }
            } catch (error) {
                this.showBranchError('Network error - please check your connection');
            }
        }
    };

    // Event handlers
    const eventHandlers = {
        handleOrganizationChange(event) {
            const organizationId = parseInt(event.target.value);
            branchManager.clearBranchDropdown();
            
            if (!organizationId || isNaN(organizationId)) return;
            branchManager.fetchBranches(organizationId);
        }
    };

    // Initialize form
    function initializeForm() {
        if (!elements.organizationSelect || !elements.branchSelect) return;

        // Add event listeners
        elements.organizationSelect.addEventListener('change', eventHandlers.handleOrganizationChange);
        
        // Initialize with pre-selected values
        if (elements.organizationSelect.value) {
            eventHandlers.handleOrganizationChange({ target: elements.organizationSelect });
        }
    }

    // Initialize
    initializeForm();
});
</script>
@endpush