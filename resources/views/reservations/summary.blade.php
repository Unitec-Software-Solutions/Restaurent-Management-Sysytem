@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gray-50">
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 w-full max-w-2xl confirmation-card">
        <!-- Close Button -->
        <div class="flex justify-end">
            <a href="/dashboard" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors duration-200">
                <i class="fas fa-times text-gray-500"></i>
            </a>
        </div>

        <!-- Success Icon -->
        <div class="flex justify-center my-6">
            <div class="w-20 h-20 rounded-full bg-green-500 flex items-center justify-center relative">
                <!-- Checkmark SVG for perfect centering -->
                <svg class="w-10 h-10 text-white absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>

        <!-- Title -->
        <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-800 mb-2">
            Reservation Confirmed!
        </h2>
        <p class="text-gray-500 text-center mb-8">
            Your reservation has been successfully updated
        </p>

        <!-- Reservation Details -->
        <div class="bg-blue-50 rounded-lg p-5 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Branch</p>
                    <p class="font-semibold text-gray-800">{{ $reservation->branch->name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Date & Time</p>
                    <p class="font-semibold text-gray-800">
                        {{ $reservation->date->format('l, F j, Y') }}<br>
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - 
                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Guests</p>
                    <p class="font-semibold text-gray-800">{{ $reservation->number_of_people }} person(s)</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Reservation ID</p>
                    <p class="font-semibold text-gray-800">#RES-{{ $reservation->id }}</p>
                </div>
            </div>
            
            <!-- Special Requests -->
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-500">Special Requests</p>
                <p class="font-semibold text-gray-800">
                    {{ $reservation->special_requests ?: 'No special requests' }}
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/dashboard" class="btn-primary text-white font-semibold py-3 px-6 rounded-lg flex-1 sm:flex-none text-center">
                <i class="fas fa-home mr-2"></i> Return to Dashboard
            </a>
            <button onclick="window.print()" class="bg-white border border-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg flex-1 sm:flex-none hover:bg-gray-50 transition-colors duration-200">
                <i class="fas fa-print mr-2"></i> Print Confirmation
            </button>
        </div>

       
        
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleCheck {
        0% { transform: scale(0); opacity: 0; }
        70% { transform: scale(1.15); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    .confirmation-card {
        animation: fadeIn 0.5s ease-out forwards;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .btn-primary {
        background-image: linear-gradient(to right, #3b82f6, #6366f1);
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
    }
    .bg-green-500 svg {
        animation: scaleCheck 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
    }
</style>
@endsection