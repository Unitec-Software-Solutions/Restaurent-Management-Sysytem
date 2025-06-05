@extends('layouts.app')

@section('title', 'Reservation Cancelled')

@section('content')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes checkmarkAnim {
        0% { stroke-dashoffset: 90; }
        100% { stroke-dashoffset: 0; }
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .fade-in { animation: fadeIn 0.8s ease-out forwards; }
    .checkmark-path {
        stroke-dasharray: 90;
        stroke-dashoffset: 90;
        animation: checkmarkAnim 0.8s ease-out forwards;
    }
    .pulse-border { animation: pulse 1.5s ease-in-out infinite; }
</style>

<div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-3xl w-full fade-in">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-500 ease-in-out transform hover:shadow-2xl">
            <div class="p-8 md:p-12">
                <div class="flex flex-col items-center text-center">
                    <!-- Animated cancellation icon -->
                    <div class="relative mb-8 pulse-border rounded-full p-5 border-4 border-red-100">
                        <svg class="w-24 h-24 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path class="checkmark-path" d="M6 6l12 12M6 18L18 6" />
                        </svg>
                    </div>
                    
                    <!-- Cancellation message -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4 tracking-tight">Reservation Cancelled Successfully</h1>
                    <p class="text-gray-600 text-lg mb-6 max-w-xl mx-auto">
                        We've successfully cancelled your reservation. Any pre-authorization holds on your payment method
                        should be released within 3-5 business days.
                    </p>

                    
                    <!-- Call-to-action buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-4 w-full justify-center max-w-md">
                        <a href="{{ url('/') }}" class="bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 flex-1 text-center">
                            <i class="fas fa-home mr-2"></i>Return Home
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')
