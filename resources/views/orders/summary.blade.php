@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <!-- Order Summary -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4">Order Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="font-semibold">Order ID: #{{ $order->id }}</p>
                    <p>Customer: {{ $order->customer_name }}</p>
                    <p>Phone: {{ $order->customer_phone }}</p>
                </div>
                <div>
                    <p>Total Items: {{ $order->items->sum('quantity') }}</p>
                    <p class="text-xl font-bold">Total: LKR  {{ number_format($order->total, 2) }}</p>
                </div>
            </div>
        </div>
        <!-- Order Items -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Order Items</h3>
            <div class="space-y-4">
                @foreach($order->items as $item)
                <div class="flex justify-between items-center border-b pb-2">
                    <div>
                        <p class="font-medium">{{ $item->menuItem->name }}</p>
                        <p class="text-sm">Qty: {{ $item->quantity }}</p>
                    </div>
                    <p>LKR  {{ number_format($item->total_price, 2) }}</p>
                </div>
                @endforeach
            </div>
        </div>
        <!-- Action Buttons -->
        <div class="flex flex-col md:flex-row md:justify-between items-center gap-4">
            @if($editable)
                <div class="space-x-4 flex flex-col md:flex-row gap-2">
                    <!-- Update Order -->
                    <a href="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.edit' : 'orders.edit', $order) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update Order</a>
                    <!-- Submit Order -->
                    <form action="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.submit' : 'orders.submit', $order) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Submit Order</button>
                    </form>
                    <!-- Add Another Order -->
                    <a href="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.create' : 'orders.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Another Order</a>
                </div>
            @else
                <p class="text-green-500 font-semibold">Order Submitted</p>
            @endif
            <form action="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.destroy' : 'orders.destroy', $order) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600" onclick="return confirm('Delete this order?')">Delete Order</button>
            </form>
        </div>
    </div>
</div>
@endsection