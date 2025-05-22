{{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Place New Order</h1>
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

                <form method="POST" action="{{ route('orders.store') }}">
                    @csrf
                    
                    @if($reservation)
                        <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                    @else
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Customer Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                    <input type="text" 
                                           name="customer_name" 
                                           id="customer_name" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           required>
                                </div>
                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="tel" 
                                           name="customer_phone" 
                                           id="customer_phone" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           required>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="order_type" class="block text-sm font-medium text-gray-700 mb-1">Order Type</label>
                                    <select name="order_type" 
                                            id="order_type" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                        <option value="dine_in_online_scheduled">Dine-in Online Scheduled</option>
                                        <option value="dine_in_walk_in_scheduled">Dine-in Walk-in Scheduled</option>
                                        <option value="takeaway">Takeaway</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Menu Items</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($menuItems as $item)
                            <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <input type="checkbox" 
                                                   name="items[{{ $item->id }}][item_id]" 
                                                   value="{{ $item->id }}" 
                                                   id="item{{ $item->id }}" 
                                                   class="mt-1 focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded item-check"
                                                   data-item-id="{{ $item->id }}">
                                            <label for="item{{ $item->id }}" class="font-medium text-gray-700">{{ $item->name }}</label>
                                        </div>
                                        <div class="ml-6 text-sm text-gray-600">
                                            Rs. {{ number_format($item->selling_price, 2) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <input type="number"
                                               min="1"
                                               value="1"
                                               class="w-20 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 item-qty"
                                               data-item-id="{{ $item->id }}"
                                               disabled>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-8">
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Place Order
                        </button>
                        <a href="{{ url()->previous() }}" 
                           class="text-gray-600 hover:text-gray-800 font-medium">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
            }
        });
    });
    // On form submit, disable all unchecked checkboxes so only checked items are submitted
    const form = document.querySelector('form');
    form.addEventListener('submit', function() {
        document.querySelectorAll('.item-check').forEach(function(checkbox) {
            if (!checkbox.checked) {
                checkbox.disabled = true;
            }
        });
    });
});
</script>
@endsection