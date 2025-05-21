@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    @if($reservationId && $orders->isNotEmpty())
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Reservation #{{ $reservationId }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="font-semibold">Customer:</p>
                    <p>{{ optional($orders->first()->reservation)->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold">Phone:</p>
                    <p>{{ optional($orders->first()->reservation)->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold">Scheduled Time:</p>
                    <p>
                        @if(optional($orders->first()->reservation)->scheduled_time)
                            {{ optional($orders->first()->reservation)->scheduled_time->format('M j, Y H:i') }}
                        @else
                            Not scheduled
                        @endif
                    </p>
                </div>
                <div>
                    <p class="font-semibold">Total Orders:</p>
                    <p>{{ $orders->count() }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @foreach($orders as $order)
        <div class="border-b last:border-b-0">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Order #{{ $order->id }}</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('orders.edit', $order->id) }}?reservation_id={{ $reservationId }}" 
                           class="text-blue-500 hover:text-blue-700">
                            Edit
                        </a>
                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <input type="hidden" name="reservation_id" value="{{ $reservationId }}">
                            <button type="submit" 
                                    class="text-red-500 hover:text-red-700"
                                    onclick="return confirm('Delete this order?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Items -->
                <table class="w-full mb-4">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-2">Item</th>
                            <th class="text-right p-2">Price</th>
                            <th class="text-right p-2">Qty</th>
                            <th class="text-right p-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                        <tr class="border-b">
                            <td class="p-2">
                                {{ $item->menuItem->name ?? '[Deleted Item]' }}
                                @if(!$item->menuItem)
                                    <span class="text-red-500 text-xs">(Removed)</span>
                                @endif
                            </td>
                            <td class="p-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="p-2 text-right">{{ $item->quantity }}</td>
                            <td class="p-2 text-right">{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Order Summary -->
                <div class="flex justify-end">
                    <div class="w-64">
                        <div class="flex justify-between mb-1">
                            <span>Subtotal:</span>
                            <span>{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span>Tax:</span>
                            <span>{{ number_format($order->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span>Service Charge:</span>
                            <span>{{ number_format($order->service_charge, 2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold border-t pt-2">
                            <span>Order Total:</span>
                            <span>{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Grand Total -->
        @if($orders->isNotEmpty())
        <div class="p-6 bg-gray-50">
            <div class="flex justify-end">
                <div class="w-64">
                    <div class="flex justify-between font-bold text-lg">
                        <span>Grand Total:</span>
                        <span>{{ number_format($grandTotals['total'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 flex justify-between">
        <a href="{{ route('orders.create', ['reservation_id' => $reservationId]) }}" 
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Add Another Order
        </a>
        
        @if($reservationId)
        <a href="{{ route('reservations.payment', ['reservation' => $reservationId]) }}" 
           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            Proceed to Payment
        </a>
        @endif
    </div>
</div>
@endsection