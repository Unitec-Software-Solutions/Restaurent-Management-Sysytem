{{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto flex flex-row gap-8">
        <!-- Left: Order Form (7/8) -->
        <div class="flex-[7_7_0%]">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h1 class="text-2xl font-bold text-gray-800">
                        @if($reservation)
                        Place Order for Reservation #{{ $reservation->id }}
                        @else
                        Create New Order
                        @endif
                    </h1>
                    @if($reservation)
                    <div class="mt-2 text-gray-600">
                        <p>
                            <span class="font-medium">Customer:</span>
                            {{ optional($reservation?->customer)->name ?? $reservation->name ?? 'Not Provided' }}
                        </p>
                        <p>
                            <span class="font-medium">Table:</span>
                            {{ optional($reservation?->table)->name ?? $reservation->table_number ?? 'Not Provided' }}
                            ({{ optional($reservation?->table)->capacity ?? 'N/A' }} people)
                        </p>
                        <p><span class="font-medium">Time:</span> {{ $reservation?->start_time ?? '' }} - {{ $reservation?->end_time ?? '' }}</p>
                    </div>
                    @endif
                </div>
                <div class="p-6">
                    @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Stock Alert Area -->
                    <div id="stock-alerts" class="hidden mb-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Stock Alerts</h3>
                                    <div id="stock-alerts-content" class="mt-2 text-sm text-yellow-700">
                                        <!-- Stock alerts will be inserted here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @unless($reservation)
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
                        <p>This order is not associated with any reservation.</p>
                    </div>
                    @endunless

                    <form method="POST" action="{{ route('orders.store') }}" id="order-form">
                        @csrf
                        @if($reservation)
                        <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                        <input type="hidden" name="customer_id" value="{{ $reservation->customer_id }}">
                        <input type="hidden" name="table_id" value="{{ $reservation->table_id }}">
                        @endif

                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Menu Items</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($menuItems as $item)
                                <div class="menu-item-card border rounded-lg p-4 hover:bg-gray-50 transition-colors" data-item-id="{{ $item->id }}">
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
                                                @if($item->item_type === 'KOT')
                                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">KOT Available</span>
                                                @endif
                                            </div>
                                            <div class="ml-6 text-sm text-gray-600">
                                                Rs. {{ number_format($item->selling_price, 2) }}
                                                @if($item->description)
                                                <p class="mt-1 text-gray-500 text-xs">{{ $item->description }}</p>
                                                @endif
                                                <div class="stock-indicator mt-1">
                                                    @if($item->item_type === 'KOT')
                                                        <span class="text-green-600 text-xs font-medium bg-green-50 px-2 py-1 rounded">✓ Always Available</span>
                                                    @elseif($item->item_type === 'Buy & Sell')
                                                        @if($item->current_stock > 0)
                                                            <span class="text-green-600 text-xs font-medium">In Stock ({{ $item->current_stock }})</span>
                                                        @else
                                                            <span class="text-red-600 text-xs font-medium">Out of Stock</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400 text-xs">Checking stock...</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center border border-gray-300 rounded overflow-hidden w-[110px]">
                                            <button type="button"
                                                    class="qty-decrease w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xl flex items-center justify-center"
                                                    data-item-id="{{ $item->id }}"
                                                    disabled>-</button>
                                            <input type="number"
                                                   min="1"
                                                   value="1"
                                                   class="item-qty w-12 text-center border-x border-gray-300 text-sm focus:outline-none"
                                                   data-item-id="{{ $item->id }}"
                                                   disabled>
                                            <button type="button"
                                                    class="qty-increase w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xl flex items-center justify-center"
                                                    data-item-id="{{ $item->id }}"
                                                    disabled>+</button>
                                        </div>

                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>

                            <div class="flex justify-between items-center">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Place Order
                                </button>
                                <a href="{{ $reservation ? route('reservations.show', $reservation->id) : url()->previous() }}"
                                    class="text-gray-600 hover:text-gray-800 font-medium">
                                    {{ $reservation ? 'Back to Reservation' : 'Cancel' }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Right: Cart Summary (1/8) -->
        <div class="flex-[1_1_0%] min-w-[180px]">
            <div class="bg-white shadow-md rounded-lg p-6 sticky top-8">
                <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                <div id="cart-items">
                    <p class="text-gray-500">No items added yet</p>
                </div>
                <hr class="my-3">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal">LKR 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax (10%):</span>
                    <span id="cart-tax">LKR 0.00</span>
                </div>
                <hr class="my-3">
                <div class="flex justify-between font-bold">
                    <span>Total:</span>
                    <span id="cart-total">LKR 0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing guest order creation page...');
        
        // Initialize quantity controls
        initializeQuantityControls();
        
        // Initialize item selection
        initializeItemSelection();
        
        // Initialize form submission
        initializeFormSubmission();
        
        // Initialize cart update functionality
        initializeCartUpdate();
        
        // Initialize stock checking
        initializeStockChecking();
    });

    /**
     * Initialize quantity controls for guest order creation
     */
    function initializeQuantityControls() {
        console.log('🔢 Initializing guest quantity controls...');
        
        // Handle quantity increase buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.qty-increase')) {
                e.preventDefault();
                const button = e.target.closest('.qty-increase');
                const itemId = button.getAttribute('data-item-id');
                const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
                
                if (qtyInput && !qtyInput.disabled && !button.disabled) {
                    const currentValue = parseInt(qtyInput.value) || 1;
                    const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
                    
                    if (currentValue < maxValue) {
                        qtyInput.value = currentValue + 1;
                        updateButtonStates(itemId, qtyInput.value);
                        updateCart();
                    }
                }
            }
        });
        
        // Handle quantity decrease buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.qty-decrease')) {
                e.preventDefault();
                const button = e.target.closest('.qty-decrease');
                const itemId = button.getAttribute('data-item-id');
                const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
                
                if (qtyInput && !qtyInput.disabled && !button.disabled) {
                    const currentValue = parseInt(qtyInput.value) || 1;
                    
                    if (currentValue > 1) {
                        qtyInput.value = currentValue - 1;
                        updateButtonStates(itemId, qtyInput.value);
                        updateCart();
                    }
                }
            }
        });
        
        // Handle direct quantity input changes
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('item-qty')) {
                const qtyInput = e.target;
                const itemId = qtyInput.getAttribute('data-item-id');
                let value = parseInt(qtyInput.value) || 1;
                const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
                
                // Validate and constrain value
                if (value < 1) {
                    value = 1;
                    qtyInput.value = value;
                } else if (value > maxValue) {
                    value = maxValue;
                    qtyInput.value = value;
                }
                
                updateButtonStates(itemId, value);
                updateCart();
            }
        });
    }

    /**
     * Initialize item selection functionality
     */
    function initializeItemSelection() {
        console.log('☑️ Initializing guest item selection...');
        
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('item-check')) {
                const checkbox = e.target;
                const itemId = checkbox.getAttribute('data-item-id');
                const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
                const increaseBtn = document.querySelector(`.qty-increase[data-item-id="${itemId}"]`);
                const decreaseBtn = document.querySelector(`.qty-decrease[data-item-id="${itemId}"]`);
                
                if (checkbox.checked) {
                    // Enable quantity controls
                    if (qtyInput) {
                        qtyInput.disabled = false;
                        qtyInput.name = `items[${itemId}][quantity]`;
                        if (!qtyInput.value || qtyInput.value === '0') {
                            qtyInput.value = 1;
                        }
                        updateButtonStates(itemId, qtyInput.value);
                    }
                } else {
                    // Disable quantity controls
                    if (qtyInput) {
                        qtyInput.disabled = true;
                        qtyInput.removeAttribute('name');
                    }
                    if (increaseBtn) increaseBtn.disabled = true;
                    if (decreaseBtn) decreaseBtn.disabled = true;
                }
                
                updateCart();
            }
        });
    }

    /**
     * Update button states based on quantity value
     */
    function updateButtonStates(itemId, value) {
        const decreaseBtn = document.querySelector(`.qty-decrease[data-item-id="${itemId}"]`);
        const increaseBtn = document.querySelector(`.qty-increase[data-item-id="${itemId}"]`);
        const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
        
        const maxValue = parseInt(qtyInput?.getAttribute('max')) || 99;
        
        if (decreaseBtn) {
            decreaseBtn.disabled = value <= 1 || qtyInput?.disabled;
        }
        if (increaseBtn) {
            increaseBtn.disabled = value >= maxValue || qtyInput?.disabled;
        }
    }

    /**
     * Initialize form submission handling
     */
    function initializeFormSubmission() {
        const form = document.getElementById('order-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Check if at least one item is selected
                const checkedItems = document.querySelectorAll('.item-check:checked');
                if (checkedItems.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one item to order.');
                    return false;
                }
                
                // Disable unchecked checkboxes to prevent submission
                document.querySelectorAll('.item-check').forEach(function(checkbox) {
                    if (!checkbox.checked) {
                        checkbox.disabled = true;
                    }
                });
                
                return true;
            });
        }
    }

    /**
     * Initialize cart update functionality
     */
    function initializeCartUpdate() {
        // Define updateCart function globally
        window.updateCart = function() {
            const items = [];
            document.querySelectorAll('.item-check:checked').forEach(function(checkbox) {
                const itemId = checkbox.getAttribute('data-item-id');
                const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
                if (qtyInput) {
                    items.push({
                        item_id: itemId,
                        quantity: qtyInput.value
                    });
                }
            });

            // Update cart display
            updateCartDisplay(items);
        }
    }

    /**
     * Update cart display
     */
    function updateCartDisplay(items) {
        let subtotal = 0;
        let cartHtml = '';
        
        if (items.length > 0) {
            items.forEach(function(item) {
                const checkbox = document.querySelector(`.item-check[data-item-id="${item.item_id}"]`);
                const menuCard = checkbox?.closest('.menu-item-card');
                if (menuCard) {
                    const itemName = menuCard.querySelector('label').textContent.trim();
                    const priceText = menuCard.querySelector('.text-sm').textContent;
                    const priceMatch = priceText.match(/[\d,]+\.?\d*/);
                    const itemPrice = priceMatch ? parseFloat(priceMatch[0].replace(',', '')) : 0;
                    const itemTotal = itemPrice * parseInt(item.quantity);
                    
                    subtotal += itemTotal;
                    
                    cartHtml += `
                        <div class="flex justify-between mb-2">
                            <span>${itemName} <span class="text-xs text-gray-400">x${item.quantity}</span></span>
                            <span>LKR ${itemTotal.toFixed(2)}</span>
                        </div>
                    `;
                }
            });
        } else {
            cartHtml = '<div class="text-center py-4 text-gray-500">No items selected</div>';
        }
        
        // Update cart items display
        const cartItems = document.getElementById('cart-items');
        if (cartItems) {
            cartItems.innerHTML = cartHtml;
        }
        
        // Update totals
        const tax = subtotal * 0.1; // 10% tax
        const total = subtotal + tax;
        
        const subtotalElement = document.getElementById('cart-subtotal');
        const taxElement = document.getElementById('cart-tax');
        const totalElement = document.getElementById('cart-total');
        
        if (subtotalElement) subtotalElement.textContent = `LKR ${subtotal.toFixed(2)}`;
        if (taxElement) taxElement.textContent = `LKR ${tax.toFixed(2)}`;
        if (totalElement) totalElement.textContent = `LKR ${total.toFixed(2)}`;
    }

    /**
     * Initialize stock checking functionality
     */
    function initializeStockChecking() {
        // Stock checking functionality would go here
        // For now, just do initial cart update
        setTimeout(() => {
            updateCart();
        }, 100);
    }

    console.log('✅ Guest Order Creation JavaScript initialized successfully');
</script>
@endsection