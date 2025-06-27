@extends('layouts.guest')

@section('title', 'Make a Reservation')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Make a Reservation</h1>
            <p class="text-gray-600">Reserve your table for a memorable dining experience</p>
        </div>

        <!-- Reservation Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Reservation Details</h2>
            </div>

            <form method="POST" action="{{ route('guest.reservations.store') }}" class="p-6 space-y-6">
                @csrf
                
                <!-- Error Messages -->
                @if ($errors->any())
                <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
                    <h3 class="font-medium mb-2">Please correct the following errors:</h3>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Branch Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Restaurant Location <span class="text-red-500">*</span>
                        </label>
                        <select id="branch_id" name="branch_id" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select a location</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} - {{ $branch->address }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Party Size -->
                    <div>
                        <label for="party_size" class="block text-sm font-medium text-gray-700 mb-2">
                            Party Size <span class="text-red-500">*</span>
                        </label>
                        <select id="party_size" name="party_size" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select party size</option>
                            @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('party_size') == $i ? 'selected' : '' }}>
                                {{ $i }} {{ $i == 1 ? 'person' : 'people' }}
                            </option>
                            @endfor
                            <option value="13+" {{ old('party_size') == '13+' ? 'selected' : '' }}>13+ people (call for availability)</option>
                        </select>
                    </div>
                </div>

                <!-- Date and Time -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Reservation Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="reservation_date" 
                               name="reservation_date" 
                               value="{{ old('reservation_date') }}"
                               min="{{ date('Y-m-d') }}"
                               max="{{ date('Y-m-d', strtotime('+30 days')) }}"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="reservation_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Preferred Time <span class="text-red-500">*</span>
                        </label>
                        <select id="reservation_time" name="reservation_time" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select time</option>
                            @for($hour = 11; $hour <= 22; $hour++)
                                @for($minute = 0; $minute < 60; $minute += 30)
                                    @php
                                        $time = sprintf('%02d:%02d', $hour, $minute);
                                        $display = date('g:i A', strtotime($time));
                                    @endphp
                                    <option value="{{ $time }}" {{ old('reservation_time') == $time ? 'selected' : '' }}>
                                        {{ $display }}
                                    </option>
                                @endfor
                            @endfor
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Reservations available between 11:00 AM - 10:30 PM
                        </p>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="customer_name" 
                                   name="customer_name" 
                                   value="{{ old('customer_name') }}"
                                   placeholder="Enter your full name"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="customer_phone" 
                                   name="customer_phone" 
                                   value="{{ old('customer_phone') }}"
                                   placeholder="Enter your phone number"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address (Optional)
                        </label>
                        <input type="email" 
                               id="customer_email" 
                               name="customer_email" 
                               value="{{ old('customer_email') }}"
                               placeholder="Enter your email address"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">
                            We'll send you a confirmation email if provided
                        </p>
                    </div>
                </div>

                <!-- Special Requests -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Special Requests</h3>
                    
                    <!-- Occasion -->
                    <div class="mb-4">
                        <label for="occasion" class="block text-sm font-medium text-gray-700 mb-2">
                            Special Occasion (Optional)
                        </label>
                        <select id="occasion" name="occasion" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select an occasion</option>
                            <option value="birthday" {{ old('occasion') == 'birthday' ? 'selected' : '' }}>Birthday</option>
                            <option value="anniversary" {{ old('occasion') == 'anniversary' ? 'selected' : '' }}>Anniversary</option>
                            <option value="date" {{ old('occasion') == 'date' ? 'selected' : '' }}>Date Night</option>
                            <option value="business" {{ old('occasion') == 'business' ? 'selected' : '' }}>Business Meeting</option>
                            <option value="celebration" {{ old('occasion') == 'celebration' ? 'selected' : '' }}>Celebration</option>
                            <option value="other" {{ old('occasion') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Seating Preference -->
                    <div class="mb-4">
                        <label for="seating_preference" class="block text-sm font-medium text-gray-700 mb-2">
                            Seating Preference (Optional)
                        </label>
                        <select id="seating_preference" name="seating_preference" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">No preference</option>
                            <option value="window" {{ old('seating_preference') == 'window' ? 'selected' : '' }}>Window seat</option>
                            <option value="booth" {{ old('seating_preference') == 'booth' ? 'selected' : '' }}>Booth</option>
                            <option value="quiet" {{ old('seating_preference') == 'quiet' ? 'selected' : '' }}>Quiet area</option>
                            <option value="bar" {{ old('seating_preference') == 'bar' ? 'selected' : '' }}>Bar seating</option>
                            <option value="outdoor" {{ old('seating_preference') == 'outdoor' ? 'selected' : '' }}>Outdoor/Patio</option>
                        </select>
                    </div>

                    <!-- Special Requests -->
                    <div>
                        <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Requests (Optional)
                        </label>
                        <textarea id="special_requests" 
                                  name="special_requests" 
                                  rows="3"
                                  placeholder="Any special requests, dietary restrictions, or notes for your reservation..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('special_requests') }}</textarea>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="terms_accepted" 
                               name="terms_accepted" 
                               value="1"
                               required
                               class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="terms_accepted" class="ml-3 text-sm text-gray-600">
                            I agree to the reservation terms and conditions <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 ml-7">
                        <p>• Reservations are held for 15 minutes past the reserved time</p>
                        <p>• Please call to cancel or modify your reservation at least 2 hours in advance</p>
                        <p>• No-show fees may apply for large parties</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <button type="submit" 
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Make Reservation
                    </button>
                    <a href="{{ route('guest.menu.branch-selection') }}" 
                       class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Menu
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-indigo-600"></i>
                </div>
                <h3 class="font-medium text-gray-900 mb-2">Quick Confirmation</h3>
                <p class="text-sm text-gray-600">You'll receive confirmation within 15 minutes</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-utensils text-green-600"></i>
                </div>
                <h3 class="font-medium text-gray-900 mb-2">Great Experience</h3>
                <p class="text-sm text-gray-600">Enjoy our carefully crafted dishes and ambiance</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone text-blue-600"></i>
                </div>
                <h3 class="font-medium text-gray-900 mb-2">Easy Changes</h3>
                <p class="text-sm text-gray-600">Call us anytime to modify your reservation</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set minimum date to today
    document.getElementById('reservation_date').min = new Date().toISOString().split('T')[0];
    
    // Format phone number input
    document.getElementById('customer_phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        e.target.value = value;
    });

    // Update available times based on selected date and branch
    document.getElementById('reservation_date').addEventListener('change', function() {
        const selectedDate = this.value;
        const today = new Date().toISOString().split('T')[0];
        const currentHour = new Date().getHours();
        
        if (selectedDate === today) {
            // Disable past times for today
            const timeSelect = document.getElementById('reservation_time');
            Array.from(timeSelect.options).forEach(option => {
                if (option.value) {
                    const optionHour = parseInt(option.value.split(':')[0]);
                    option.disabled = optionHour <= currentHour;
                }
            });
        } else {
            // Enable all times for future dates
            const timeSelect = document.getElementById('reservation_time');
            Array.from(timeSelect.options).forEach(option => {
                option.disabled = false;
            });
        }
    });

    // Auto-focus on first input
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('branch_id').focus();
    });
</script>
@endpush
