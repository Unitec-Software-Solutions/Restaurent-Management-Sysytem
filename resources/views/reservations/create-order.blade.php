@extends('layouts.app')

@section('title', 'Create Order - ' . $reservation->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create Order</h1>
                    <p class="text-gray-600 mt-1">Add items to your order for Reservation #{{ $reservation->id }}</p>
                </div>
                <a href="{{ route('reservations.summary', $reservation) }}" 
                   class="text-gray-600 hover:text-gray-800 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Summary
                </a>
            </div>
        </div>

        <!-- Reservation Info Banner -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-check text-blue-600"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900">{{ $reservation->name }}</h3>
                        <p class="text-sm text-blue-700">
                            {{ $reservation->date->format('F d, Y') }} at {{ $reservation->start_time->format('h:i A') }} 
                            • {{ $reservation->number_of_people }} guests • {{ $reservation->branch->name }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Confirmed
                    </span>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Menu Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-utensils mr-3 text-orange-600"></i>
                            Available Menu Items
                        </h2>
                        <p class="text-gray-600 mt-1">Select items to add to your order</p>
                    </div>

                    <div class="p-6">
                        <!-- Search and Filter -->
                        <div class="mb-6 flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <input type="text" id="searchMenu" placeholder="Search menu items..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Categories</option>
                                    @foreach($menuItems->groupBy('menuCategory.name') as $categoryName => $items)
                                        <option value="{{ $categoryName }}">{{ $categoryName ?: 'Uncategorized' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Menu Items Grid -->
                        <div class="grid md:grid-cols-2 gap-4" id="menuItemsContainer">
                            @foreach($menuItems as $item)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors menu-item-card" 
                                 data-category="{{ $item->menuCategory->name ?? '' }}"
                                 data-name="{{ strtolower($item->name) }}"
                                 data-item-id="{{ $item->id }}">
                                
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">{{ $item->name }}</h3>
                                        @if($item->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($item->description, 80) }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right ml-4">
                                        <span class="text-lg font-bold text-blue-600">LKR {{ number_format($item->price, 2) }}</span>
                                    </div>
                                </div>

                                <!-- Item Type & Availability -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        @if($item->type === \App\Models\MenuItem::TYPE_KOT)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-fire mr-1"></i>KOT
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-box mr-1"></i>Stock
                                            </span>
                                        @endif
                                        
                                        @if($item->can_order)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Available
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Out of Stock
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($item->type === \App\Models\MenuItem::TYPE_BUY_SELL && $item->current_stock !== null)
                                        <span class="text-xs text-gray-500">Stock: {{ $item->current_stock }}</span>
                                    @endif
                                </div>

                                <!-- Add to Order Button -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                        <button type="button" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 quantity-btn" 
                                                data-action="decrease" data-item="{{ $item->id }}" {{ !$item->can_order ? 'disabled' : '' }}>
                                            <i class="fas fa-minus text-sm"></i>
                                        </button>
                                        <input type="number" class="quantity-input w-16 text-center border-0 focus:ring-0" 
                                               value="0" min="0" max="99" data-item="{{ $item->id }}" {{ !$item->can_order ? 'disabled' : '' }}>
                                        <button type="button" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 quantity-btn" 
                                                data-action="increase" data-item="{{ $item->id }}" {{ !$item->can_order ? 'disabled' : '' }}>
                                            <i class="fas fa-plus text-sm"></i>
                                        </button>
                                    </div>
                                    
                                    <button type="button" class="add-to-cart-btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:bg-gray-300 disabled:cursor-not-allowed" 
                                            data-item="{{ $item->id }}" 
                                            data-name="{{ $item->name }}" 
                                            data-price="{{ $item->price }}"
                                            {{ !$item->can_order ? 'disabled' : '' }}>
                                        <i class="fas fa-plus mr-1"></i>Add
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm sticky top-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-shopping-cart mr-3 text-green-600"></i>
                            Order Summary
                        </h2>
                    </div>

                    <form action="{{ route('reservations.store-order', $reservation) }}" method="POST" id="orderForm">
                        @csrf
                        
                        <div class="p-6">
                            <!-- Order Type Selection -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Order Type</label>
                                <select name="order_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    @foreach($orderTypes as $type)
                                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Steward Selection -->
                            @if($stewards->count() > 0)
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Steward (Optional)</label>
                                <select name="steward_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Steward</option>
                                    @foreach($stewards as $steward)
                                        <option value="{{ $steward->id }}">{{ $steward->first_name }} {{ $steward->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Order Items -->
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">Order Items</h3>
                                <div id="orderItems" class="space-y-3">
                                    <div id="emptyCart" class="text-center py-8 text-gray-500">
                                        <i class="fas fa-shopping-cart text-3xl mb-3"></i>
                                        <p>No items in cart</p>
                                        <p class="text-sm">Add items from the menu</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Instructions -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                                <textarea name="special_instructions" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                          placeholder="Any special requests or dietary requirements..."></textarea>
                            </div>

                            <!-- Order Total -->
                            <div class="border-t border-gray-200 pt-4 mb-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Subtotal:</span>
                                        <span id="subtotal">LKR 0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Tax (10%):</span>
                                        <span id="tax">LKR 0.00</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-semibold border-t border-gray-200 pt-2">
                                        <span>Total:</span>
                                        <span id="total">LKR 0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" id="submitOrder" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-semibold disabled:bg-gray-300 disabled:cursor-not-allowed" 
                                    disabled>
                                <i class="fas fa-check mr-2"></i>
                                Create Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
const taxRate = 0.1; // 10% tax

// Menu search and filter
document.getElementById('searchMenu').addEventListener('input', filterMenu);
document.getElementById('categoryFilter').addEventListener('change', filterMenu);

function filterMenu() {
    const searchTerm = document.getElementById('searchMenu').value.toLowerCase();
    const selectedCategory = document.getElementById('categoryFilter').value;
    const menuItems = document.querySelectorAll('.menu-item-card');

    menuItems.forEach(item => {
        const itemName = item.dataset.name;
        const itemCategory = item.dataset.category;
        
        const matchesSearch = itemName.includes(searchTerm);
        const matchesCategory = !selectedCategory || itemCategory === selectedCategory;
        
        if (matchesSearch && matchesCategory) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Quantity controls
document.querySelectorAll('.quantity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.dataset.action;
        const itemId = this.dataset.item;
        const input = document.querySelector(`input[data-item="${itemId}"]`);
        
        let value = parseInt(input.value) || 0;
        
        if (action === 'increase') {
            value = Math.min(value + 1, 99);
        } else if (action === 'decrease') {
            value = Math.max(value - 1, 0);
        }
        
        input.value = value;
        updateAddButton(itemId, value);
    });
});

// Quantity input changes
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        const itemId = this.dataset.item;
        const value = Math.max(0, Math.min(99, parseInt(this.value) || 0));
        this.value = value;
        updateAddButton(itemId, value);
    });
});

function updateAddButton(itemId, quantity) {
    const btn = document.querySelector(`button[data-item="${itemId}"]`);
    if (quantity > 0) {
        btn.innerHTML = `<i class="fas fa-plus mr-1"></i>Add (${quantity})`;
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
    } else {
        btn.innerHTML = '<i class="fas fa-plus mr-1"></i>Add';
        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
    }
}

// Add to cart
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const itemId = this.dataset.item;
        const itemName = this.dataset.name;
        const itemPrice = parseFloat(this.dataset.price);
        const quantity = parseInt(document.querySelector(`input[data-item="${itemId}"]`).value) || 1;
        
        if (quantity <= 0) return;
        
        addToCart(itemId, itemName, itemPrice, quantity);
        
        // Reset quantity input
        document.querySelector(`input[data-item="${itemId}"]`).value = 0;
        updateAddButton(itemId, 0);
    });
});

