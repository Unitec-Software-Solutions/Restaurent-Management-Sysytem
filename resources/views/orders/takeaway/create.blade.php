@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Takeaway Order</h2>
    
    <form method="POST" action="{{ route('orders.takeaway.store') }}">
        @csrf
        
        @if(auth()->check() && auth()->user()->isAdmin())
        <!-- Admin-only order type selection -->
        <div class="form-group">
            <label>Order Type</label>
            <select name="order_type" class="form-control">
                <option value="takeaway_walk_in_demand" selected>In-House</option>
                <option value="takeaway_in_call_scheduled">In-Call</option>
            </select>
        </div>
        @endif

        <!-- Branch selection (hidden for admins) -->
        <div class="form-group" @if(auth()->check() && auth()->user()->isAdmin()) style="display:none" @endif>
            <label>Select Outlet</label>
            <select name="branch_id" class="form-control" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" 
                        {{ $defaultBranch == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Order time (pre-filled for admins) -->
        <div class="form-group">
            <label>Pickup Time</label>
            <input type="datetime-local" name="order_time" 
                   value="{{ auth()->check() && auth()->user()->isAdmin() ? now()->format('Y-m-d\TH:i') : '' }}"
                   min="{{ now()->format('Y-m-d\TH:i') }}"
                   class="form-control"
                   required>
        </div>

        <!-- Customer Info -->
        <div class="form-group">
            <label>Customer Name (Optional)</label>
            <input type="text" name="customer_name" class="form-control">
        </div>

        <div class="form-group">
            <label>Phone Number (Optional)</label>
            <input type="tel" name="customer_phone" class="form-control">
        </div>

        <!-- Menu Items -->
        <h3>Select Items</h3>
        <div class="menu-items">
            @foreach($items as $item)
            <div class="menu-item">
                <input type="checkbox" name="items[{{ $item->id }}][item_id]" 
                       value="{{ $item->id }}" class="item-check">
                <span>{{ $item->name }}</span>
                <input type="number" name="items[{{ $item->id }}][quantity]" 
                       min="1" value="1" class="quantity-input" disabled>
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Place Order</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAdmin = @json(auth()->check() && auth()->user()->isAdmin());

    if (isAdmin) {
        // Set default time to now + 15 minutes
        const now = new Date();
        now.setMinutes(now.getMinutes() + 15);
        document.querySelector('input[name="order_time"]').value =
            now.toISOString().slice(0, 16);

        // Toggle between in-house/in-call
        document.querySelector('select[name="order_type"]').addEventListener('change', function() {
            const timeField = document.querySelector('input[name="order_time"]');
            if (this.value === 'takeaway_in_call_scheduled') {
                // Set default to now + 30 mins for phone orders
                const phoneTime = new Date();
                phoneTime.setMinutes(phoneTime.getMinutes() + 30);
                timeField.value = phoneTime.toISOString().slice(0, 16);
            } else {
                // Reset to now + 15 mins for in-house
                const inHouseTime = new Date();
                inHouseTime.setMinutes(inHouseTime.getMinutes() + 15);
                timeField.value = inHouseTime.toISOString().slice(0, 16);
            }
        });
    }

    // Enable/disable quantity input based on checkbox
    document.querySelectorAll('.item-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.menu-item').querySelector('input[type="number"]');
            quantityInput.disabled = !this.checked;
            if (!this.checked) quantityInput.value = 1;
        });
    });
});
</script>
@endsection