@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl py-8">
    <h2 class="text-2xl font-bold mb-6">Find Your Reservations</h2>
    <form method="GET" action="{{ route('customer.dashboard') }}" class="mb-8">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <input type="text" name="phone" value="{{ old('phone', $phone ?? '') }}" placeholder="Enter your phone number" class="border rounded px-4 py-2 w-full md:w-2/3" required>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Search</button>
        </div>
    </form>

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
