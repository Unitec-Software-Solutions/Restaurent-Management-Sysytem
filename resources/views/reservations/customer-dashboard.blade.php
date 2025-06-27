@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl py-8">
    {{-- Debug Info Card for Customer Dashboard --}}
    {{-- @if(config('app.debug'))
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-medium text-purple-800">🔍 Customer Dashboard Debug Info</h3>
                <a href="{{ route('customer.dashboard', array_merge(request()->all(), ['debug' => 1])) }}" 
                   class="text-xs text-purple-600 hover:text-purple-800">
                    Full Debug (@dd)
                </a>
            </div>
            <div class="text-xs text-purple-700 mt-2 grid grid-cols-2 gap-4">
                <div>
                    <p><strong>Phone:</strong> {{ $phone ?? 'NOT SET' }}</p>
                    <p><strong>Reservations Variable:</strong> {{ isset($reservations) ? 'Set (' . count($reservations) . ')' : 'NOT SET' }}</p>
                </div>
                <div>
                    <p><strong>DB Total Reservations:</strong> {{ \App\Models\Reservation::count() }}</p>
                    <p><strong>DB Total Orders:</strong> {{ \App\Models\Order::count() }}</p>
                    <p><strong>DB Total Branches:</strong> {{ \App\Models\Branch::count() }}</p>
                    <p><strong>DB Total Organizations:</strong> {{ \App\Models\Organization::count() }}</p> 
                </div>
            </div>
        </div>
    @endif --}}

    <h2 class="text-2xl font-bold mb-6">Find Your Reservations</h2>
    <form method="GET" action="{{ route('customer.dashboard') }}" class="mb-8">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <input type="text" name="phone" value="{{ old('phone', $phone ?? '') }}" placeholder="Enter your phone number" class="border rounded px-4 py-2 w-full md:w-2/3" required>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Search</button>
        </div>
    </form>

    <a href="{{ route('orders.takeaway.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Place Takeaway Order
    </a>

    @if(isset($phone) && $phone)
        <h3 class="text-xl font-semibold mb-4">Reservations for <span class="text-blue-700">{{ $phone }}</span></h3>
        @if($reservations && count($reservations))
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border">Date</th>
                            <th class="px-4 py-2 border">Time</th>
                            <th class="px-4 py-2 border">Branch</th>
                            <th class="px-4 py-2 border">People</th>
                            <th class="px-4 py-2 border">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservations as $reservation)
                            <tr>
                                <td class="px-4 py-2 border">{{ $reservation->date }}</td>
                                <td class="px-4 py-2 border">{{ $reservation->start_time }} - {{ $reservation->end_time }}</td>
                                <td class="px-4 py-2 border">{{ optional($reservation->branch)->name ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $reservation->number_of_people }}</td>
                                <td class="px-4 py-2 border capitalize">{{ $reservation->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No reservations found for this phone number.</p>
        @endif
    @endif
</div>
@endsection
