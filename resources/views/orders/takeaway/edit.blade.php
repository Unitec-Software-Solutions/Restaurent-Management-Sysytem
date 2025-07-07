@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-blue-600 px-6 py-4 text-white">
            <h2 class="text-2xl font-bold">Edit Takeaway Order #{{ $order->order_number ?? $order->id }}</h2>
            <p class="text-blue-100 mt-1">
                Status: <span class="font-semibold">{{ ucfirst($order->status) }}</span> | 
                Total: <span class="font-semibold">LKR {{ number_format($order->total_amount ?? $order->total, 2) }}</span>
            </p>
        </div>

        <div class="p-6">
            <form action="{{ route('orders.takeaway.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-2">
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                        <select name="branch_id" id="branch_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" 
                                    {{ $order->branch_id == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="order_time" class="block text-sm font-medium text-gray-700">Pickup Time</label>
                        <input type="datetime-local" name="order_time" id="order_time" 
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               value="{{ old('order_time', $order->order_time ? \Carbon\Carbon::parse($order->order_time)->format('Y-m-d\TH:i') : '') }}" 
                               required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-2">
                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" 
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               value="{{ old('customer_name', $order->customer_name) }}" 
                               required>
                    </div>
                    <div class="space-y-2">
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
                        <input type="tel" name="customer_phone" id="customer_phone" 
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               value="{{ old('customer_phone', $order->customer_phone) }}" 
                               required>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">Order Items</h4>
                    <div id="cart-items" class="space-y-3">
                        @foreach($cart['items'] as $index => $item)
                            <div class="cart-item grid grid-cols-12 gap-3 items-center bg-gray-50 p-3 rounded-lg">
                                <div class="col-span-12 md:col-span-6">
                                    <select name="items[{{ $index }}][item_id]" class="item-select block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $menuItem)
                                            <option value="{{ $menuItem->id }}"
                                                {{ $item['item_id'] == $menuItem->id ? 'selected' : '' }}>
                                                {{ $menuItem->name }} - LKR {{ number_format($menuItem->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-6 md:col-span-3">
                                    <input type="number" name="items[{{ $index }}][quantity]" 
                                           class="quantity block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                           min="1" 
                                           value="{{ $item['quantity'] }}" 
                                           required>
                                </div>
                                <div class="col-span-6 md:col-span-3">
                                    <button type="button" class="remove-item w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-item" class="mt-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Item
                    </button>
                </div>

                <div class="mt-8 bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">Order Summary</h4>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">Subtotal</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" id="cart-subtotal">LKR {{ number_format($cart['subtotal'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">Tax (10%)</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" id="cart-tax">LKR {{ number_format($cart['tax'], 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-gray-900 sm:pl-6">Total</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-gray-900" id="cart-total">LKR {{ number_format($cart['total'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <a href="{{ route('orders.takeaway.summary', $order->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Back
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                        </svg>
                        Update Order
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
                <div class="cart-item grid grid-cols-12 gap-3 items-center bg-gray-50 p-3 rounded-lg">
                    <div class="col-span-12 md:col-span-6">
                        <select name="items[${index}][item_id]" class="item-select block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                            <option value="">Select Item</option>
                            @foreach($items as $menuItem)
                                <option value="{{ $menuItem->id }}">
                                    {{ $menuItem->name }} - LKR {{ number_format($menuItem->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <input type="number" name="items[${index}][quantity]" 
                               class="quantity block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               min="1" value="1" required>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <button type="button" class="remove-item w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Remove
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newItem);
        });

        // Remove item
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
                const itemToRemove = e.target.closest('.remove-item') ? e.target.closest('.cart-item') : e.target.closest('.cart-item');
                itemToRemove.remove();
                updateCartTotals();
            }
        });

        // You would need to implement the updateCartTotals() function
        // to recalculate prices when items are added/removed or quantities change
        function updateCartTotals() {
            // Implementation would depend on your backend structure
            // This might involve an AJAX call to recalculate totals
        }
    });
</script>
@endsection