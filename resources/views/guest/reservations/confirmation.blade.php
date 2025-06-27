@extends('layouts.guest')

@section('title', 'Reservation Confirmed')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Reservation Confirmed!</h1>
            <p class="text-gray-600">Your table has been reserved. We look forward to serving you.</p>
        </div>

        <!-- Reservation Details Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Reservation #{{ $reservation->reservation_number ?? $reservation->id }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Confirmed {{ $reservation->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                            {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $reservation->status === 'seated' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $reservation->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $reservation->status === 'no_show' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Reservation Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reservation Details</h3>
                        <dl class="space-y-3">
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Date:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('l, F j, Y') }}</dd>
                            </div>
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    Time:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($reservation->reservation_time)->format('g:i A') }}</dd>
                            </div>
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-users mr-2"></i>
                                    Party:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ $reservation->party_size }} {{ $reservation->party_size == 1 ? 'person' : 'people' }}</dd>
                            </div>
                            @if($reservation->occasion)
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-star mr-2"></i>
                                    Occasion:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($reservation->occasion) }}</dd>
                            </div>
                            @endif
                            @if($reservation->seating_preference)
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-chair mr-2"></i>
                                    Seating:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $reservation->seating_preference)) }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Customer Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                        <dl class="space-y-3">
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-user mr-2"></i>
                                    Name:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ $reservation->customer_name }}</dd>
                            </div>
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-phone mr-2"></i>
                                    Phone:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ $reservation->customer_phone }}</dd>
                            </div>
                            @if($reservation->customer_email)
                            <div class="flex items-center">
                                <dt class="text-sm font-medium text-gray-500 w-24 flex items-center">
                                    <i class="fas fa-envelope mr-2"></i>
                                    Email:
                                </dt>
                                <dd class="text-sm text-gray-900">{{ $reservation->customer_email }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if($reservation->special_requests)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Special Requests</h3>
                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">{{ $reservation->special_requests }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Restaurant Information -->
        @if($reservation->branch)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Restaurant Information</h3>
            </div>
            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-medium text-gray-900 mb-2">{{ $reservation->branch->name }}</h4>
                        <p class="text-gray-600 mb-2">{{ $reservation->branch->address }}</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            @if($reservation->branch->phone)
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone mr-2 text-gray-400"></i>
                                <a href="tel:{{ $reservation->branch->phone }}" class="hover:text-indigo-600">
                                    {{ $reservation->branch->phone }}
                                </a>
                            </div>
                            @endif
                            
                            @if($reservation->branch->opening_time && $reservation->branch->closing_time)
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                {{ $reservation->branch->opening_time }} - {{ $reservation->branch->closing_time }}
                            </div>
                            @endif
                        </div>

                        @if($reservation->branch->description)
                        <p class="text-sm text-gray-600 mt-3">{{ $reservation->branch->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Important Information -->
        <div class="bg-blue-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-blue-900 mb-3">Important Information</h3>
            <div class="space-y-2 text-sm text-blue-800">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5 text-blue-600"></i>
                    <span>Please arrive on time. We'll hold your table for 15 minutes past your reservation time.</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-phone mr-2 mt-0.5 text-blue-600"></i>
                    <span>To cancel or modify your reservation, please call us at least 2 hours in advance.</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-users mr-2 mt-0.5 text-blue-600"></i>
                    <span>For parties of 8 or more, a 20% gratuity will be automatically added to your bill.</span>
                </div>
                @if($reservation->occasion)
                <div class="flex items-start">
                    <i class="fas fa-star mr-2 mt-0.5 text-blue-600"></i>
                    <span>We've noted your special occasion and will do our best to make it memorable!</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Calendar Reminder -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add to Calendar</h3>
            <p class="text-sm text-gray-600 mb-4">Don't forget your reservation! Add it to your calendar.</p>
            
            @php
                $datetime = \Carbon\Carbon::parse($reservation->reservation_date . ' ' . $reservation->reservation_time);
                $endDatetime = $datetime->copy()->addHours(2); // Assume 2-hour dining duration
                $googleCalendarUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . urlencode("Dinner Reservation at " . $reservation->branch->name) . "&dates=" . $datetime->format('Ymd\THis\Z') . "/" . $endDatetime->format('Ymd\THis\Z') . "&details=" . urlencode("Reservation for " . $reservation->party_size . " people at " . $reservation->branch->name . ". Phone: " . $reservation->customer_phone) . "&location=" . urlencode($reservation->branch->address);
            @endphp
            
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ $googleCalendarUrl }}" target="_blank" 
                   class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fab fa-google mr-2"></i>
                    Google Calendar
                </a>
                <a href="data:text/calendar;charset=utf-8,BEGIN:VCALENDAR%0AVERSION:2.0%0ABEGIN:VEVENT%0ADTSTART:{{ $datetime->format('Ymd\THis\Z') }}%0ADTEND:{{ $endDatetime->format('Ymd\THis\Z') }}%0ASUMMARY:Dinner Reservation at {{ $reservation->branch->name }}%0ADESCRIPTION:Reservation for {{ $reservation->party_size }} people%0ALOCATION:{{ $reservation->branch->address }}%0AEND:VEVENT%0AEND:VCALENDAR" 
                   download="reservation.ics"
                   class="flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Download .ics
                </a>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @if($reservation->branch)
            <a href="tel:{{ $reservation->branch->phone }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-phone mr-2"></i>
                Call Restaurant
            </a>
            @endif
            <a href="{{ route('guest.menu.view', ['branchId' => $reservation->branch_id]) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-utensils mr-2"></i>
                Pre-Order Food
            </a>
            <a href="{{ route('guest.menu.branch-selection') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>

        <!-- Email Confirmation Notice -->
        @if($reservation->customer_email)
        <div class="text-center mt-8 p-4 bg-green-50 rounded-lg">
            <div class="flex items-center justify-center mb-2">
                <i class="fas fa-envelope-check text-green-600 mr-2"></i>
                <span class="font-medium text-green-800">Confirmation Sent</span>
            </div>
            <p class="text-sm text-green-700">
                A confirmation email has been sent to {{ $reservation->customer_email }}
            </p>
        </div>
        @endif

        <!-- Feedback Section -->
        <div class="text-center mt-8 p-6 bg-gray-100 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">We Value Your Feedback</h3>
            <p class="text-sm text-gray-600 mb-4">
                After your dining experience, we'd love to hear about it!
            </p>
            <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                <i class="fas fa-star mr-2"></i>
                Leave a Review
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show success message
    document.addEventListener('DOMContentLoaded', function() {
        // You can add any additional JavaScript here for enhanced functionality
        
        // Example: Auto-scroll to important information after a delay
        setTimeout(function() {
            const importantInfo = document.querySelector('.bg-blue-50');
            if (importantInfo) {
                importantInfo.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 2000);
    });
</script>
@endpush
