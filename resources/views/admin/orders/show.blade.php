@extends('layouts.admin')

@section('content')

{{-- Debug Info Card for Order Show --}}
{{-- @if(config('app.debug'))
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center">
            <h3 class="text-sm font-medium text-orange-800">🔍 Order Debug Info</h3>
            <a href="{{ route('admin.orders.show', $order->id ?? 0) }}?debug=1" 
               class="text-xs text-orange-600 hover:text-orange-800">
                Full Debug (@dd)
            </a>
        </div>
        <div class="text-xs text-orange-700 mt-2 grid grid-cols-4 gap-4">
            <div>
                <p><strong>Order Variable:</strong> {{ isset($order) ? 'Set' : 'NOT SET' }}</p>
                <p><strong>Order ID:</strong> {{ $order->id ?? 'N/A' }}</p>
            </div>
            <div>
                <p><strong>Customer:</strong> {{ $order->customer_name ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $order->customer_phone ?? 'N/A' }}</p>
            </div>
            <div>
                <p><strong>Items Count:</strong> {{ isset($order) ? $order->orderItems->count() : 'N/A' }}</p>
                <p><strong>Total Amount:</strong> Rs. {{ number_format($order->total_amount ?? 0, 2) }}</p>
            </div>
            <div>
                <p><strong>Status:</strong> {{ $order->status ?? 'N/A' }}</p>
                <p><strong>Branch:</strong> {{ $order->branch->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
@endif --}}

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Order #{{ $order->id }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('orders.edit', $order->id) }}" 
                   class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    Edit Order
                </a>
                <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" 
                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                            onclick="return confirm('Delete this order?')">
                        Delete Order
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Details -->
        <div class="mb-8">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="font-semibold">Customer:</p>
                    <p>{{ $order->customer_name }}</p>
                    <p>{{ $order->customer_phone }}</p>
                </div>
                <div>
                    <p class="font-semibold">Order Type:</p>
                    <p>{{ ucwords(str_replace('_', ' ', $order->order_type)) }}</p>
                    @if($order->reservation)
                        <p class="mt-2">
                            Reservation #{{ $order->reservation_id }}
                            @if($order->reservation->scheduled_time)
                                ({{ $order->reservation->scheduled_time->format('M j, Y H:i') }})
                            @else
                                (Time not scheduled)
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            <!-- Order Items Table -->
            <table class="w-full mb-6">
                <thead class="bg-gray-100">
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
                            {{ $item->menuItem->name ?? $item->inventoryItem->name ?? '[Deleted Item]' }}
                            @if(!$item->menuItem && !$item->inventoryItem)
                                <span class="text-red-500 text-xs">(Item removed from system)</span>
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
            <div class="ml-auto max-w-xs">
                <div class="flex justify-between mb-2">
                    <span>Subtotal:</span>
                    <span>{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Tax (10%):</span>
                    <span>{{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Service Charge (5%):</span>
                    <span>{{ number_format($order->service_charge, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold border-t pt-2">
                    <span>Total:</span>
                    <span>{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center border-t pt-4">
            <div class="flex gap-2">
                @if($order->reservation_id)
                    <a href="{{ route('orders.create', ['reservation_id' => $order->reservation_id]) }}"
                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Another Order to Reservation
                    </a>
                @else
                    <a href="{{ route('orders.create') }}"
                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Create New Order
                    </a>
                @endif
            </div>

            <div class="flex gap-2">
                @if($order->reservation_id)
                    <a href="{{ route('reservations.payment', ['reservation' => $order->reservation_id]) }}"
                       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Proceed to Payment
                    </a>
                @else
                    <a href="{{ route('orders.payment', $order->id) }}"
                       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Process Payment
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection