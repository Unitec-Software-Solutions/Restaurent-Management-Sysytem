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
            <form method="POST" action="{{ route('admin.orders.takeaway.update', $order) }}">
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
                                @foreach($items as $item)
                                @php
                                    $orderItem = $order->items->firstWhere('item_id', $item->id);
                                @endphp
                                <div class="card mb-2 menu-item-card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="form-check me-3">
                                            <input class="form-check-input item-check" type="checkbox" 
                                                name="items[{{ $item->id }}][item_id]" 
                                                value="{{ $item->id }}" 
                                                id="item_{{ $item->id }}"
                                                {{ $orderItem ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label flex-grow-1" for="item_{{ $item->id }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-medium">{{ $item->name }}</span>
                                                <span class="text-primary">LKR {{ number_format($item->selling_price, 2) }}</span>
                                            </div>
                                        </label>
                                        <input type="number" name="items[{{ $item->id }}][quantity]" 
                                            min="1" 
                                            value="{{ $orderItem ? $orderItem->quantity : 1 }}" 
                                            class="form-control quantity-input ms-2" 
                                            style="width: 70px;" 
                                            {{ $orderItem ? '' : 'disabled' }}>
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
    const isAdmin = @json(auth()->check() && auth()->user()->isAdmin());

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
});
</script>
@endsection