@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Header with Status -->
            <div class="bg-gray-50 px-6 py-4 border-b">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Reservation Details</h1>
                    <x-reservation-status :status="$reservation->status" />
                </div>
            </div>

            <!-- Reservation Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-700 mb-2">Reservation Information</h2>
                            <div class="space-y-2">
                                <p class="text-gray-600">
                                    <span class="font-medium">Date:</span>
                                    {{ $reservation->date->format('F j, Y') }}
                                </p>
                                <p class="text-gray-600">
                                    <span class="font-medium">Time:</span>
                                    {{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}
                                </p>
                                <p class="text-gray-600">
                                    <span class="font-medium">Number of People:</span>
                                    {{ $reservation->number_of_people }}
                                </p>
                                <p class="text-gray-600">
                                    <span class="font-medium">Branch:</span>
                                    {{ $reservation->branch->name }}
                                </p>
                            </div>
                        </div>

                        @if($reservation->comments)
                        <div>
                            <h2 class="text-lg font-semibold text-gray-700 mb-2">Special Requests</h2>
                            <p class="text-gray-600">{{ $reservation->comments }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-700 mb-2">Contact Information</h2>
                            <div class="space-y-2">
                                <p class="text-gray-600">
                                    <span class="font-medium">Name:</span>
                                    {{ $reservation->name }}
                                </p>
                                <p class="text-gray-600">
                                    <span class="font-medium">Phone:</span>
                                    {{ $reservation->phone }}
                                </p>
                                @if($reservation->email)
                                <p class="text-gray-600">
                                    <span class="font-medium">Email:</span>
                                    {{ $reservation->email }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Information -->
                        @if($reservation->payments->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-700 mb-2">Payment Information</h2>
                            <div class="space-y-2">
                                @foreach($reservation->payments as $payment)
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-gray-600">
                                        <span class="font-medium">Amount:</span>
                                        ${{ number_format($payment->amount, 2) }}
                                    </p>
                                    <p class="text-gray-600">
                                        <span class="font-medium">Method:</span>
                                        {{ ucfirst($payment->payment_method) }}
                                    </p>
                                    <p class="text-gray-600">
                                        <span class="font-medium">Date:</span>
                                        {{ $payment->created_at->format('F j, Y H:i') }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-between">
                    <a href="{{ route('home') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Home
                    </a>
                    
                    @if($reservation->status === 'pending')
                    <a href="{{ route('reservations.cancel', $reservation) }}" 
                       class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                       onclick="return confirm('Are you sure you want to cancel this reservation?')">
                        Cancel Reservation
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 