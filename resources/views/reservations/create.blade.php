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
                                            <option value="{{ $i }}" {{ $number_of_people == $i ? 'selected' : '' }}>
                                                {{ $i }} {{ $i == 1 ? 'person' : 'people' }}
                                            </option>
                                        @endfor
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
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
                                                    {{ $organization_id == $organization['id'] ? 'selected' : '' }}>
                                                {{ $organization['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Selection -->
                            <div class="space-y-2">
                                <label for="branch_id" class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-store text-blue-500 mr-2"></i>
                                    Restaurant Branch <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="branch_id" id="branch_id" 
                                            class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 appearance-none cursor-pointer hover:border-blue-300" 
                                            required>
                                        <option value="" disabled selected>🏢 Select a branch</option>
                                        <!-- Branches will be dynamically loaded here -->
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <div id="branch-info" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    <span id="branch-info-text" class="text-sm text-blue-700">Select a restaurant to view available branches</span>
                                </div>
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
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200">
                        <button type="button" onclick="window.history.back();" 
                                class="flex items-center text-gray-600 hover:text-gray-800 mb-4 sm:mb-0 transition-colors duration-200 px-4 py-2 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-arrow-left mr-2"></i> Cancel
                        </button>
                        <button type="submit" id="submitButton" 
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-black font-semibold py-3 px-8 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-100 flex items-center">
                            <i class="fas fa-search mr-2"></i> Review Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>


    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const organizationSelect = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');
    const branchInfo = document.getElementById('branch-info-text');
    const branchInfoContainer = document.getElementById('branch-info');

    // Pre-selected values from PHP
    const selectedOrganization = @json($organization_id);
    const selectedBranch = @json($branch_id);

    // Branch cache
    const branchCache = new Map();

    // Initialize branch selection
    if (selectedOrganization) {
        loadBranches(selectedOrganization).then(() => {
            if (selectedBranch) {
                branchSelect.value = selectedBranch;
                updateBranchInfo();
            }
        });
    }

    // Event listeners
    organizationSelect.addEventListener('change', function() {
        const orgId = this.value;
        loadBranches(orgId);
    });

    branchSelect.addEventListener('change', updateBranchInfo);

    // Load branches for organization
    async function loadBranches(organizationId) {
        // Clear branch dropdown
        branchSelect.innerHTML = '<option value="" disabled selected>🏢 Loading branches...</option>';
        branchSelect.disabled = true;
        
        // Check cache first
        if (branchCache.has(organizationId)) {
            populateBranchDropdown(branchCache.get(organizationId));
            return;
        }

        try {
            const response = await fetch(`/api/organizations/${organizationId}/branches`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) throw new Error('Failed to fetch branches');
            
            const { branches } = await response.json();
            
            // Cache the result
            branchCache.set(organizationId, branches);
            populateBranchDropdown(branches);
            
        } catch (error) {
            console.error('Error loading branches:', error);
            branchSelect.innerHTML = '<option value="" disabled selected>❌ Error loading branches</option>';
            branchInfo.textContent = 'Failed to load branches. Please try again.';
        }
    }

    // Populate branch dropdown
    function populateBranchDropdown(branches) {
        branchSelect.innerHTML = '<option value="" disabled selected>🏢 Select a branch</option>';
        
        if (!branches || branches.length === 0) {
            branchSelect.innerHTML = '<option value="" disabled selected>📍 No branches available</option>';
            branchInfo.textContent = 'No branches available for this restaurant';
            return;
        }

        branches.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.id;
            
            // Enhanced display text
            let text = `🏢 ${branch.name}`;
            if (branch.address) {
                text += ` - ${branch.address.substring(0, 30)}${branch.address.length > 30 ? '...' : ''}`;
            }
            option.textContent = text;
            
            // Store additional data
            option.dataset.opening = branch.opening_time;
            option.dataset.closing = branch.closing_time;
            option.dataset.address = branch.address;
            option.dataset.phone = branch.phone;
            
            branchSelect.appendChild(option);
        });

        branchSelect.disabled = false;
        updateBranchInfo();
    }

    // Update branch info display
    function updateBranchInfo() {
        const selectedOption = branchSelect.options[branchSelect.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) {
            branchInfo.textContent = 'Select a branch to view details';
            return;
        }

        const opening = selectedOption.dataset.opening;
        const closing = selectedOption.dataset.closing;
        const address = selectedOption.dataset.address;
        const phone = selectedOption.dataset.phone;

        let infoHTML = '';
        
        if (opening && closing) {
            infoHTML += `<div class="mb-1"><i class="fas fa-clock mr-2"></i> ${opening} - ${closing}</div>`;
        }
        
        if (address) {
            infoHTML += `<div class="mb-1"><i class="fas fa-map-marker-alt mr-2"></i> ${address}</div>`;
        }
        
        if (phone) {
            infoHTML += `<div><i class="fas fa-phone mr-2"></i> ${phone}</div>`;
        }

        branchInfo.innerHTML = infoHTML || 'No additional information available';
    }
});
</script>
@endpush