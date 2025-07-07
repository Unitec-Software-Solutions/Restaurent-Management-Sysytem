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
                                @if(isset($reservation) && $reservation)
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
                                @else
                                <div class="mt-3">
                                    <p class="text-gray-600">Creating a new order</p>
                                </div>
                                @endif
                            </div>
                            @if(isset($reservation) && $reservation)
                            <div class="bg-white/20 rounded-xl p-3 text-center flex flex-col justify-center items-center">
                                <div class="text-black-100 text-sm font-semibold">Reservation #</div>
                                <div class="text-white font-bold text-3xl">{{ $reservation->id }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ isset($reservation) && $reservation ? route('admin.orders.reservations.store', ['reservation' => $reservation->id]) : route('admin.orders.store') }}" id="orderForm">
                            @csrf
                            @if(isset($reservation) && $reservation)
                            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                            @else
                            <input type="hidden" name="order_type" value="{{ $orderType ?? 'dine_in_walk_in_demand' }}">
                            
                            <!-- Order Type and Customer Information (for non-reservation orders) -->
                            @if(!isset($reservation) || !$reservation)
                            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Only show Order Type Selection if not already a takeaway order -->
                                @if(!request()->routeIs('admin.orders.takeaway.*') && ($orderType ?? '') !== 'takeaway' && !request()->has('type'))
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-shopping-cart mr-2 text-blue-500"></i>
                                        Order Type
                                    </h3>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="order_type" value="dine_in_walk_in_demand" 
                                                {{ ($orderType ?? 'dine_in_walk_in_demand') === 'dine_in_walk_in_demand' ? 'checked' : '' }}
                                                class="mr-2 text-blue-600">
                                            <span class="text-gray-700">Dine In</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="order_type" value="takeaway_walk_in_demand" 
                                                {{ ($orderType ?? '') === 'takeaway_walk_in_demand' ? 'checked' : '' }}
                                                class="mr-2 text-blue-600">
                                            <span class="text-gray-700">Takeaway</span>
                                        </label>
                                    </div>
                                </div>
                                @elseif(($orderType ?? '') === 'takeaway_walk_in_demand' || request()->has('type') && request()->get('type') === 'takeaway')
                                <!-- For takeaway orders, show confirmation but don't allow changing -->
                                <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                                    <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                                        <i class="fas fa-shopping-bag mr-2 text-blue-600"></i>
                                        Takeaway Order
                                    </h3>
                                    <p class="text-blue-700">This order is set for takeaway</p>
                                    <input type="hidden" name="order_type" value="takeaway_walk_in_demand">
                                </div>
                                @else
                                <!-- Default to in-house if no specific type is set -->
                                <input type="hidden" name="order_type" value="{{ $orderType ?? 'in_house' }}">
                                @endif

                                <!-- Branch Selection (for super admin) -->
                                @if(auth('admin')->user()->is_super_admin)
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-building mr-2 text-blue-500"></i>
                                        Branch
                                    </h3>
                                    <select name="branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="">Select Branch...</option>
                                        @foreach($branches ?? [] as $branch)
                                            <option value="{{ $branch->id }}" 
                                                {{ (old('branch_id', $defaultData['branch_id'] ?? $defaultBranch ?? '') == $branch->id) ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <input type="hidden" name="branch_id" value="{{ $defaultBranch }}">
                                @endif
                            </div>

                            <!-- Customer Information (for takeaway orders) -->
                            <div id="customer-info" class="mb-6 bg-blue-50 rounded-xl p-4" style="display: {{ ($orderType ?? '') === 'takeaway' ? 'block' : 'none' }}">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-user mr-2 text-blue-500"></i>
                                    Customer Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                                        <input type="text" name="customer_name" 
                                            value="{{ old('customer_name', $defaultData['customer_name'] ?? '') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                            placeholder="Enter customer name">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                        <input type="tel" name="customer_phone" 
                                            value="{{ old('customer_phone', $defaultData['customer_phone'] ?? '') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                            placeholder="Enter phone number" required>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Time</label>
                                        <input type="datetime-local" name="order_time" 
                                            value="{{ old('order_time', $defaultData['order_time'] ?? now()->format('Y-m-d\TH:i')) }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endif
                            
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
                                    @php
                                        $existing = isset($order) ? $order->items->firstWhere('menu_item_id', $item->id) : null;
                                    @endphp
                                    <div class="bg-white rounded-lg border border-gray-200 hover:border-indigo-300 transition-colors duration-200 p-4 menu-item"
                                        data-item-id="{{ $item->id }}"
                                        data-item-type="{{ $item->type ?? 1 }}"
                                        data-stock="{{ isset($item->stock) ? $item->stock : 0 }}"
                                        data-is-available="{{ $item->is_available ? 'true' : 'false' }}">
                                        
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900">{{ $item->name }}</h3>
                                                <p class="text-sm text-gray-600 mt-1">{{ $item->description ?? '' }}</p>
                                                <p class="text-lg font-bold text-indigo-600 mt-2">LKR {{ number_format($item->price, 2) }}</p>
                                            </div>
                                            
                                            <!-- Menu Item Type Display Logic -->
                                            <div class="ml-3 text-right">
                                                @if(($item->type ?? 1) === App\Models\MenuItem::TYPE_BUY_SELL)
                                                    <div class="stock-indicator bg-gray-100 px-2 py-1 rounded text-xs font-medium" 
                                                         data-stock="{{ $item->stock ?? 0 }}">
                                                        <i class="fas fa-boxes mr-1"></i>
                                                        Stock: {{ $item->stock ?? 0 }}
                                                    </div>
                                                    @if(($item->stock ?? 0) < 1)
                                                        <div class="mt-1 text-xs text-red-600 font-medium">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            Out of Stock
                                                        </div>
                                                    @elseif(($item->stock ?? 0) < 5)
                                                        <div class="mt-1 text-xs text-orange-600 font-medium">
                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                            Low Stock
                                                        </div>
                                                    @endif
                                                @elseif(($item->type ?? 1) === App\Models\MenuItem::TYPE_KOT)
                                                    <span class="badge bg-success text-white px-2 py-1 rounded text-xs font-medium">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Available
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Add to Order Controls -->
                                        <div class="flex items-center justify-between mt-4">
                                            <div class="flex items-center">
                                                <input type="checkbox"
                                                    class="item-check add-to-order mr-3 w-4 h-4 text-indigo-600"
                                                    data-item-id="{{ $item->id }}"
                                                    data-stock="{{ $item->stock ?? 0 }}"
                                                    id="item_{{ $item->id }}"
                                                    value="{{ $item->id }}"
                                                    @if(($item->type ?? 1) === App\Models\MenuItem::TYPE_BUY_SELL && ($item->stock ?? 0) < 1) disabled @endif
                                                    @if($existing) checked @endif>
                                                <label for="item_{{ $item->id }}" class="text-sm font-medium text-gray-700">
                                                    Add to Order
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-center space-x-1">
                                                <button type="button"
                                                    class="qty-decrease w-8 h-8 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg flex items-center justify-center"
                                                    data-item-id="{{ $item->id }}"
                                                    @if(!$existing) disabled @endif>
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <input type="number"
                                                    min="1"
                                                    max="{{ ($item->type ?? 1) === App\Models\MenuItem::TYPE_BUY_SELL ? ($item->stock ?? 0) : 99 }}"
                                                    value="{{ $existing ? $existing->quantity : 1 }}"
                                                    class="item-qty w-12 text-center border border-gray-300 rounded text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                    data-item-id="{{ $item->id }}"
                                                    @if(!$existing) disabled @endif
                                                    @if($existing) name="items[{{ $item->id }}][quantity]" @endif>
                                                <button type="button"
                                                    class="qty-increase w-8 h-8 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg flex items-center justify-center"
                                                    data-item-id="{{ $item->id }}"
                                                    @if(!$existing) disabled @endif>
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>
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
                                    <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg flex items-center" id="submitOrderButton">
                                        <i class="fas fa-paper-plane mr-2"></i>Place Order
                                    </button>
                                    @if(isset($reservation) && $reservation)
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="w-full sm:w-auto text-center text-gray-600 hover:text-blue-600 font-medium py-3 px-6 rounded-xl border border-gray-300 hover:border-blue-300 transition-colors flex items-center justify-center">
                                        <i class="fas fa-arrow-left mr-2"></i>Back to Reservation
                                    </a>
                                    @else
                                    <a href="{{ route('admin.orders.index') }}" class="w-full sm:w-auto text-center text-gray-600 hover:text-blue-600 font-medium py-3 px-6 rounded-xl border border-gray-300 hover:border-blue-300 transition-colors flex items-center justify-center">
                                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                                    </a>
                                    @endif
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
        appearance: textfield;
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
    console.log('🚀 Admin Order Management System - Flow Implementation');
    
    // Initialize admin-specific defaults
    initializeAdminDefaults();
    
    // Initialize menu item display with stock validation
    initializeMenuItemDisplay();
    
    // Initialize order flow buttons
    initializeOrderFlowButtons();
    
    // Initialize branch filtering
    initializeBranchFiltering();
    
    // Initialize takeaway type selector for admin
    initializeTakeawayTypeSelector();
    
    // Initialize order type change handler
    initializeOrderTypeHandler();
});

/**
 * Initialize admin-specific default values from session
 */
function initializeAdminDefaults() {
    console.log('👨‍💼 Initializing Admin Defaults...');
    
    const sessionDefaults = @json($sessionDefaults ?? []);
    
    // Pre-fill form with admin defaults
    if (sessionDefaults.branch_id) {
        const branchField = document.getElementById('branch_id');
        if (branchField && !branchField.value) {
            branchField.value = sessionDefaults.branch_id;
            console.log('✅ Branch pre-filled:', sessionDefaults.branch_id);
        }
    }
    
    if (sessionDefaults.default_order_type) {
        const orderTypeField = document.getElementById('order_type');
        if (orderTypeField && !orderTypeField.value) {
            orderTypeField.value = sessionDefaults.default_order_type;
            console.log('✅ Order type pre-filled:', sessionDefaults.default_order_type);
        }
    }
    
    // Set takeaway type default to 'in_house' for admin
    const takeawayTypeField = document.getElementById('takeaway_type');
    if (takeawayTypeField && !takeawayTypeField.value) {
        takeawayTypeField.value = 'in_house';
        console.log('✅ Takeaway type set to default: in_house');
    }
}

/**
 * Initialize menu item display with proper stock levels and KOT badges
 */
function initializeMenuItemDisplay() {
    console.log('🍽️ Initializing Menu Item Display...');
    
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach((item, index) => {
        const itemId = item.dataset.itemId;
        const itemType = item.dataset.itemType;
        const stockLevel = parseInt(item.dataset.stockLevel) || 0;
        const isKot = item.dataset.isKot === 'true';
        
        // Add stock display for Buy & Sell items
        if (itemType === 'Buy & Sell') {
            addStockLevelDisplay(item, stockLevel);
        }
        
        // Add KOT badge for KOT items
        if (isKot || itemType === 'KOT') {
            addKotAvailabilityBadge(item);
        }
        
        // Add click handler for item selection
        item.addEventListener('click', function() {
            selectMenuItem(itemId, itemType, stockLevel);
        });
        
        console.log(`Menu Item ${index + 1}: ${itemType}, Stock: ${stockLevel}, KOT: ${isKot}`);
    });
}

/**
 * Add stock level display to Buy & Sell items
 */
function addStockLevelDisplay(itemElement, stockLevel) {
    const stockContainer = itemElement.querySelector('.stock-container');
    if (!stockContainer) return;
    
    const stockBadge = document.createElement('span');
    stockBadge.className = `stock-badge px-2 py-1 text-xs font-semibold rounded-full ${getStockStatusClass(stockLevel)}`;
    stockBadge.innerHTML = `<i class="fas fa-box mr-1"></i>Stock: ${stockLevel}`;
    
    stockContainer.appendChild(stockBadge);
    
    // Disable item if out of stock
    if (stockLevel <= 0) {
        itemElement.classList.add('opacity-50', 'cursor-not-allowed');
        itemElement.querySelector('.item-checkbox').disabled = true;
    }
}

/**
 * Add KOT availability badge to KOT items
 */
function addKotAvailabilityBadge(itemElement) {
    const badgeContainer = itemElement.querySelector('.badge-container');
    if (!badgeContainer) return;
    
    const kotBadge = document.createElement('span');
    kotBadge.className = 'kot-badge bg-green-100 text-green-800 px-2 py-1 text-xs font-semibold rounded-full';
    kotBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>KOT Available';
    
    badgeContainer.appendChild(kotBadge);
}

/**
 * Get stock status CSS class based on stock level
 */
function getStockStatusClass(stockLevel) {
    if (stockLevel <= 0) return 'bg-red-100 text-red-800';
    if (stockLevel <= 10) return 'bg-yellow-100 text-yellow-800';
    if (stockLevel <= 25) return 'bg-orange-100 text-orange-800';
    return 'bg-green-100 text-green-800';
}

/**
 * Initialize order flow buttons following the exact requested flow
 */
function initializeOrderFlowButtons() {
    console.log('🔄 Initializing Order Flow Buttons...');
    
    // Submit Order Button - Goes to summary page
    const submitButton = document.getElementById('submitOrderButton');
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('📋 Submit Order clicked - Going to summary page');
            
            if (validateOrderForm()) {
                // Create order and go to summary
                submitOrderToSummary();
            }
        });
    }
    
    // Update Order Button (for editing existing orders)
    const updateButton = document.getElementById('updateOrderButton');
    if (updateButton) {
        updateButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('✏️ Update Order clicked - Saving changes');
            
            if (validateOrderForm()) {
                updateExistingOrder();
            }
        });
    }
    
    // Add Another Order Button
    const addAnotherButton = document.getElementById('addAnotherOrderButton');
    if (addAnotherButton) {
        addAnotherButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('➕ Add Another Order clicked - Starting new order');
            
            // Save current order and redirect to new order creation
            saveAndCreateNewOrder();
        });
    }
}

/**
 * Submit order and redirect to summary page (exact flow requirement)
 */
function submitOrderToSummary() {
    const formData = new FormData(document.getElementById('orderForm'));
    
    // Add admin-specific data
    formData.append('created_by_admin', true);
    formData.append('admin_id', '{{ auth("admin")->id() }}');
    
    showLoadingState('Creating order...');
    
    fetch('{{ route("admin.orders.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            console.log('✅ Order created successfully');
            
            // Redirect to summary page with three options as requested
            if (data.reservation_id) {
                // Reservation-linked order - go to reservation + order summary
                window.location.href = `{{ route('admin.orders.reservations.summary', ['reservation' => ':reservation_id', 'order' => ':order_id']) }}`
                    .replace(':reservation_id', data.reservation_id)
                    .replace(':order_id', data.order_id);
            } else {
                // Takeaway order - go to order details by number
                window.location.href = "{{ url('admin/orders') }}/" + data.order_id;
            }
        } else {
            showErrorMessage('Failed to create order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('❌ Order creation failed:', error);
        showErrorMessage('Failed to create order. Please try again.');
    });
}

/**
 * Update existing order and return to summary
 */
function updateExistingOrder() {
    const orderId = document.getElementById('order_id')?.value;
    if (!orderId) {
        showErrorMessage('Order ID not found');
        return;
    }
    
    const formData = new FormData(document.getElementById('orderForm'));
    formData.append('_method', 'PUT');
    
    showLoadingState('Updating order...');
    
    fetch(`{{ route('admin.orders.update', ['order' => 'ORDER_ID_PLACEHOLDER']) }}`.replace('ORDER_ID_PLACEHOLDER', orderId), {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            console.log('✅ Order updated successfully');
            showSuccessMessage('Order updated successfully');
            
            // Return to summary page as requested
            if (data.reservation_id) {
                window.location.href = `{{ route('admin.orders.reservations.summary', ['reservation' => ':reservation_id', 'order' => ':order_id']) }}`
                    .replace(':reservation_id', data.reservation_id)
                    .replace(':order_id', orderId);
            } else {
                window.location.href = "{{ route('admin.orders.summary', ['order' => 'ORDER_ID_PLACEHOLDER']) }}".replace('ORDER_ID_PLACEHOLDER', orderId);
            }
        } else {
            showErrorMessage('Failed to update order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('❌ Order update failed:', error);
        showErrorMessage('Failed to update order. Please try again.');
    });
}

/**
 * Save current order and create new one (Add Another flow)
 */
function saveAndCreateNewOrder() {
    // First save the current order
    const formData = new FormData(document.getElementById('orderForm'));
    formData.append('save_and_continue', true);
    
    showLoadingState('Saving order and preparing new order...');
    
    fetch('{{ route("admin.orders.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            console.log('✅ Order saved, creating new order');
            showSuccessMessage('Order saved! Starting new order...');
            
            // Redirect back to create page (fresh form)
            setTimeout(() => {
                window.location.href = '{{ route("admin.orders.create") }}';
            }, 1500);
        } else {
            showErrorMessage('Failed to save order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('❌ Save and continue failed:', error);
        showErrorMessage('Failed to save order. Please try again.');
    });
}

/**
 * Initialize branch filtering for admin
 */
function initializeBranchFiltering() {
    const branchSelector = document.getElementById('branch_id');
    if (!branchSelector) return;
    
    branchSelector.addEventListener('change', function() {
        const selectedBranchId = this.value;
        console.log('🏢 Branch changed:', selectedBranchId);
        
        if (selectedBranchId) {
            // Load menu items for selected branch
            loadMenuItemsForBranch(selectedBranchId);
        }
    });
}

/**
 * Initialize takeaway type selector (Call/In-house) for admin
 */
function initializeTakeawayTypeSelector() {
    const orderTypeField = document.getElementById('order_type');
    const takeawayTypeContainer = document.getElementById('takeaway_type_container');
    
    if (!orderTypeField || !takeawayTypeContainer) return;
    
    orderTypeField.addEventListener('change', function() {
        const orderType = this.value;
        
        if (orderType === 'takeaway_walk_in_demand' || orderType === 'takeaway_online_scheduled') {
            // Show takeaway type selector for admin
            takeawayTypeContainer.style.display = 'block';
            console.log('📞 Showing takeaway type selector');
        } else {
            takeawayTypeContainer.style.display = 'none';
        }
    });
}

/**
 * Load menu items for selected branch with stock validation
 */
function loadMenuItemsForBranch(branchId) {
    showLoadingState('Loading menu items...');
    
    fetch(`{{ route('admin.menu-items.by-branch') }}?branch_id=${branchId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            updateMenuItemsDisplay(data.items);
            console.log(`✅ Loaded ${data.items.length} menu items for branch ${branchId}`);
        } else {
            showErrorMessage('Failed to load menu items');
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('❌ Failed to load menu items:', error);
        showErrorMessage('Failed to load menu items');
    });
}

/**
 * Validate order form before submission
 */
function validateOrderForm() {
    const requiredFields = ['branch_id', 'order_type', 'customer_name', 'customer_phone'];
    const errors = [];
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field || !field.value.trim()) {
            errors.push(`${fieldName.replace('_', ' ')} is required`);
        }
    });
    
    // Check if at least one menu item is selected
    const selectedItems = document.querySelectorAll('.item-checkbox:checked');
    if (selectedItems.length === 0) {
        errors.push('Please select at least one menu item');
    }
    
    // Validate stock for selected items
    selectedItems.forEach(checkbox => {
        const itemElement = checkbox.closest('.menu-item');
        const stockLevel = parseInt(itemElement.dataset.stockLevel) || 0;
        const quantity = parseInt(itemElement.querySelector('.quantity-input')?.value) || 1;
        
        if (stockLevel < quantity) {
            const itemName = itemElement.querySelector('.item-name')?.textContent || 'Unknown item';
            errors.push(`Insufficient stock for ${itemName}. Available: ${stockLevel}, Required: ${quantity}`);
        }
    });
    
    if (errors.length > 0) {
        showErrorMessage('Validation errors:\n' + errors.join('\n'));
        return false;
    }
    
    return true;
}

/**
 * Utility functions for UI feedback
 */
function showLoadingState(message) {
    const loader = document.getElementById('loadingOverlay');
    if (loader) {
        loader.querySelector('.loading-text').textContent = message;
        loader.style.display = 'flex';
    }
}

function hideLoadingState() {
    const loader = document.getElementById('loadingOverlay');
    if (loader) {
        loader.style.display = 'none';
    }
}

function showSuccessMessage(message) {
    showToast(message, 'success');
}

function showErrorMessage(message) {
    showToast(message, 'error');
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

/**
 * Initialize quantity control buttons (+ and - buttons)
 */
function initializeQuantityControls() {
    console.log('🔢 Initializing quantity controls...');
    
    // Handle quantity increase buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.qty-increase')) {
            e.preventDefault();
            const button = e.target.closest('.qty-increase');
            const itemId = button.getAttribute('data-item-id');
            const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
            const checkbox = document.querySelector(`.item-check[data-item-id="${itemId}"]`);
            
            if (qtyInput && !qtyInput.disabled && !button.disabled) {
                const currentValue = parseInt(qtyInput.value) || 1;
                const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
                
                if (currentValue < maxValue) {
                    qtyInput.value = currentValue + 1;
                    updateCartDisplay(itemId, qtyInput.value);
                    
                    // Update decrease button state
                    const decreaseBtn = document.querySelector(`.qty-decrease[data-item-id="${itemId}"]`);
                    if (decreaseBtn) {
                        decreaseBtn.disabled = qtyInput.value <= 1;
                    }
                    
                    // Update increase button state
                    button.disabled = qtyInput.value >= maxValue;
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
                    updateCartDisplay(itemId, qtyInput.value);
                    
                    // Update decrease button state
                    button.disabled = qtyInput.value <= 1;
                    
                    // Update increase button state
                    const increaseBtn = document.querySelector(`.qty-increase[data-item-id="${itemId}"]`);
                    const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
                    if (increaseBtn) {
                        increaseBtn.disabled = qtyInput.value >= maxValue;
                    }
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
            
            // Update button states
            const decreaseBtn = document.querySelector(`.qty-decrease[data-item-id="${itemId}"]`);
            const increaseBtn = document.querySelector(`.qty-increase[data-item-id="${itemId}"]`);
            
            if (decreaseBtn) {
                decreaseBtn.disabled = value <= 1;
            }
            if (increaseBtn) {
                increaseBtn.disabled = value >= maxValue;
            }
            
            updateCartDisplay(itemId, value);
        }
    });
}

/**
 * Initialize item selection (checkbox) functionality
 */
function initializeItemSelection() {
    console.log('☑️ Initializing item selection...');
    
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-check') || e.target.classList.contains('add-to-order')) {
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
                    
                    // Set proper button states based on current value and max
                    const currentValue = parseInt(qtyInput.value) || 1;
                    const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
                    
                    if (decreaseBtn) {
                        decreaseBtn.disabled = currentValue <= 1;
                    }
                    if (increaseBtn) {
                        increaseBtn.disabled = currentValue >= maxValue;
                    }
                } else {
                    if (increaseBtn) increaseBtn.disabled = false;
                    if (decreaseBtn) decreaseBtn.disabled = true; // Always disable decrease when qty=1
                }
                
                // Add to cart display
                addToCartDisplay(itemId);
            } else {
                // Disable quantity controls
                if (qtyInput) {
                    qtyInput.disabled = true;
                    qtyInput.removeAttribute('name');
                }
                if (increaseBtn) increaseBtn.disabled = true;
                if (decreaseBtn) decreaseBtn.disabled = true;
                
                // Remove from cart display
                removeFromCartDisplay(itemId);
            }
        }
    });
}

/**
 * Update cart display when quantity changes
 */
function updateCartDisplay(itemId, quantity) {
    console.log(`🛒 Updating cart display for item ${itemId}, quantity: ${quantity}`);
    
    const cartItem = document.querySelector(`#cart-item-${itemId}`);
    if (cartItem) {
        const qtySpan = cartItem.querySelector('.cart-item-qty');
        const priceSpan = cartItem.querySelector('.cart-item-price');
        
        if (qtySpan) qtySpan.textContent = quantity;
        
        // Update price if available
        const itemPrice = getItemPrice(itemId);
        if (priceSpan && itemPrice) {
            const totalPrice = itemPrice * quantity;
            priceSpan.textContent = `LKR ${totalPrice.toFixed(2)}`;
        }
    }
    
    updateCartTotals();
}

/**
 * Add item to cart display
 */
function addToCartDisplay(itemId) {
    console.log(`➕ Adding item ${itemId} to cart display`);
    
    const cartItemsContainer = document.getElementById('cart-items');
    if (!cartItemsContainer) return;
    
    // Remove empty cart message if it exists
    const emptyMessage = cartItemsContainer.querySelector('.text-center');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    // Check if item already exists in cart
    if (document.querySelector(`#cart-item-${itemId}`)) {
        return;
    }
    
    const itemName = getItemName(itemId);
    const itemPrice = getItemPrice(itemId);
    const quantity = getItemQuantity(itemId);
    
    const cartItemHTML = `
        <div id="cart-item-${itemId}" class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
            <div class="flex-1">
                <div class="font-medium text-sm text-gray-800">${itemName}</div>
                <div class="text-xs text-gray-500">Qty: <span class="cart-item-qty">${quantity}</span></div>
            </div>
            <div class="cart-item-price text-sm font-semibold text-blue-600">LKR ${(itemPrice * quantity).toFixed(2)}</div>
        </div>
    `;
    
    cartItemsContainer.insertAdjacentHTML('beforeend', cartItemHTML);
    updateCartTotals();
}

/**
 * Remove item from cart display
 */
function removeFromCartDisplay(itemId) {
    console.log(`➖ Removing item ${itemId} from cart display`);
    
    const cartItem = document.querySelector(`#cart-item-${itemId}`);
    if (cartItem) {
        cartItem.remove();
    }
    
    // Show empty message if no items left
    const cartItemsContainer = document.getElementById('cart-items');
    if (cartItemsContainer && cartItemsContainer.children.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-shopping-basket text-gray-200 text-5xl mb-3"></i>
                <p class="text-gray-400 font-medium">Your cart is empty</p>
                <p class="text-gray-400 text-sm mt-1">Add items from the menu</p>
            </div>
        `;
    }
    
    updateCartTotals();
}

/**
 * Helper functions to get item information
 */
function getItemName(itemId) {
    const itemElement = document.querySelector(`#item_${itemId}`);
    if (itemElement) {
        const labelElement = itemElement.closest('.item-card, .bg-white').querySelector('label');
        return labelElement ? labelElement.textContent.trim() : `Item ${itemId}`;
    }
    return `Item ${itemId}`;
}

function getItemPrice(itemId) {
    const itemElement = document.querySelector(`#item_${itemId}`);
    if (itemElement) {
        const priceElement = itemElement.closest('.item-card, .bg-white').querySelector('.text-blue-600, .font-semibold');
        
        if (priceElement) {
            const priceText = priceElement.textContent;
            const priceMatch = priceText.match(/[\d,]+\.?\d*/);
            return priceMatch ? parseFloat(priceMatch[0].replace(',', '')) : 0;
        }
    }
    return 0;
}

function getItemQuantity(itemId) {
    const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
    return qtyInput ? parseInt(qtyInput.value) || 1 : 1;
}

/**
 * Update cart totals
 */
function updateCartTotals() {
    const cartItems = document.querySelectorAll('[id^="cart-item-"]');
    let subtotal = 0;
    
    cartItems.forEach(item => {
        const priceElement = item.querySelector('.cart-item-price');
        if (priceElement) {
            const priceText = priceElement.textContent;
            const priceMatch = priceText.match(/[\d,]+\.?\d*/);
            if (priceMatch) {
                subtotal += parseFloat(priceMatch[0].replace(',', ''));
            }
        }
    });
    
    const tax = subtotal * 0.1; // 10% tax
    const total = subtotal + tax;
    
    // Update display elements
    const subtotalElement = document.getElementById('cart-subtotal');
    const taxElement = document.getElementById('cart-tax');
    const totalElement = document.getElementById('cart-total');
    
    if (subtotalElement) subtotalElement.textContent = `LKR ${subtotal.toFixed(2)}`;
    if (taxElement) taxElement.textContent = `LKR ${tax.toFixed(2)}`;
    if (totalElement) totalElement.textContent = `LKR ${total.toFixed(2)}`;
}

// Initialize quantity controls when the page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeQuantityControls();
    initializeItemSelection();
});

console.log('✅ Admin Order Management JavaScript initialized successfully');
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/order-system.js') }}"></script>
@endpush