@extends('layouts.admin')
@section('content')
<div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8">
            <!-- Left: Order Form -->
            <div class="lg:flex-[7_7_0%]">
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
                    <!-- Gradient Header -->
                    <div class="px-6 py-5 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-black flex items-center">
                                    <i class="fas fa-utensils mr-3 bg-white/20 p-2 rounded-lg"></i>
                                    <span>Place Order</span>
                                </h1>
                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div class="flex items-center text-black-100">
                                        <i class="fas fa-user mr-2 w-5"></i>
                                        <span class="font-medium">Customer:</span>
                                        <span class="ml-1">{{ $reservation->name }}</span>
                                    </div>
                                    <div class="flex items-center text-black-100">
                                        <i class="fas fa-phone mr-2 w-5"></i>
                                        <span class="font-medium">Phone:</span>
                                        <span class="ml-1">{{ $reservation->phone }}</span>
                                    </div>
                                    <div class="flex items-center text-black-100">
                                        <i class="far fa-calendar-alt mr-2 w-5"></i>
                                        <span class="font-medium">Date:</span>
                                        <span class="ml-1">{{ $reservation->date }}</span>
                                    </div>
                                    <div class="flex items-center text-black-100">
                                        <i class="far fa-clock mr-2 w-5"></i>
                                        <span class="font-medium">Time:</span>
                                        <span class="ml-1">{{ $reservation->start_time }} - {{ $reservation->end_time }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/20 rounded-xl p-3 text-center flex flex-col justify-center items-center">
                                <div class="text-black-100 text-sm font-semibold">Reservation #</div>
                                <div class="text-white font-bold text-3xl">{{ $reservation->id }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route('admin.orders.reservations.store', ['reservation' => $reservation->id]) }}">
                            @csrf
                            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                            
                            <!-- Menu Search and Categories -->
                            <div class="mb-6">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-list-ul mr-2 text-blue-500 bg-blue-100 p-2 rounded-lg"></i>
                                        Menu Items
                                    </h2>
                                    <div class="flex gap-3">
                                        <div class="relative flex-1 min-w-[200px]">
                                            <input type="text" id="menu-search" placeholder="Search menu items..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm">
                                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Menu Items Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="menu-items-container">
                                    @foreach($menuItems as $item)
                                    <div class="item-card bg-white border border-gray-200 rounded-xl p-4 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                                        <div class="flex items-start">
                                            <!-- Item Image Placeholder -->
                                            <div class="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center mr-3">
                                                <i class="fas fa-utensils text-gray-400"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-start justify-between">
                                                    <div>
                                                        <div class="flex items-center space-x-2">
                                                            <input type="checkbox" 
                                                                   name="items[{{ $item->id }}][item_id]"
                                                                   value="{{ $item->id }}"
                                                                   id="item{{ $item->id }}"
                                                                   class="mt-1 focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded item-check"
                                                                   data-item-id="{{ $item->id }}">
                                                            <label for="item{{ $item->id }}" class="font-semibold text-gray-800">{{ $item->name }}</label>
                                                        </div>
                                                        <div class="ml-6 text-sm text-gray-600">
                                                            <span class="font-medium">Rs. {{ number_format($item->selling_price, 2) }}</span>
                                                            @if($item->description)
                                                                <p class="mt-1 text-gray-500 text-xs">{{ $item->description }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ml-6 mt-3 flex items-center">
                                                    <input type="number"
                                                           min="1"
                                                           value="1"
                                                           class="w-20 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 item-qty quantity-input"
                                                           data-item-id="{{ $item->id }}"
                                                           name="items[{{ $item->id }}][quantity]"
                                                           disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Special Instructions and Submit -->
                            <div class="mt-8 border-t pt-6">
                                <div class="mb-6">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                        <i class="fas fa-sticky-note mr-2 text-blue-500 bg-blue-100 p-2 rounded-lg"></i>
                                        Special Instructions
                                    </label>
                                    <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Any special requests, dietary restrictions, or preparation instructions..."></textarea>
                                </div>

                                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg flex items-center">
                                        <i class="fas fa-paper-plane mr-2"></i>Place Order
                                    </button>
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="w-full sm:w-auto text-center text-gray-600 hover:text-blue-600 font-medium py-3 px-6 rounded-xl border border-gray-300 hover:border-blue-300 transition-colors flex items-center justify-center">
                                        <i class="fas fa-arrow-left mr-2"></i>Back to Reservation
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Cart Summary -->
            <div class="lg:flex-[1_1_0%] lg:min-w-[300px]">
                <div class="cart-summary bg-white rounded-2xl p-6 sticky top-8 border border-blue-100 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-shopping-cart mr-2 text-blue-500 bg-blue-100 p-2 rounded-lg"></i>
                            Order Summary
                        </h2>
                    </div>
                    
                    <div id="cart-items" class="mb-4 max-h-[300px] overflow-y-auto pr-2">
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-basket text-gray-200 text-5xl mb-3"></i>
                            <p class="text-gray-400 font-medium">Your cart is empty</p>
                            <p class="text-gray-400 text-sm mt-1">Add items from the menu</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-gray-600">
                            <span class="flex items-center">
                                <i class="fas fa-receipt mr-2 text-gray-400"></i>
                                Subtotal
                            </span>
                            <span id="cart-subtotal">LKR 0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span class="flex items-center">
                                <i class="fas fa-percent mr-2 text-gray-400"></i>
                                Tax (10%)
                            </span>
                            <span id="cart-tax">LKR 0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span class="flex items-center">
                                <i class="fas fa-concierge-bell mr-2 text-gray-400"></i>
                                Service Charge (5%)
                            </span>
                            <span id="cart-service">LKR 0.00</span>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <div class="flex justify-between font-bold text-lg text-gray-800">
                            <span>Total:</span>
                            <span id="cart-total">LKR 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .item-card {
        transition: all 0.3s ease;
    }
    .item-card:hover {
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.15);
    }
    .cart-summary {
        transition: all 0.3s ease;
    }
    .quantity-input {
        -moz-appearance: textfield;
    }
    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #cart-items::-webkit-scrollbar {
        width: 6px;
    }
    #cart-items::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    #cart-items::-webkit-scrollbar-thumb {
        background: #c5d5f1;
        border-radius: 10px;
    }
    #cart-items::-webkit-scrollbar-thumb:hover {
        background: #93b6f1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable quantity fields and update cart on change
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            if (this.checked) {
                qtyInput.disabled = false;
                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');
                qtyInput.classList.add('border-blue-400', 'ring-1', 'ring-blue-200');
            } else {
                qtyInput.disabled = true;
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;
                qtyInput.classList.remove('border-blue-400', 'ring-1', 'ring-blue-200');
            }
            updateCart();
        });
    });
    
    document.querySelectorAll('.item-qty').forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value < 1) this.value = 1;
            updateCart();
        });
    });

    // Menu search functionality
    document.getElementById('menu-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.item-card').forEach(function(card) {
            const itemName = card.querySelector('label').textContent.toLowerCase();
            if (itemName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // AJAX cart update
    function updateCart() {
        const items = [];
        let itemCount = 0;
        
        document.querySelectorAll('.item-check:checked').forEach(function(checkbox) {
            const itemId = checkbox.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            items.push({
                item_id: itemId,
                quantity: qtyInput.value
            });
            itemCount += parseInt(qtyInput.value);
        });

        fetch("{{ route('admin.orders.update-cart') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ items: items })
        })
        .then(response => response.json())
        .then(cart => {
            // Update cart display
            const cartItemsEl = document.getElementById('cart-items');
            
            if (cart.items.length > 0) {
                let itemsHtml = '';
                cart.items.forEach(function(item) {
                    itemsHtml += `
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-700 truncate">${item.name}</div>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded mr-2">Rs. ${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    <span class="text-gray-400">×</span>
                                    <span class="ml-2 bg-gray-100 text-gray-700 px-2 py-0.5 rounded">${item.quantity}</span>
                                </div>
                            </div>
                            <div class="font-semibold text-gray-800">
                                Rs. ${item.total.toLocaleString('en-US', {minimumFractionDigits: 2})}
                            </div>
                        </div>`;
                });
                
                cartItemsEl.innerHTML = itemsHtml;
                
                document.getElementById('cart-subtotal').textContent = 'LKR ' + cart.subtotal.toFixed(2);
                document.getElementById('cart-tax').textContent = 'LKR ' + cart.tax.toFixed(2);
                document.getElementById('cart-service').textContent = 'LKR ' + cart.service.toFixed(2);
                document.getElementById('cart-total').textContent = 'LKR ' + cart.total.toFixed(2);
                
                // Update item count badge
                document.getElementById('item-count').textContent = itemCount + (itemCount === 1 ? ' item' : ' items');
                document.getElementById('item-count').classList.remove('bg-blue-100', 'text-blue-800');
                document.getElementById('item-count').classList.add('bg-blue-600', 'text-white');
            } else {
                cartItemsEl.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-shopping-basket text-gray-200 text-5xl mb-3"></i>
                        <p class="text-gray-400 font-medium">Your cart is empty</p>
                        <p class="text-gray-400 text-sm mt-1">Add items from the menu</p>
                    </div>`;
                
                document.getElementById('cart-subtotal').textContent = 'LKR 0.00';
                document.getElementById('cart-tax').textContent = 'LKR 0.00';
                document.getElementById('cart-service').textContent = 'LKR 0.00';
                document.getElementById('cart-total').textContent = 'LKR 0.00';
                
                // Reset item count badge
                document.getElementById('item-count').textContent = '0 items';
                document.getElementById('item-count').classList.remove('bg-blue-600', 'text-white');
                document.getElementById('item-count').classList.add('bg-blue-100', 'text-blue-800');
            }
        });
    }
});
</script>
@endsection