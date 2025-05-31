@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto flex flex-row gap-8">
        <!-- Left: Edit Order Form (7/8) -->
        <div class="flex-[7_7_0%]">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Edit Order #{{ $order->id }}
                    </h1>
                    @if($order->reservation)
                        <div class="mt-2 text-gray-600">
                            <p>
                                <span class="font-medium">Customer:</span>
                                {{ optional($order->reservation->customer)->name ?? 'Not Provided' }}
                            </p>
                            <p>
                                <span class="font-medium">Table:</span>
                                {{ optional($order->reservation->table)->name ?? 'Not Provided' }}
                                ({{ optional($order->reservation->table)->capacity ?? 'N/A' }} people)
                            </p>
                            <p>
                                <span class="font-medium">Time:</span>
                                {{ $order->reservation->start_time }} - {{ $order->reservation->end_time }}
                            </p>
                        </div>
                    @endif
                </div>
                <div class="p-6">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- For reservation orders -->
                    <form method="POST" 
                          action="{{ route($order->reservation_id ? 'admin.orders.reservations.update' : 'admin.orders.takeaway.update', $order) }}">
                        @csrf
                        @method('PUT')
                        @if($order->reservation)
                            <input type="hidden" name="reservation_id" value="{{ $order->reservation->id }}">
                            <input type="hidden" name="customer_id" value="{{ $order->reservation->customer_id }}">
                            <input type="hidden" name="table_id" value="{{ $order->reservation->table_id }}">
                        @endif

                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Menu Items</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($menuItems as $item)
                                @php
                                    $existingItem = $order->orderItems->where('menu_item_id', $item->id)->first();
                                @endphp
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <input type="checkbox"
                                                    name="items[{{ $item->id }}][item_id]"
                                                    value="{{ $item->id }}"
                                                    id="item{{ $item->id }}"
                                                    class="mt-1 focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded item-check"
                                                    data-item-id="{{ $item->id }}"
                                                    {{ $existingItem ? 'checked' : '' }}>
                                                <label for="item{{ $item->id }}" class="font-medium text-gray-700">{{ $item->name }}</label>
                                            </div>
                                            <div class="ml-6 text-sm text-gray-600">
                                                Rs. {{ number_format($item->selling_price, 2) }}
                                                @if($item->description)
                                                    <p class="mt-1 text-gray-500 text-xs">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <input type="number"
                                                min="1"
                                                value="{{ $existingItem ? $existingItem->quantity : 1 }}"
                                                class="w-20 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 item-qty"
                                                data-item-id="{{ $item->id }}"
                                                name="items[{{ $item->id }}][quantity]"
                                                {{ $existingItem ? '' : 'disabled' }}>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $order->notes) }}</textarea>
                            </div>
                            <div class="flex justify-between items-center">
                                <a href="{{ route('orders.summary', $order->id) }}"
                                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                    Cancel
                                </a>
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Update Order
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Right: Cart Summary (1/8) -->
        <div class="flex-[1_1_0%] min-w-[180px]">
            <div class="bg-white shadow-md rounded-lg p-6 sticky top-8">
                <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                <div id="cart-items">
                    <p class="text-gray-500">No items added yet</p>
                </div>
                <hr class="my-3">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal">LKR 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax (10%):</span>
                    <span id="cart-tax">LKR 0.00</span>
                </div>
                <hr class="my-3">
                <div class="flex justify-between font-bold">
                    <span>Total:</span>
                    <span id="cart-total">LKR 0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable quantity fields and update cart on change
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            if (this.checked) {
                qtyInput.disabled = false;
                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');
            } else {
                qtyInput.disabled = true;
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;
            }
            updateCart();
        });
    });
    document.querySelectorAll('.item-qty').forEach(function(input) {
        input.addEventListener('input', updateCart);
    });

    // On form submit, disable unchecked checkboxes
    const form = document.getElementById('order-form');
    form.addEventListener('submit', function() {
        document.querySelectorAll('.item-check').forEach(function(checkbox) {
            if (!checkbox.checked) {
                checkbox.disabled = true;
            }
        });
    });

    // AJAX cart update
    function updateCart() {
        const items = [];
        document.querySelectorAll('.item-check:checked').forEach(function(checkbox) {
            const itemId = checkbox.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            items.push({
                item_id: itemId,
                quantity: qtyInput.value
            });
        });

        fetch('{{ route("admin.orders.update-cart") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ items: items })
        })
        .then(response => response.json())
        .then(cart => updateCartDisplay(cart));
    }

    function updateCartDisplay(cart) {
        if (cart.items.length > 0) {
            let itemsHtml = '';
            cart.items.forEach(function(item) {
                itemsHtml += `
                    <div class="flex justify-between mb-2">
                        <span>${item.name} <span class="text-xs text-gray-400">x${item.quantity}</span></span>
                        <span>LKR ${item.total.toFixed(2)}</span>
                    </div>`;
            });
            document.getElementById('cart-items').innerHTML = itemsHtml;
        } else {
            document.getElementById('cart-items').innerHTML = '<p class="text-gray-500">No items added yet</p>';
        }
        document.getElementById('cart-subtotal').textContent = 'LKR ' + cart.subtotal.toFixed(2);
        document.getElementById('cart-tax').textContent = 'LKR ' + cart.tax.toFixed(2);
        document.getElementById('cart-total').textContent = 'LKR ' + cart.total.toFixed(2);
    }

    // Initial cart update
    updateCart();
});
</script>
@endsection