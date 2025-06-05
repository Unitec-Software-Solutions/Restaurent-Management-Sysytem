@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-utensils me-2"></i>
            <h2 class="mb-0">Create Takeaway Order</h2>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.orders.takeaway.store') }}">
                @csrf
                <input type="hidden" name="order_type" value="{{ request('type', 'takeaway_walk_in_demand') }}">

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6 mb-4">
                        <div class="mb-4">
                            <h4 class="section-title border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Order Information
                            </h4>
                            
                            @if(auth('admin')->check())
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Order Type</label>
                                <select name="order_type" class="form-select">
                                    <option value="takeaway_walk_in_demand" selected>In-House</option>
                                    <option value="takeaway_in_call_scheduled">In-Call</option>
                                </select>
                            </div>
                            @endif

                            <div class="form-group mb-3" @if(auth('admin')->check()) style="display:none" @endif>
                                <label class="form-label fw-bold">Select Outlet</label>
                                <select name="branch_id" class="form-select" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $defaultBranch == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Pickup Time</label>
                                <input type="datetime-local" name="order_time" 
                                    value="{{ old('order_time', now()->format('Y-m-d\TH:i')) }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="form-control"
                                    required>
                            </div>
                        </div>

                        <div>
                            <h4 class="section-title border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-2 text-primary"></i>Customer Information
                            </h4>
                            
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" required value="{{ old('customer_name', 'Not Provided') }}">
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="customer_phone" class="form-control" required
                                        pattern="[0-9]{10,15}" 
                                        title="Please enter a valid 10-15 digit phone number"
                                        value="{{ old('customer_phone', $defaultBranchPhone ?? '') }}">
                                </div>
                                <small class="form-text text-muted">We'll notify you about your order status</small>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6 mb-4">
                        <div class="mb-4">
                            <h4 class="section-title border-bottom pb-2 mb-3">
                                <i class="fas fa-list-ul me-2 text-primary"></i>Menu Items
                            </h4>
                            
                            <div class="menu-items-container" style="max-height: 400px; overflow-y: auto;">
                                @foreach($items as $item)
                                <div class="card mb-2 menu-item-card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="form-check me-3">
                                            <input class="form-check-input item-check" type="checkbox" 
                                                name="items[{{ $item->id }}][item_id]" 
                                                value="{{ $item->id }}" 
                                                id="item_{{ $item->id }}">
                                        </div>
                                        <label class="form-check-label flex-grow-1" for="item_{{ $item->id }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-medium">{{ $item->name }}</div>
                                                    @if($item->description)
                                                    <small class="text-muted">{{ $item->description }}</small>
                                                    @endif
                                                </div>
                                                <span class="text-primary">LKR {{ number_format($item->selling_price, 2) }}</span>
                                            </div>
                                        </label>
                                        <input type="number" name="items[{{ $item->id }}][quantity]" 
                                            min="1" value="1" class="form-control quantity-input ms-2" 
                                            style="width: 70px;" disabled>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="order-summary" class="mt-4 p-3 border rounded bg-light">
                            <h5 class="mb-3"><i class="fas fa-receipt me-2 text-secondary"></i>Order Summary</h5>
                            <ul class="list-unstyled" id="summary-list">
                                <li>No items selected</li>
                            </ul>
                            <div class="fw-bold mt-2">Total: LKR <span id="summary-total">0.00</span></div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check-circle me-2"></i> Place Order
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
    .menu-item-card {
        transition: all 0.2s ease-in-out;
    }
    .menu-item-card:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }
    .quantity-input:disabled {
        background-color: #e9ecef;
        opacity: 1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAdmin = @json(auth('admin')->check());

    const orderTimeInput = document.querySelector('input[name="order_time"]');
    if (orderTimeInput) {
        const now = new Date();
        const pad = n => n.toString().padStart(2, '0');
        const formatted = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        orderTimeInput.value = formatted;
        orderTimeInput.min = formatted;
    }

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

    function updateSummary() {
        const summaryList = document.getElementById('summary-list');
        const summaryTotal = document.getElementById('summary-total');
        let total = 0;
        let listItems = '';

        document.querySelectorAll('.item-check:checked').forEach(checkbox => {
            const card = checkbox.closest('.menu-item-card');
            const name = card.querySelector('.form-check-label .fw-medium').innerText;
            const price = parseFloat(card.querySelector('.text-primary').innerText.replace('LKR', '').trim());
            const qtyInput = card.querySelector('.quantity-input');
            const quantity = parseInt(qtyInput.value || 1);
            total += price * quantity;
            listItems += `<li>${name} x ${quantity} - LKR ${(price * quantity).toFixed(2)}</li>`;
        });

        summaryList.innerHTML = listItems || '<li>No items selected</li>';
        summaryTotal.innerText = total.toFixed(2);
    }

    document.querySelectorAll('.item-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.menu-item-card').querySelector('.quantity-input');
            quantityInput.disabled = !this.checked;
            if (!this.checked) quantityInput.value = 1;
            updateSummary();
        });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', updateSummary);
        input.addEventListener('input', updateSummary);
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const phoneInput = document.querySelector('input[name="customer_phone"]');
        if (!phoneInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            phoneInput.focus();
        }
    });

    updateSummary(); // Initial call
});
</script>
@endsection
