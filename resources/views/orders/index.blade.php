@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">
                @if($reservationId)
                    Orders for Reservation #{{ $reservationId }}
                @else
                    All Orders
                @endif
            </h1>
            <a href="{{ route('orders.create', ['reservation_id' => $reservationId]) }}" 
               class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Add New Order
            </a>
        </div>

        @if($reservationId)
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Reservation Details:</h2>
            @php
                $reservation = $orders->first()->reservation ?? null;
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-gray-600">Customer Name:</p>
                    <p class="font-medium">{{ $reservation->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Scheduled Time:</p>
                    <p class="font-medium">
                        @if($reservation && $reservation->scheduled_time)
                            {{ $reservation->scheduled_time->format('M j, Y H:i') }}
                        @else
                            Not scheduled
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-600">Total Orders:</p>
                    <p class="font-medium">{{ $orders->count() }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Grand Total:</p>
                    <p class="font-medium text-green-600">
                        ₹{{ number_format($grandTotals['total'], 2) }}
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Order #</th>
                    <th class="px-4 py-2 text-left">Customer</th>
                    <th class="px-4 py-2 text-right">Items</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $order->id }}</td>
                    <td class="px-4 py-3">
                        {{ $order->customer_name }}
                        <p class="text-sm text-gray-600">
                            {{ $order->customer_phone }}
                        </p>
                    </td>
                    <td class="px-4 py-3 text-right">
                        {{ $order->orderItems->sum('quantity') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        ₹{{ number_format($order->total, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('orders.show', $order->id) }}" 
                               class="text-blue-500 hover:text-blue-700" 
                               title="View Order">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            
                            <a href="{{ route('orders.edit', $order->id) }}?reservation_id={{ $reservationId }}" 
                               class="text-yellow-500 hover:text-yellow-700" 
                               title="Edit Order">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>

                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="reservation_id" value="{{ $reservationId }}">
                                <button type="submit" 
                                        class="text-red-500 hover:text-red-700"
                                        onclick="return confirm('Delete this order permanently?')"
                                        title="Delete Order">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                        No orders found for this reservation
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="mt-6">
        {{ $orders->appends(['reservation_id' => $reservationId])->links() }}
    </div>
    @endif
</div>
@endsection