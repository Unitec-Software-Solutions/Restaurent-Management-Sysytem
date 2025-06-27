@extends('layouts.guest')

@section('title', 'Your Cart')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Your Cart</h1>
                        <p class="text-gray-600 mt-1">Review your items before placing order</p>
                    </div>
                    
                    <a href="javascript:history.back()" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(!empty($cart) && count($cart) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cart as $index => $item)
                        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                            <div class="flex items-center space-x-4">
                                <!-- Item Image Placeholder -->
                                <div class="bg-gray-100 rounded-lg w-20 h-20 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-utensils text-gray-400 text-xl"></i>
                                </div>
                                
                                <!-- Item Details -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $item['name'] }}</h3>
                                    <p class="text-gray-600 text-sm">${{ number_format($item['price'], 2) }} each</p>
                                    
                                    @if(!empty($item['special_instructions']))
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 mt-2">
                                            <p class="text-xs text-yellow-800">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <strong>Special Instructions:</strong> {{ $item['special_instructions'] }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-3">
                                    <button onclick="updateQuantity({{ $item['menu_item_id'] }}, {{ $item['quantity'] - 1 }})"
                                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center"
                                            {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    
                                    <span class="font-semibold text-gray-900 min-w-[30px] text-center">{{ $item['quantity'] }}</span>
                                    
                                    <button onclick="updateQuantity({{ $item['menu_item_id'] }}, {{ $item['quantity'] + 1 }})"
                                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center"
                                            {{ $item['quantity'] >= 10 ? 'disabled' : '' }}>
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                                
                                <!-- Item Total -->
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-900">${{ number_format($item['total'], 2) }}</div>
                                    <button onclick="removeItem({{ $item['menu_item_id'] }})"
                                            class="text-red-600 hover:text-red-700 text-sm mt-1">
                                        <i class="fas fa-trash mr-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ $cartSummary['total_items'] ?? 0 }} items)</span>
                                <span>${{ number_format($cartSummary['subtotal'] ?? 0, 2) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-gray-600">
                                <span>Tax (10%)</span>
                                <span>${{ number_format(($cartSummary['subtotal'] ?? 0) * 0.10, 2) }}</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold text-gray-900">
                                    <span>Total</span>
                                    <span>${{ number_format(($cartSummary['subtotal'] ?? 0) * 1.10, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="space-y-3">
                            <button onclick="proceedToCheckout()" 
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium">
                                <i class="fas fa-credit-card mr-2"></i>
                                Proceed to Checkout
                            </button>
                            
                            <button onclick="clearCart()" 
                                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                                <i class="fas fa-trash mr-2"></i>
                                Clear Cart
                            </button>
                        </div>
                        
                        <!-- Estimated Time -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-blue-600 mr-2"></i>
                                <div>
                                    <div class="text-sm font-medium text-blue-900">Estimated Ready Time</div>
                                    <div class="text-sm text-blue-700">15-25 minutes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <div class="text-gray-400 text-6xl mb-6">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Your cart is empty</h3>
                <p class="text-gray-600 text-lg max-w-md mx-auto mb-8">
                    Looks like you haven't added any items to your cart yet. Browse our menu to get started!
                </p>
                
                <a href="{{ route('guest.menu.branch-selection') }}" 
                   class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-utensils mr-2"></i>
                    Browse Menu
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Order Details</h3>
                <button onclick="closeCheckoutModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="orderForm" action="{{ route('guest.order.create') }}" method="POST">
                @csrf
                
                <!-- Customer Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="customer_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                            <input type="tel" name="customer_phone" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                            <input type="email" name="customer_email"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                
                <!-- Order Type -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Order Type</h4>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" name="order_type" value="takeaway_on_demand" checked
                                   class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3">
                                <span class="font-medium">Takeaway - Ready ASAP</span>
                                <span class="text-gray-500 text-sm block">Ready in 15-25 minutes</span>
                            </span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="order_type" value="takeaway_scheduled"
                                   class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3">
                                <span class="font-medium">Takeaway - Schedule for Later</span>
                                <span class="text-gray-500 text-sm block">Choose your pickup time</span>
                            </span>
                        </label>
                    </div>
                    
                    <div id="pickupTimeSection" class="mt-4 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Time</label>
                        <input type="datetime-local" name="pickup_time"
                               min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
                
                <!-- Special Instructions -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions (Optional)</label>
                    <textarea name="special_instructions" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Any special requests or dietary requirements..."></textarea>
                </div>
                
                <!-- Payment Method -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Method</h4>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="cash_on_pickup" checked
                                   class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 font-medium">Cash on Pickup</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="online_payment"
                                   class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 font-medium">Pay Online</span>
                        </label>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeCheckoutModal()" 
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                        Place Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateQuantity(itemId, newQuantity) {
    if (newQuantity < 1) {
        removeItem(itemId);
        return;
    }
    
    fetch('{{ route("guest.cart.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            menu_item_id: itemId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update cart');
    });
}

function removeItem(itemId) {
    if (confirm('Remove this item from your cart?')) {
        fetch('{{ route("guest.cart.remove", ["itemId" => "ITEM_ID"]) }}'.replace('ITEM_ID', itemId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to remove item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove item');
        });
    }
}

function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        fetch('{{ route("guest.cart.clear") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to clear cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to clear cart');
        });
    }
}

function proceedToCheckout() {
    document.getElementById('checkoutModal').classList.remove('hidden');
}

function closeCheckoutModal() {
    document.getElementById('checkoutModal').classList.add('hidden');
}

// Handle order type change
document.querySelectorAll('input[name="order_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const pickupTimeSection = document.getElementById('pickupTimeSection');
        if (this.value === 'takeaway_scheduled') {
            pickupTimeSection.classList.remove('hidden');
            pickupTimeSection.querySelector('input').required = true;
        } else {
            pickupTimeSection.classList.add('hidden');
            pickupTimeSection.querySelector('input').required = false;
        }
    });
});

// Close modal when clicking outside
document.getElementById('checkoutModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCheckoutModal();
    }
});
</script>
@endpush
