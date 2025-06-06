@extends('layouts.admin')
@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8">
            <!-- Left: Order Form -->
            <div class="lg:flex-[7_7_0%]">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="px-6 py-5 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-black/90">
                                    <i class="fas fa-utensils mr-2"></i>Place Order
                                </h1>
                                <div class="mt-2 text-black/70 text-sm">
                                    <p class="flex items-center"><i class="fas fa-user mr-2"></i> <span class="font-medium">Customer:</span> {{ $reservation->name }}</p>
                                    <p class="flex items-center"><i class="fas fa-phone mr-2"></i> <span class="font-medium">Phone:</span> {{ $reservation->phone }}</p>
                                    <p class="flex items-center"><i class="far fa-calendar-alt mr-2"></i> <span class="font-medium">Date:</span> {{ $reservation->date }}</p>
                                    <p class="flex items-center"><i class="far fa-clock mr-2"></i> <span class="font-medium">Time:</span> {{ $reservation->start_time }} - {{ $reservation->end_time }}</p>
                                </div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-blue-100 text-sm">Reservation #</div>
                                <div class="text-white font-bold text-2xl">{{ $reservation->id }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route('admin.orders.reservations.store', ['reservation' => $reservation->id]) }}">
                            @csrf
                            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                            
                            <div class="mb-8">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-xl font-semibold text-gray-800">
                                        <i class="fas fa-list-ul mr-2 text-blue-500"></i>Menu Items
                                    </h2>
                                    <div class="relative">
                                        <input type="text" id="menu-search" placeholder="Search menu..." class="pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="menu-items-container">
                                    @foreach($menuItems as $item)
                                    <div class="item-card border border-gray-200 rounded-lg p-4 hover:bg-blue-50 transition-all">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <input type="checkbox" 
                                                           name="items[{{ $item->id }}][item_id]"
                                                           value="{{ $item->id }}"
                                                           id="item{{ $item->id }}"
                                                           class="mt-1 focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded item-check"
                                                           data-item-id="{{ $item->id }}">
                                                    <label for="item{{ $item->id }}" class="font-medium text-gray-700">{{ $item->name }}</label>
                                                </div>
                                                <div class="ml-6 text-sm text-gray-600">
                                                    Rs. {{ number_format($item->selling_price, 2) }}
                                                    @if($item->description)
                                                        <p class="mt-1 text-gray-500 text-xs">{{ $item->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <input type="number"
                                                       min="1"
                                                       value="1"
                                                       class="w-20 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 item-qty quantity-input"
                                                       data-item-id="{{ $item->id }}"
                                                       name="items[{{ $item->id }}][quantity]"
                                                       disabled>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8 border-t pt-6">
                                <div class="mb-6">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-sticky-note mr-2 text-blue-500"></i>Special Instructions
                                    </label>
                                    <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Any special requests or dietary restrictions..."></textarea>
                                </div>

                                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-md">
                                        <i class="fas fa-paper-plane mr-2"></i>Place Order
                                    </button>
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="w-full sm:w-auto text-center text-gray-600 hover:text-blue-600 font-medium py-3 px-6 rounded-lg border border-gray-300 hover:border-blue-300 transition-colors">
                                        <i class="fas fa-arrow-left mr-2"></i>Back to Reservation
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Cart Summary -->
            <div class="lg:flex-[1_1_0%] lg:min-w-[280px]">
                <div class="cart-summary bg-white shadow-lg rounded-xl p-6 sticky top-8 border border-blue-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-shopping-cart mr-2 text-blue-500"></i>Order Summary
                        </h2>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="item-count">0 items</span>
                    </div>
                    
                    <div id="cart-items" class="mb-4 max-h-64 overflow-y-auto">
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-basket text-gray-300 text-4xl mb-2"></i>
                            <p class="text-gray-500">No items added yet</p>
                        </div>
                    </div>
                    
                    <hr class="my-3 border-gray-200">
                    
                    <div class="space-y-2 mb-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span id="cart-subtotal">LKR 0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Tax (10%):</span>
                            <span id="cart-tax">LKR 0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Service Charge (5%):</span>
                            <span id="cart-service">LKR 0.00</span>
                        </div>
                    </div>
                    
                    <hr class="my-3 border-gray-200">
                    
                    <div class="flex justify-between font-bold text-lg text-gray-800">
                        <span>Total:</span>
                        <span id="cart-total">LKR 0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .item-card {
        transition: all 0.2s ease;
    }
    .cart-summary {
        transition: all 0.3s ease;
    }
    .cart-summary:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .quantity-input {
        -moz-appearance: textfield;
    }
    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
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
                qtyInput.classList.add('border-blue-300');
            } else {
                qtyInput.disabled = true;
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;
                qtyInput.classList.remove('border-blue-300');
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
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <div class="font-medium text-gray-700 truncate">${item.name}</div>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <span>Rs. ${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    <span class="mx-2">×</span>
                                    <span>${item.quantity}</span>
                                </div>
                            </div>
                            <div class="font-medium text-gray-800">
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
                        <i class="fas fa-shopping-basket text-gray-300 text-4xl mb-2"></i>
                        <p class="text-gray-500">No items added yet</p>
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