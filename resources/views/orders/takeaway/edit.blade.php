@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Edit Takeaway Order #{{ $order->takeaway_id }}</h2>
        </div>

        <div class="card-body">
            <form action="{{ route('orders.takeaway.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="branch_id">Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" 
                                        {{ $order->branch_id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="order_time">Pickup Time</label>
                            <input type="datetime-local" name="order_time" id="order_time" 
                                   class="form-control" 
                                   value="{{ old('order_time', $order->order_time->format('Y-m-d\TH:i')) }}" 
                                   required>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" name="customer_name" id="customer_name" 
                                   class="form-control" 
                                   value="{{ old('customer_name', $order->customer_name) }}" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_phone">Customer Phone</label>
                            <input type="tel" name="customer_phone" id="customer_phone" 
                                   class="form-control" 
                                   value="{{ old('customer_phone', $order->customer_phone) }}" 
                                   required>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3">Order Items</h4>
                <div id="cart-items">
                    @foreach($cart['items'] as $index => $item)
                        <div class="cart-item row mb-2">
                            <div class="col-md-5">
                                <select name="items[{{ $index }}][item_id]" class="form-control item-select" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $menuItem)
                                        <option value="{{ $menuItem->id }}"
                                            {{ $item['item_id'] == $menuItem->id ? 'selected' : '' }}>
                                            {{ $menuItem->name }} - LKR {{ number_format($menuItem->selling_price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[{{ $index }}][quantity]" 
                                       class="form-control quantity" 
                                       min="1" 
                                       value="{{ $item['quantity'] }}" 
                                       required>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-danger remove-item">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-item" class="btn btn-secondary mt-2">
                    <i class="fas fa-plus"></i> Add Item
                </button>

                <div class="mt-4">
                    <h4>Order Summary</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>Subtotal</th>
                            <td id="cart-subtotal">LKR {{ number_format($cart['subtotal'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>Tax (10%)</th>
                            <td id="cart-tax">LKR {{ number_format($cart['tax'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td id="cart-total">LKR {{ number_format($cart['total'], 2) }}</td>
                        </tr>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('orders.takeaway.summary', $order->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add new item
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('cart-items');
            const index = container.children.length;
            const newItem = `
                <div class="cart-item row mb-2">
                    <div class="col-md-5">
                        <select name="items[${index}][item_id]" class="form-control item-select" required>
                            <option value="">Select Item</option>
                            @foreach($items as $menuItem)
                                <option value="{{ $menuItem->id }}">
                                    {{ $menuItem->name }} - LKR {{ number_format($menuItem->selling_price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="items[${index}][quantity]" 
                               class="form-control quantity" min="1" value="1" required>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-danger remove-item">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newItem);
        });

        // Remove item
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('.cart-item').remove();
                updateCartTotals();
            }
        });
    });
</script>
@endsection