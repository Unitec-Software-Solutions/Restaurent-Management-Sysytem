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
                <!-- Payment Options Section -->
                <div class="w-full mb-6">
                    <h3 class="text-lg font-semibold mb-3">Payment Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer payment-option" data-method="cash">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                <div>
                                    <p class="font-medium">Cash Payment</p>
                                    <p class="text-sm text-gray-500">Pay with cash</p>
                                </div>
                            </div>
                        </div>
                        <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer payment-option" data-method="card">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-credit-card text-blue-600"></i>
                                <div>
                                    <p class="font-medium">Card Payment</p>
                                    <p class="text-sm text-gray-500">Credit/Debit card</p>
                                </div>
                            </div>
                        </div>
                        <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer payment-option" data-method="mobile">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-mobile-alt text-purple-600"></i>
                                <div>
                                    <p class="font-medium">Mobile Payment</p>
                                    <p class="text-sm text-gray-500">eZ Cash, mCash, etc.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-x-4 flex flex-col md:flex-row gap-2">
                    <!-- Update Order -->
                    <a href="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.edit' : 'orders.edit', $order) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update Order</a>
                    <!-- Submit Order -->
                    <form action="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.submit' : 'orders.submit', $order) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="payment_method" id="selected_payment_method" value="">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600" id="submit_order_btn" disabled>Submit Order</button>
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

<script>
// Payment option selection
document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        // Remove active class from all options
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('bg-blue-50', 'border-blue-500');
        });
        
        // Add active class to selected option
        this.classList.add('bg-blue-50', 'border-blue-500');
        
        // Set payment method value
        const method = this.dataset.method;
        document.getElementById('selected_payment_method').value = method;
        
        // Enable submit button
        document.getElementById('submit_order_btn').disabled = false;
        document.getElementById('submit_order_btn').classList.remove('opacity-50', 'cursor-not-allowed');
    });
});

// Initially disable submit button
document.getElementById('submit_order_btn').classList.add('opacity-50', 'cursor-not-allowed');
</script>
@endsection