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
                            
                            @if(auth()->check() && auth()->user()->isAdmin())
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Order Type</label>
                                <select name="order_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                                    <option value="takeaway_walk_in_demand" selected>In-House</option>
                                    <option value="takeaway_in_call_scheduled">In-Call</option>
                                </select>
                            </div>
                            @endif

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
                                        name="items[{{ $item->id }}][item_id]" 
                                        value="{{ $item->id }}" 
                                        id="item_{{ $item->id }}" 
                                        data-item-id="{{ $item->id }}">
                                    
                                    <label for="item_{{ $item->id }}" class="ml-3 flex-1">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-gray-800">{{ $item->name }}</span>
                                            <span class="text-blue-600 font-semibold">LKR {{ number_format($item->selling_price, 2) }}</span>
                                        </div>
                                    </label>
                                    
                                    <div class="flex items-center">
                                        <button type="button"
                                            class="qty-decrease w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xl flex items-center justify-center rounded"
                                            data-item-id="{{ $item->id }}"
                                            disabled>-</button>
                                        <input type="number"
                                            min="1"
                                            value="{{ isset($existingItem) ? $existingItem->quantity : 1 }}"
                                            class="item-qty w-12 text-center border-x border-gray-300 text-sm focus:outline-none mx-1"
                                            data-item-id="{{ $item->id }}"
                                            name="items[{{ $item->id }}][quantity]"
                                            @if(empty($existingItem)) disabled @endif>
                                        <button type="button"
                                            class="qty-increase w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xl flex items-center justify-center rounded"
                                            data-item-id="{{ $item->id }}"
                                            disabled>+</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-md font-semibold text-white hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Place Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAdmin = @json(auth()->check() && auth()->user()->isAdmin());

    // Admin-specific time handling
    if (isAdmin) {
        const setDefaultTime = (minutesToAdd) => {
            const time = new Date();
            time.setMinutes(time.getMinutes() + minutesToAdd);
            document.querySelector('input[name="order_time"]').value = time.toISOString().slice(0, 16);
        };

        // Initial time setting
        setDefaultTime(15);

        // Handle order type changes
        document.querySelector('select[name="order_type"]').addEventListener('change', function() {
            setDefaultTime(this.value === 'takeaway_in_call_scheduled' ? 30 : 15);
        });
    }

    // Enable/disable qty and buttons on checkbox change
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            const plusBtn = document.querySelector('.qty-increase[data-item-id="' + itemId + '"]');
            const minusBtn = document.querySelector('.qty-decrease[data-item-id="' + itemId + '"]');
            if (this.checked) {
                qtyInput.disabled = false;
                plusBtn.disabled = false;
                minusBtn.disabled = false;
                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');
            } else {
                qtyInput.disabled = true;
                plusBtn.disabled = true;
                minusBtn.disabled = true;
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;
            }
            if (typeof updateCart === 'function') updateCart();
        });
    });

    // Prevent going below 1
    document.querySelectorAll('.item-qty').forEach(function(input) {
        input.addEventListener('input', function() {
            if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                this.value = 1;
            }
            if (typeof updateCart === 'function') updateCart();
        });
    });

    // Plus button
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

    // Minus button
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

    // Item selection handling
    document.querySelectorAll('[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.bg-white').querySelector('[type="number"]');
            quantityInput.disabled = !this.checked;
            if (!this.checked) {
                quantityInput.value = 1;
                quantityInput.classList.add('bg-gray-100');
            } else {
                quantityInput.classList.remove('bg-gray-100');
            }
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const phoneInput = document.querySelector('input[name="customer_phone"]');
        if (!phoneInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            phoneInput.focus();
        }
    });
});
</script>
@endsection