@extends('layouts.admin')

@php
    use App\Models\OrderStatusStateMachine;
@endphp

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Edit Takeaway Order #{{ $order->id }}</h2>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.orders.takeaway.update', ['order' => $order->id]) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Left Column - Order Details -->
                    <div class="col-md-6">
                        <div class="order-details-section mb-4">
                            <h4 class="section-title border-bottom pb-2 mb-3">Order Information</h4>
                            
                            @if(auth()->check() && auth()->user()->isAdmin())
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Order Type</label>
                                <select name="order_type" class="form-select">
                                    <option value="takeaway_walk_in_demand" {{ $order->order_type == 'takeaway_walk_in_demand' ? 'selected' : '' }}>In-House</option>
                                    <option value="takeaway_in_call_scheduled" {{ $order->order_type == 'takeaway_in_call_scheduled' ? 'selected' : '' }}>In-Call</option>
                                </select>
                            </div>
                            @endif

                            <div class="form-group mb-3" @if(auth()->check() && auth()->user()->isAdmin()) style="display:none" @endif>
                                <label class="form-label fw-bold">Select Outlet</label>
                                <select name="branch_id" class="form-select" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $order->branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Pickup Time</label>
                                <input type="datetime-local" name="order_time" 
                                    value="{{ old('order_time', ($order->order_time instanceof \Carbon\Carbon ? $order->order_time : \Carbon\Carbon::parse($order->order_time))->format('Y-m-d\TH:i')) }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="customer-info-section">
                            <h4 class="section-title border-bottom pb-2 mb-3">Customer Information</h4>
                            
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" 
                                    value="{{ old('customer_name', $order->customer_name) }}" 
                                    class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="customer_phone" 
                                    value="{{ old('customer_phone', $order->customer_phone) }}" 
                                    class="form-control" required
                                    pattern="[0-9]{10,15}" 
                                    title="Please enter a valid 10-15 digit phone number">
                                <small class="form-text text-muted">We'll notify you about your order status</small>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Menu Items -->
                    <div class="col-md-6">
                        <div class="menu-items-section">
                            <h4 class="section-title border-bottom pb-2 mb-3">Menu Items</h4>
                            
                            <div class="menu-items-container" style="max-height: 400px; overflow-y: auto;">
                                @foreach($menuItems as $item)
                                @php
                                    $existing = isset($order) ? $order->items->firstWhere('menu_item_id', $item->id) : null;
                                @endphp
                                <div class="flex items-center border-b py-4">
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
                                            class="item-qty w-12 text-center border-x border-gray-300 text-sm focus:outline-none mx-1"
                                            data-item-id="{{ $item->id }}"
                                            @if(!$existing) disabled @endif
                                            @if($existing) name="items[{{ $item->id }}][quantity]" @endif>
                                        <button type="button"
                                            class="qty-increase w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xl flex items-center justify-center rounded"
                                            data-item-id="{{ $item->id }}"
                                            @if(!$existing) disabled @endif>+</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Order Status</label>
                    <select name="status" class="form-select" required>
                        <option value="{{ $order->status }}" selected>{{ ucfirst($order->status) }}</option>
                        @foreach(OrderStatusStateMachine::getValidTransitions($order->status) as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="{{ route('orders.takeaway.summary', $order) }}" class="btn btn-secondary px-4">
                        <i class="fas fa-times-circle me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .section-title {
        color: #2c3e50;
        font-weight: 600;
    }
    .menu-item-card:hover {
        background-color: #f8f9fa;
    }
    .quantity-input:disabled {
        background-color: #e9ecef;
        opacity: 1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Initialize enabled quantity inputs for checked items
    document.querySelectorAll('.item-check:checked').forEach(checkbox => {
        checkbox.closest('.menu-item-card').querySelector('.quantity-input').disabled = false;
    });

    // Admin-specific time handling
    if (isAdmin) {
        const setDefaultTime = (minutesToAdd) => {
            const time = new Date();
            time.setMinutes(time.getMinutes() + minutesToAdd);
            document.querySelector('input[name="order_time"]').value = time.toISOString().slice(0, 16);
        };

        // Handle order type changes
        document.querySelector('select[name="order_type"]').addEventListener('change', function() {
            setDefaultTime(this.value === 'takeaway_in_call_scheduled' ? 30 : 15);
        });
    }

    // Item selection handling
    document.querySelectorAll('.item-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.menu-item-card').querySelector('.quantity-input');
            quantityInput.disabled = !this.checked;
            if (!this.checked) quantityInput.value = 1;
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