function addToCart(itemId, itemName, itemPrice, quantity) {
    const existingItem = cart.find(item => item.id === itemId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: itemId,
            name: itemName,
            price: itemPrice,
            quantity: quantity
        });
    }
    
    updateCartDisplay();
}

function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCartDisplay();
}

function updateCartQuantity(itemId, newQuantity) {
    const item = cart.find(item => item.id === itemId);
    if (item) {
        if (newQuantity <= 0) {
            removeFromCart(itemId);
        } else {
            item.quantity = newQuantity;
            updateCartDisplay();
        }
    }
}

function updateCartDisplay() {
    const orderItemsContainer = document.getElementById('orderItems');
    const emptyCart = document.getElementById('emptyCart');
    const submitButton = document.getElementById('submitOrder');
    
    if (cart.length === 0) {
        emptyCart.style.display = 'block';
        submitButton.disabled = true;
        updateTotals(0, 0, 0);
        return;
    }
    
    emptyCart.style.display = 'none';
    submitButton.disabled = false;
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        html += `
            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-900">${item.name}</h4>
                    <p class="text-sm text-gray-600">LKR ${item.price.toFixed(2)} × ${item.quantity}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="hidden" name="items[${item.id}][menu_item_id]" value="${item.id}">
                    <input type="hidden" name="items[${item.id}][quantity]" value="${item.quantity}">
                    <span class="font-semibold text-gray-900">LKR ${itemTotal.toFixed(2)}</span>
                    <button type="button" onclick="removeFromCart('${item.id}')" 
                            class="text-red-600 hover:text-red-800 ml-2">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    orderItemsContainer.innerHTML = html;
    
    const tax = subtotal * taxRate;
    const total = subtotal + tax;
    
    updateTotals(subtotal, tax, total);
}

function updateTotals(subtotal, tax, total) {
    document.getElementById('subtotal').textContent = `LKR ${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `LKR ${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `LKR ${total.toFixed(2)}`;
}

// Form submission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to your order.');
        return;
    }
    
    // Show loading state
    const btn = document.getElementById('submitOrder');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Order...';
});
</script>
@endsection
