@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <!-- Order Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Order Summary</h2>
                @if(isset($orderType))
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        {{ $orderType === 'reservation' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($orderType) }} Order
                    </span>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="font-semibold">Order #{{ $order->order_number ?? $order->id }}</p>
                    <p>Customer: {{ $order->customer_name }}</p>
                    <p>Phone: {{ $order->customer_phone }}</p>
                    @if($order->branch)
                        <p>Branch: {{ $order->branch->name }}</p>
                    @endif
                </div>
                <div>
                    @if(isset($reservation) && $reservation)
                        <p class="text-sm text-gray-600">Reservation Details:</p>
                        <p>Date: {{ \Carbon\Carbon::parse($reservation->date)->format('M d, Y') }}</p>
                        <p>Time: {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}</p>
                        <p>Party Size: {{ $reservation->number_of_people }}</p>
                    @endif
                    <p>Total Items: {{ $order->items->sum('quantity') }}</p>
                    <p class="text-xl font-bold">Total: LKR {{ number_format($order->total, 2) }}</p>
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
                        
                        <!-- Menu Item Type Indicators -->
                        @if($item->menuItem->type === \App\Models\MenuItem::TYPE_BUY_SELL)
                            <div class="stock-indicator" data-stock="{{ $item->menuItem->stock ?? 0 }}" data-item="{{ $item->menuItem->id }}">
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    Stock: {{ $item->menuItem->stock ?? 0 }}
                                </span>
                            </div>
                        @elseif($item->menuItem->type === \App\Models\MenuItem::TYPE_KOT)
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Available</span>
                        @endif
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
                
                <div class="action-buttons space-x-4 flex flex-col md:flex-row gap-2">
                    <!-- Submit Order: redirect to OrderConfirmationController -->
                    <form action="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.submit' : 'orders.store', $order) }}" method="POST" class="inline" id="order-form">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="hidden" name="payment_method" id="selected_payment_method" value="">
                        <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center" id="submit-order-btn">
                            <i class="fas fa-check mr-2"></i>Submit Order
                        </button>
                    </form>
                    
                    <!-- Update Order: redirect to OrderEditController -->
                    <a href="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.edit' : 'orders.edit', $order) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg flex items-center" id="update-order-btn" data-order-id="{{ $order->id }}">
                        <i class="fas fa-edit mr-2"></i>Update Order
                    </a>
                    
                    <!-- Add Another: redirect to OrderCreateController -->
                    @if(isset($reservation) && $reservation)
                        <a href="{{ route('orders.create', ['reservation' => $reservation->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center" id="add-another-order-btn" data-reservation-id="{{ $reservation->id }}">
                            <i class="fas fa-plus mr-2"></i>Add Another Order
                        </a>
                    @else
                        <a href="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.create' : 'orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center" id="add-another-order-btn">
                            <i class="fas fa-plus mr-2"></i>Add Another Order
                        </a>
                    @endif
                </div>
            @else
                <p class="text-green-500 font-semibold">Order Submitted</p>
            @endif
            <form action="{{ request()->routeIs('orders.takeaway.*') ? route('orders.takeaway.destroy', $order) : route('orders.destroy', $order) }}" method="POST">
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

@push('scripts')
<script src="{{ asset('js/order-system.js') }}"></script>
<script>
    // Initialize order system for summary page
    document.addEventListener('DOMContentLoaded', function() {
        // Enable payment method selection
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const selectedPaymentField = document.getElementById('selected_payment_method');
        const submitButton = document.getElementById('submit-order-btn');
        
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                selectedPaymentField.value = this.value;
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50');
            });
        });
    });
</script>
@endpush