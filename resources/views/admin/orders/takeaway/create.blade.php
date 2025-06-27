@extends('layouts.admin')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-700">
                    <div class="flex items-center">
                        <i class="fas fa-utensils text-white text-2xl bg-white/20 p-3 rounded-xl mr-4"></i>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Create Takeaway Order</h1>
                            <p class="text-blue-100 mt-1">Fill in the details below to create a new takeaway order</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.orders.takeaway.store') }}" id="order-form">
                        @csrf
                        <input type="hidden" name="order_type" value="{{ request('type', 'takeaway_walk_in_demand') }}">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Left Column - Order Info -->
                            <div>
                                <!-- Order Information Section -->
                                <div class="mb-8">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-info-circle text-blue-500 bg-blue-100 p-2 rounded-lg mr-3"></i>
                                        <h2 class="text-xl font-semibold text-gray-800">Order Information</h2>
                                    </div>
                                    
                                    @if(auth('admin')->check())
                                    <div class="form-group mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Order Type</label>
                                        <select name="order_type" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="takeaway_walk_in_demand" selected>Walk-in/Demand</option>
                                            <option value="takeaway_in_call_scheduled">Scheduled Order</option>
                                        </select>
                                    </div>
                                    @endif

                                    <div class="form-group mb-4" @if(auth('admin')->check()) style="display:none" @endif>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Outlet</label>
                                        <select name="branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $defaultBranch == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pickup Time</label>
                                        <div class="relative">
                                            <input type="datetime-local" name="order_time" 
                                                value="{{ old('order_time', now()->format('Y-m-d\TH:i')) }}"
                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                required>
                                            <i class="fas fa-calendar-alt absolute right-3 top-3 text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Information Section -->
                                <div>
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-user text-blue-500 bg-blue-100 p-2 rounded-lg mr-3"></i>
                                        <h2 class="text-xl font-semibold text-gray-800">Customer Information</h2>
                                    </div>
                                    
                                    <div class="form-group mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="customer_name" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                            required 
                                            value="{{ old('customer_name', 'Not Provided') }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-phone text-gray-400"></i>
                                            </div>
                                            <input type="tel" name="customer_phone" 
                                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                                required
                                                pattern="^\+?[0-9 ]{10,15}$" 
                                                title="Please enter a valid phone number (e.g., +94 11 234 5678 or 0112345678)"
                                                value="{{ old('customer_phone', preg_match('/^\+?[0-9 ]{10,15}$/', preg_replace('/[^\+0-9 ]/', '', optional(auth('admin')->user()->branch)->phone ?? '')) ? preg_replace('/[^\+0-9 ]/', '', optional(auth('admin')->user()->branch)->phone) : '') }}">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">We'll notify you about your order status</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Menu Items -->
                            <div>
                                <!-- Menu Items Section -->
                                <div class="mb-6">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-list-ul text-blue-500 bg-blue-100 p-2 rounded-lg mr-3"></i>
                                        <h2 class="text-xl font-semibold text-gray-800">Menu Items</h2>
                                    </div>
                                    
                                    <div class="relative mb-4">
                                        <input type="text" id="menu-search" placeholder="Search menu items..." 
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                    </div>
                                    
                                    <div class="menu-items-container border border-gray-200 rounded-xl p-2" style="max-height: 400px; overflow-y: auto;">
                                        @foreach($menuItems as $item)
                                        @php
                                            $existing = isset($order) ? $order->items->firstWhere('menu_item_id', $item->id) : null;
                                            
                                            // Handle KOT vs Buy & Sell items differently
                                            if ($item->item_type === 'KOT') {
                                                $stockLevel = 'unlimited';
                                                $isLowStock = false;
                                                $isOutOfStock = false;
                                                $stockBadgeClass = 'bg-green-100 text-green-800';
                                                $stockText = 'Always Available';
                                            } else {
                                                $stockLevel = $item->current_stock ?? 0;
                                                $isLowStock = $stockLevel > 0 && $stockLevel <= 5;
                                                $isOutOfStock = $stockLevel <= 0;
                                                $stockBadgeClass = $isOutOfStock ? 'bg-red-100 text-red-800' : ($isLowStock ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                                $stockText = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? "Low Stock ({$stockLevel})" : 'Available');
                                            }
                                        @endphp
                                        <div class="menu-item-card bg-white border border-gray-200 rounded-lg p-4 mb-3 hover:shadow-md transition-all duration-300 {{ $isOutOfStock ? 'opacity-50' : '' }}">
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
                                                                    id="item_{{ $item->id }}"
                                                                    class="mt-1 focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded item-check"
                                                                    @if($existing) checked @endif
                                                                    @if($isOutOfStock) disabled @endif>
                                                                <label for="item_{{ $item->id }}" class="font-semibold text-gray-800 {{ $isOutOfStock ? 'text-gray-400' : '' }}">{{ $item->name }}</label>
                                                                
                                                                <!-- KOT Badge -->
                                                                @if($item->item_type === 'KOT')
                                                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">KOT Available</span>
                                                                @endif
                                                                
                                                                <!-- Stock Status Badge for Buy & Sell items only -->
                                                                @if($item->item_type === 'Buy & Sell')
                                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $stockBadgeClass }}">
                                                                    {{ $stockText }}
                                                                </span>
                                                                @endif
                                                            </div>
                                                            @if($item->description)
                                                            <p class="ml-6 text-sm text-gray-500">{{ $item->description }}</p>
                                                            @endif
                                                            
                                                            <!-- Additional Item Info -->
                                                            <div class="ml-6 mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                                                @if(isset($item->attributes['prep_time_minutes']))
                                                                <div class="flex items-center">
                                                                    <i class="fas fa-clock mr-1"></i>
                                                                    <span>{{ $item->attributes['prep_time_minutes'] }} mins</span>
                                                                </div>
                                                                @endif
                                                                @if(isset($item->attributes['cuisine_type']))
                                                                <div class="flex items-center">
                                                                    <i class="fas fa-tag mr-1"></i>
                                                                    <span>{{ ucfirst($item->attributes['cuisine_type']) }}</span>
                                                                </div>
                                                                @endif
                                                                @if(isset($item->attributes['serving_size']))
                                                                <div class="flex items-center">
                                                                    <i class="fas fa-users mr-1"></i>
                                                                    <span>{{ $item->attributes['serving_size'] }}</span>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-blue-600 font-medium">LKR {{ number_format($item->selling_price, 2) }}</span>
                                                            @if($stockLevel > 0)
                                                            <div class="text-xs text-gray-500 mt-1">Stock: {{ $stockLevel }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="ml-6 mt-3 {{ $isOutOfStock ? 'hidden' : '' }}">
                                                        <input type="number" 
                                                            name="items[{{ $item->id }}][quantity]" 
                                                            min="1" 
                                                            max="{{ $stockLevel }}"
                                                            value="{{ $existing ? $existing->quantity : 1 }}" 
                                                            class="w-20 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 quantity-input"
                                                            disabled>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Order Summary Section -->
                                <div id="order-summary" class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                                    <div class="flex items-center mb-3">
                                        <i class="fas fa-receipt text-blue-500 bg-blue-100 p-2 rounded-lg mr-3"></i>
                                        <h2 class="text-lg font-semibold text-gray-800">Order Summary</h2>
                                    </div>
                                    
                                    <div id="summary-list" class="mb-4 max-h-40 overflow-y-auto pr-2">
                                        <div class="text-center py-4">
                                            <i class="fas fa-shopping-basket text-gray-200 text-4xl mb-2"></i>
                                            <p class="text-gray-400">No items selected</p>
                                        </div>
                                    </div>
                                    
                                    <div class="border-t border-gray-200 pt-3">
                                        <div class="flex justify-between font-bold text-lg text-gray-800">
                                            <span>Total:</span>
                                            <span id="summary-total">LKR 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        
                        <div class="flex justify-between items-center">
                                <button type="submit" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Place Order
                                </button>
                                
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .menu-item-card {
        transition: all 0.3s ease;
    }
    .menu-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
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
    #summary-list::-webkit-scrollbar {
        width: 6px;
    }
    #summary-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    #summary-list::-webkit-scrollbar-thumb {
        background: #c5d5f1;
        border-radius: 10px;
    }
    #summary-list::-webkit-scrollbar-thumb:hover {
        background: #93b6f1;
    }
    .menu-items-container::-webkit-scrollbar {
        width: 6px;
    }
    .menu-items-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .menu-items-container::-webkit-scrollbar-thumb {
        background: #c5d5f1;
        border-radius: 10px;
    }
    .menu-items-container::-webkit-scrollbar-thumb:hover {
        background: #93b6f1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Initialize datetime picker
    const orderTimeInput = document.querySelector('input[name="order_time"]');
    if (orderTimeInput) {
        const now = new Date();
        const pad = n => n.toString().padStart(2, '0');
        const formatted = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        orderTimeInput.value = formatted;
        orderTimeInput.min = formatted;
    }

    // Admin-specific logic for order type
    if (isAdmin) {
        const setDefaultTime = (minutesToAdd) => {
            const time = new Date();
            time.setMinutes(time.getMinutes() + minutesToAdd);
            const pad = n => n.toString().padStart(2, '0');
            const formatted = `${time.getFullYear()}-${pad(time.getMonth()+1)}-${pad(time.getDate())}T${pad(time.getHours())}:${pad(time.getMinutes())}`;
            orderTimeInput.value = formatted;
            orderTimeInput.min = formatted;
        };
        
        const orderTypeSelect = document.querySelector('select[name="order_type"]');
        if (orderTypeSelect) {
            orderTypeSelect.addEventListener('change', function() {
                setDefaultTime(this.value === 'takeaway_in_call_scheduled' ? 30 : 15);
            });
        }
    }

    // Menu search functionality
    document.getElementById('menu-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.menu-item-card').forEach(card => {
            const itemName = card.querySelector('label').textContent.toLowerCase();
            if (itemName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Update order summary
    function updateSummary() {
        const summaryList = document.getElementById('summary-list');
        const summaryTotal = document.getElementById('summary-total');
        let total = 0;
        let listItems = '';

        document.querySelectorAll('.item-check:checked').forEach(checkbox => {
            const card = checkbox.closest('.menu-item-card');
            const name = card.querySelector('label').textContent.trim();
            const priceText = card.querySelector('.text-blue-600').textContent;
            const price = parseFloat(priceText.replace('LKR', '').trim());
            const qtyInput = card.querySelector('.quantity-input');
            const quantity = parseInt(qtyInput.value || 1);
            const itemTotal = price * quantity;
            
            total += itemTotal;
            listItems += `
                <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <div class="font-medium text-gray-700 truncate">${name}</div>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded mr-2">Rs. ${price.toFixed(2)}</span>
                            <span class="text-gray-400">×</span>
                            <span class="ml-2 bg-gray-100 text-gray-700 px-2 py-0.5 rounded">${quantity}</span>
                        </div>
                    </div>
                    <div class="font-medium text-gray-800">
                        Rs. ${itemTotal.toFixed(2)}
                    </div>
                </div>`;
        });

        if (listItems) {
            summaryList.innerHTML = listItems;
        } else {
            summaryList.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-shopping-basket text-gray-200 text-4xl mb-2"></i>
                    <p class="text-gray-400">No items selected</p>
                </div>`;
        }
        
        summaryTotal.textContent = 'LKR ' + total.toFixed(2);
    }

    // Toggle quantity inputs when items are selected
    document.querySelectorAll('.item-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.menu-item-card').querySelector('.quantity-input');
            quantityInput.disabled = !this.checked;
            if (!this.checked) quantityInput.value = 1;
            updateSummary();
        });
    });

    // Update summary when quantities change
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', updateSummary);
        input.addEventListener('input', updateSummary);
    });

    // Enhanced form validation with stock checks
    document.getElementById('order-form').addEventListener('submit', function(e) {
        const phoneInput = document.querySelector('input[name="customer_phone"]');
        if (!phoneInput.value.trim() || !phoneInput.checkValidity()) {
            e.preventDefault();
            alert('Please enter a valid 10-15 digit phone number');
            phoneInput.focus();
            return;
        }

        // Stock validation
        let stockErrors = [];
        document.querySelectorAll('.item-check:checked').forEach(checkbox => {
            const itemCard = checkbox.closest('.menu-item-card');
            const quantityInput = itemCard.querySelector('.quantity-input');
            const stockLevel = parseInt(quantityInput.getAttribute('max')) || 0;
            const requestedQty = parseInt(quantityInput.value) || 0;
            const itemName = checkbox.closest('.menu-item-card').querySelector('label').textContent;

            if (requestedQty > stockLevel) {
                stockErrors.push(`${itemName}: Requested ${requestedQty}, but only ${stockLevel} available`);
            }
        });

        if (stockErrors.length > 0) {
            e.preventDefault();
            alert('Stock validation errors:\n' + stockErrors.join('\n'));
            return;
        }

        // Check if at least one item is selected
        const selectedItems = document.querySelectorAll('.item-check:checked');
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one menu item');
            return;
        }
    });

    // Real-time stock validation on quantity change
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            const maxStock = parseInt(this.getAttribute('max')) || 0;
            const currentValue = parseInt(this.value) || 0;
            const stockWarning = this.parentNode.querySelector('.stock-warning');
            
            // Remove existing warning
            if (stockWarning) {
                stockWarning.remove();
            }
            
            if (currentValue > maxStock) {
                this.style.borderColor = '#dc2626';
                const warning = document.createElement('div');
                warning.className = 'stock-warning text-xs text-red-600 mt-1';
                warning.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i>Only ${maxStock} available`;
                this.parentNode.appendChild(warning);
            } else {
                this.style.borderColor = '#d1d5db';
            }
            
            updateSummary();
        });
        
        input.addEventListener('change', updateSummary);
    });
        });
    });

    document.querySelectorAll('.item-qty').forEach(function(input) {
        input.addEventListener('input', function() {
            if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                this.value = 1;
            }
        });
    });

    document.querySelectorAll('.qty-increase').forEach(function(btn) {
        btn.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            const input = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            if (!input.disabled) {
                input.value = parseInt(input.value) + 1;
                input.dispatchEvent(new Event('input'));
            }
        });
    });

    document.querySelectorAll('.qty-decrease').forEach(function(btn) {
        btn.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            const input = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            if (!input.disabled) {
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('input'));
                }
            }
        });
    });
});
</script>
@endsection