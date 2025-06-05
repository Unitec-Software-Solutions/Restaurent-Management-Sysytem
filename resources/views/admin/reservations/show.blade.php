@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Reservation Details</h1>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <span class="font-semibold">Customer Name:</span> {{ $reservation->name }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Phone:</span> {{ $reservation->phone }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Email:</span> {{ $reservation->email ?? '-' }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Date:</span> {{ $reservation->date }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Time:</span> {{ $reservation->start_time }} - {{ $reservation->end_time }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Number of People:</span> {{ $reservation->number_of_people }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Status:</span> {{ ucfirst($reservation->status) }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Branch:</span> {{ $reservation->branch->name ?? '-' }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Tables:</span>
                    @if($reservation->tables && $reservation->tables->count())
                        @foreach($reservation->tables as $table)
                            <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1 mb-1">Table {{ $table->number }}</span>
                        @endforeach
                    @else
                        <span class="text-gray-400">None</span>
                    @endif
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Comments:</span> {{ $reservation->comments ?? '-' }}
                </div>
                <a href="{{ route('admin.reservations.index') }}" class="inline-block mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Back to List</a>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">Associated Orders</h2>
            @if($reservation->orders->count() > 0)
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservation->orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td>LKR {{ number_format($order->total, 2) }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>
                                <a href="{{ route('admin.orders.reservations.edit', [
                                    'reservation' => $reservation, 
                                    'order' => $order
                                ]) }}" class="text-blue-500">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No orders associated with this reservation</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('admin.orders.reservations.create', $reservation) }}" 
                   class="bg-blue-500 text-white px-4 py-2 rounded">
                    + Create Order
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
