@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Reservation Summary</h1>
            </div>

            <div class="p-6">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <h5 class="font-bold">Reservation Submitted!</h5>
                    <p>Your reservation has been submitted and is pending confirmation. We will notify you once it's confirmed.</p>
                </div>
               
                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Reservation Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><span class="font-medium">Branch:</span> {{ $reservation->branch->name }}</p>
                                <p><span class="font-medium">Date:</span> {{ \Carbon\Carbon::parse($reservation->date)->format('F j, Y') }}</p>
                                <p><span class="font-medium">Time:</span> {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</p>
                            </div>
                            <div>
                                <p><span class="font-medium">Number of People:</span> {{ $reservation->number_of_people }}</p>
                                <p><span class="font-medium">Status:</span> <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">{{ ucfirst($reservation->status) }}</span></p>
                                <p><span class="font-medium">Reservation Fee:</span> ${{ number_format($reservation->reservation_fee, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Contact Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><span class="font-medium">Name:</span> {{ $reservation->name }}</p>
                                <p><span class="font-medium">Phone:</span> {{ $reservation->phone }}</p>
                                @if($reservation->email)
                                    <p><span class="font-medium">Email:</span> {{ $reservation->email }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($reservation->comments)
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 mb-3">Additional Comments</h2>
                            <p class="text-gray-600">{{ $reservation->comments }}</p>
                        </div>
                    @endif

                    <div class="flex justify-between items-center pt-6">
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

                    @if($reservation->status === 'pending')
                        <div class="mt-8 flex flex-col md:flex-row gap-4 justify-center items-center">
                            <form action="{{ route('orders.create') }}" method="GET">
                                <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">
                                    Place an Order
                                </button>
                            </form>
                            <form action="{{ route('reservations.payment', $reservation) }}" method="GET">
                                <button type="submit" class="bg-green-600 hover:bg-green-800 text-white font-bold py-2 px-4 rounded">
                                    Proceed to Payment
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection