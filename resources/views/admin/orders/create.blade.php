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
                                    <div class="flex items-center border-b py-4 menu-item"
                                        data-item-id="{{ $item->id }}"
                                        data-item-type="{{ $item->type }}"
                                        data-stock-level="{{ $item->stock_level }}"
                                        @if($item->is_kot) data-is-kot="true" @endif>
                                        <input type="checkbox"
                                            class="item-check mr-4"
                                            data-item-id="{{ $item->id }}"
                                            id="item_{{ $item->id }}"
                                            name="items[{{ $item->id }}][item_id]"
                                            value="{{ $item->id }}"
                                            @if($existing) checked @endif>
                                        <label for="item_{{ $item->id }}" class="flex-1">
                                            <span class="font-semibold">{{ $item->name }}</span>
                                            <span class="ml-2 text-gray-500">LKR {{ number_format($item->selling_price, 2) }}</span>
                                        </label>
                                        <div class="flex items-center ml-4">
                                            <button type="button"
                                                class="qty-decrease w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xl flex items-center justify-center rounded"
                                                data-item-id="{{ $item->id }}"
                                                @if(!$existing) disabled @endif>-</button>
                                            <input type="number"
                                                min="1"
                                                value="{{ $existing ? $existing->quantity : 1 }}"
                                                class="item-qty w-12 text-center border-x border-gray-300 text-sm focus:outline-none mx-1 quantity-input"
                                                data-item-id="{{ $item->id }}"
                                                @if(!$existing) disabled @endif
                                                @if($existing) name="items[{{ $item->id }}][quantity]" @endif>
                                            <button type="button"
                                                class="qty-increase w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xl flex items-center justify-center rounded"
                                                data-item-id="{{ $item->id }}"
                                                @if(!$existing) disabled @endif>+</button>
                                        </div>

                                        <!-- Stock and KOT Info -->
                                        <div class="hidden stock-container"></div>
                                        <div class="hidden badge-container"></div>
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
                window.location.href = `{{ route('admin.orders.summary', ':order_id') }}`
                    .replace(':order_id', orderId);
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

console.log('✅ Admin Order Management JavaScript initialized successfully');
</script>
@endsection