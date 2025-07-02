@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Create Takeaway Order</h2>
        </div>
        
        <!-- Card Body -->
        <div class="p-6">
            <form method="POST" action="{{ route('orders.takeaway.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column - Order Details -->
                    <div class="space-y-6">
                        <!-- Order Information Section -->
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Order Information</h3>
                            
                            <!-- Show Takeaway Order Type Info (No Selection Needed) -->
                            <div class="mb-4 bg-blue-50 p-3 rounded-lg border border-blue-200">
                                <div class="flex items-center">
                                    <i class="fas fa-shopping-bag text-blue-600 mr-2"></i>
                                    <span class="font-semibold text-blue-800">Takeaway Order</span>
                                </div>
                                <p class="text-blue-700 text-sm mt-1">This order is for pickup/delivery</p>
                                <input type="hidden" name="order_type" value="takeaway_walk_in_demand">
                            </div>

                            <div class="mb-4" @if(auth()->check() && auth()->user()->isAdmin()) style="display:none" @endif>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select Outlet</label>
                                <select name="branch_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $defaultBranch == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Time</label>
                                <input type="datetime-local" name="order_time" 
                                    value="{{ auth()->check() && auth()->user()->isAdmin() ? now()->format('Y-m-d\TH:i') : '' }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border" 
                                    required>
                            </div>

                            <!-- Add Special Instructions Field -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions <span class="text-gray-500 text-xs">(Optional)</span></label>
                                <textarea name="special_instructions" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border" 
                                    rows="3"
                                    placeholder="Any special requests or instructions for your order..."></textarea>
                            </div>
                        </div>

                        <!-- Customer Information Section -->
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Customer Information</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border" 
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="customer_phone" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border" 
                                    required
                                    pattern="[0-9]{10,15}" 
                                    title="Please enter a valid 10-15 digit phone number">
                                <p class="mt-1 text-sm text-gray-500">We'll notify you about your order status</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Menu Items -->
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 h-fit">
                        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Menu Items</h3>
                        
                        <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                            @foreach($items as $item)
                            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:border-blue-300 transition-colors duration-150">
                                <div class="flex items-center">
                                    <input class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 item-check" 
                                        type="checkbox" 
                                        value="{{ $item->id }}" 
                                        id="item_{{ $item->id }}" 
                                        data-item-id="{{ $item->id }}">
                                    
                                    <label for="item_{{ $item->id }}" class="ml-3 flex-1">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-gray-800">{{ $item->name }}</span>
                                                @if($item->item_type === 'KOT')
                                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">KOT Available</span>
                                                @endif
                                            </div>
                                            <span class="text-blue-600 font-semibold">LKR {{ number_format($item->selling_price, 2) }}</span>
                                        </div>
                                        @if($item->item_type === 'KOT')
                                            <div class="text-xs text-green-600 font-medium bg-green-50 px-2 py-1 rounded mt-1 inline-block">✓ Always Available</div>
                                        @elseif($item->item_type === 'Buy & Sell')
                                            @if($item->current_stock > 0)
                                                <div class="text-xs text-green-600 font-medium mt-1">In Stock ({{ $item->current_stock }})</div>
                                            @else
                                                <div class="text-xs text-red-600 font-medium mt-1">Out of Stock</div>
                                            @endif
                                        @endif
                                    </label>
                                    
                                    <div class="flex items-center border border-gray-300 rounded overflow-hidden touch-friendly-controls">
                                        <button type="button"
                                            class="qty-decrease w-12 h-12 bg-red-50 hover:bg-red-100 active:bg-red-200 text-red-600 text-2xl font-bold flex items-center justify-center touch-manipulation transition-all duration-150 border-r border-gray-300"
                                            data-item-id="{{ $item->id }}"
                                            disabled>−</button>
                                        <input type="number"
                                            min="1"
                                            max="99"
                                            value="1"
                                            class="item-qty w-16 h-12 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 touch-manipulation"
                                            data-item-id="{{ $item->id }}"
                                            disabled
                                            readonly>
                                        <button type="button"
                                            class="qty-increase w-12 h-12 bg-green-50 hover:bg-green-100 active:bg-green-200 text-green-600 text-2xl font-bold flex items-center justify-center touch-manipulation transition-all duration-150 border-l border-gray-300"
                                            data-item-id="{{ $item->id }}"
                                            disabled>+</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Order Summary Section -->
                <div class="mt-6 bg-gray-50 p-5 rounded-lg border border-gray-200" id="order-summary" style="display: none;">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Order Summary</h3>
                    <div id="selected-items" class="space-y-2"></div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between items-center text-lg font-semibold">
                            <span>Total Items:</span>
                            <span id="total-items">0</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-bold text-white text-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all shadow-lg transform hover:scale-105 touch-manipulation">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Create Order for Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Admin-specific time handling
    @if(auth()->check() && auth()->user()->isAdmin())
    const setDefaultTime = (minutesToAdd) => {
        const time = new Date();
        time.setMinutes(time.getMinutes() + minutesToAdd);
        const timeInput = document.querySelector('input[name="order_time"]');
        if (timeInput) {
            timeInput.value = time.toISOString().slice(0, 16);
        }
    };

    // Initial time setting
    setDefaultTime(15);

    // Handle order type changes
    const orderTypeSelect = document.querySelector('select[name="order_type"]');
    if (orderTypeSelect) {
        orderTypeSelect.addEventListener('change', function() {
            setDefaultTime(this.value === 'takeaway_in_call_scheduled' ? 30 : 15);
        });
    }
    @endif

    // Initialize quantity controls first
    initializeQuantityControls();

    // Enable/disable qty and buttons on checkbox change
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            const plusBtn = document.querySelector('.qty-increase[data-item-id="' + itemId + '"]');
            const minusBtn = document.querySelector('.qty-decrease[data-item-id="' + itemId + '"]');
            const itemContainer = this.closest('.bg-white');
            
            if (this.checked) {
                // Enable controls
                qtyInput.disabled = false;
                qtyInput.removeAttribute('readonly');
                plusBtn.disabled = false;
                minusBtn.disabled = false;
                
                // Set proper form field names for Laravel validation
                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');
                
                // Create hidden input for item_id to ensure it's submitted with form
                let hiddenInput = itemContainer.querySelector('.item-hidden-' + itemId);
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'items[' + itemId + '][item_id]';
                    hiddenInput.value = itemId;
                    hiddenInput.className = 'item-hidden-' + itemId;
                    itemContainer.appendChild(hiddenInput);
                }
                
                // Visual feedback - highlight selected item
                itemContainer.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
                
                updateButtonStates(itemId, qtyInput.value);
                console.log('✅ Item selected:', itemId, 'Quantity:', qtyInput.value);
                updateOrderSummary();
            } else {
                // Disable controls
                qtyInput.disabled = true;
                qtyInput.setAttribute('readonly', 'readonly');
                plusBtn.disabled = true;
                minusBtn.disabled = true;
                
                // Remove form field names
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;
                
                // Remove hidden input
                const hiddenInput = itemContainer.querySelector('.item-hidden-' + itemId);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
                
                // Remove visual feedback
                itemContainer.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
                
                console.log('❌ Item deselected:', itemId);
                updateOrderSummary();
            }
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const phoneInput = document.querySelector('input[name="customer_phone"]');
        if (phoneInput && !phoneInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            phoneInput.focus();
            return false;
        }
        
        // Check if at least one item is selected
        const checkedItems = document.querySelectorAll('.item-check:checked');
        if (checkedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one item');
            return false;
        }
        
        // Add loading state to form
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin h-6 w-6 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating Order...
            `;
            this.classList.add('form-submitting');
        }
    });
});

/**
 * Initialize quantity controls for takeaway orders with enhanced touch support
 */
function initializeQuantityControls() {
    console.log('🔢 Initializing enhanced touch-friendly quantity controls...');
    
    // Handle quantity increase buttons with enhanced touch feedback
    document.addEventListener('click', function(e) {
        if (e.target.closest('.qty-increase')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.qty-increase');
            const itemId = button.getAttribute('data-item-id');
            const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
            
            if (qtyInput && !qtyInput.disabled && !button.disabled) {
                const currentValue = parseInt(qtyInput.value) || 1;
                const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
                
                if (currentValue < maxValue) {
                    qtyInput.value = currentValue + 1;
                    updateButtonStates(itemId, qtyInput.value);
                    console.log('➕ Quantity increased for item', itemId, 'to', qtyInput.value);
                    if (typeof updateCart === 'function') updateCart();
                    updateOrderSummary();
                    
                    // Enhanced visual feedback for touch devices
                    button.style.transform = 'scale(0.9)';
                    button.style.backgroundColor = '#22c55e';
                    setTimeout(() => {
                        button.style.transform = 'scale(1)';
                        button.style.backgroundColor = '';
                    }, 150);
                    
                    // Haptic feedback for mobile devices (if supported)
                    if ('vibrate' in navigator) {
                        navigator.vibrate(50);
                    }
                }
            }
        }
    });
    
    // Handle quantity decrease buttons with enhanced touch feedback
    document.addEventListener('click', function(e) {
        if (e.target.closest('.qty-decrease')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.qty-decrease');
            const itemId = button.getAttribute('data-item-id');
            const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);
            
            if (qtyInput && !qtyInput.disabled && !button.disabled) {
                const currentValue = parseInt(qtyInput.value) || 1;
                
                if (currentValue > 1) {
                    qtyInput.value = currentValue - 1;
                    updateButtonStates(itemId, qtyInput.value);
                    console.log('➖ Quantity decreased for item', itemId, 'to', qtyInput.value);
                    if (typeof updateCart === 'function') updateCart();
                    updateOrderSummary();
                    
                    // Enhanced visual feedback for touch devices
                    button.style.transform = 'scale(0.9)';
                    button.style.backgroundColor = '#ef4444';
                    setTimeout(() => {
                        button.style.transform = 'scale(1)';
                        button.style.backgroundColor = '';
                    }, 150);
                    
                    // Haptic feedback for mobile devices (if supported)
                    if ('vibrate' in navigator) {
                        navigator.vibrate(50);
                    }
                }
            }
        }
    });
    
    // Prevent manual input changes since we want touch-only interaction
    document.addEventListener('keydown', function(e) {
        if (e.target.classList.contains('item-qty')) {
            // Allow only Tab, Enter, and arrow keys for accessibility
            const allowedKeys = ['Tab', 'Enter', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];
            if (!allowedKeys.includes(e.key)) {
                e.preventDefault();
            }
        }
    });
    
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty')) {
            e.preventDefault(); // Prevent manual typing
        }
    });
    
    // Handle blur event to ensure valid values
    document.addEventListener('blur', function(e) {
        if (e.target.classList.contains('item-qty')) {
            const qtyInput = e.target;
            let value = parseInt(qtyInput.value) || 1;
            const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
            
            // Ensure value is within bounds
            if (value < 1) value = 1;
            if (value > maxValue) value = maxValue;
            
            qtyInput.value = value;
            const itemId = qtyInput.getAttribute('data-item-id');
            updateButtonStates(itemId, value);
            if (typeof updateCart === 'function') updateCart();
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
 * Update the order summary display
 */
function updateOrderSummary() {
    const selectedItems = document.querySelectorAll('.item-check:checked');
    const summaryContainer = document.getElementById('order-summary');
    const selectedItemsContainer = document.getElementById('selected-items');
    const totalItemsSpan = document.getElementById('total-items');
    
    if (selectedItems.length === 0) {
        summaryContainer.style.display = 'none';
        return;
    }
    
    summaryContainer.style.display = 'block';
    selectedItemsContainer.innerHTML = '';
    
    let totalItems = 0;
    
    selectedItems.forEach(function(checkbox) {
        const itemId = checkbox.getAttribute('data-item-id');
        const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
        const label = document.querySelector('label[for="item_' + itemId + '"]');
        const itemName = label.querySelector('.font-medium').textContent;
        const quantity = parseInt(qtyInput.value) || 1;
        
        totalItems += quantity;
        
        const summaryItem = document.createElement('div');
        summaryItem.className = 'flex justify-between items-center text-sm';
        summaryItem.innerHTML = `
            <span>${itemName}</span>
            <span class="font-medium">Qty: ${quantity}</span>
        `;
        selectedItemsContainer.appendChild(summaryItem);
    });
    
    totalItemsSpan.textContent = totalItems;
}
</script>

<style>
/* Enhanced touch-friendly controls */
.touch-friendly-controls {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.touch-friendly-controls button {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    cursor: pointer;
    transition: all 0.15s ease;
}

.touch-friendly-controls button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background-color: #f3f4f6 !important;
    color: #9ca3af !important;
}

.touch-friendly-controls button:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.touch-friendly-controls button:not(:disabled):active {
    transform: translateY(0);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.touch-friendly-controls input[type="number"] {
    -moz-appearance: textfield;
    -webkit-appearance: none;
    appearance: none;
    background: #fff;
    border: none;
    font-weight: 600;
    color: #1f2937;
}

.touch-friendly-controls input[type="number"]::-webkit-outer-spin-button,
.touch-friendly-controls input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.touch-friendly-controls input[type="number"]:disabled {
    background-color: #f9fafb;
    color: #6b7280;
}

.touch-friendly-controls input[type="number"]:focus {
    outline: none;
    box-shadow: inset 0 0 0 2px #3b82f6;
}

/* Responsive design for mobile */
@media (max-width: 768px) {
    .touch-friendly-controls button {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
    }
    
    .touch-friendly-controls input[type="number"] {
        width: 64px;
        height: 48px;
        font-size: 1.125rem;
    }
}

/* Loading state for form submission */
.form-submitting {
    opacity: 0.6;
    pointer-events: none;
}

/* Item selection highlight */
.item-check:checked + label {
    background-color: #eff6ff;
    border-color: #3b82f6;
}

/* Smooth animations */
.transition-all {
    transition: all 0.15s ease;
}

/* Touch feedback */
@media (hover: none) and (pointer: coarse) {
    .touch-friendly-controls button:not(:disabled):active {
        transform: scale(0.95);
    }
}
</style>
@endsection