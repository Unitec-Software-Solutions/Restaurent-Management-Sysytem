<!-- resources/views/orders/takeaway/summary.blade.php -->
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden transform transition-all duration-300 card">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-primary to-secondary py-6 px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                        <i class="fas fa-receipt text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">Takeaway Order Summary</h2>
                        <p class="text-blue-100">Complete order details</p>
                    </div>
                </div>
                <button id="print-button" class="bg-white text-primary font-medium py-2 px-4 rounded-lg flex items-center hover:bg-opacity-90 transition print:hidden">
                    <i class="fas fa-print mr-2"></i> Print Receipt
                </button>
            </div>
        </div>

        <!-- Order Info -->
        <div class="p-6">
            <!-- Success Alert -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-green-800 font-medium">Order confirmed successfully!</h3>
                        <p class="mt-1 text-green-700">Order ID: {{ $order->takeaway_id }}</p>
                    </div>
                </div>
            </div>

            <!-- Order and Customer Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Order Details -->
                <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice mr-2 text-primary"></i> Order Details
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order ID:</span>
                            <span class="font-medium">{{ $order->takeaway_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Branch:</span>
                            <span class="font-medium">{{ $order->branch->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order Type:</span>
                            <span class="font-medium">Takeaway</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order Date:</span>
                            <span class="font-medium">
                                @if($order->created_at instanceof \Carbon\Carbon)
                                    {{ $order->created_at->format('M j, Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pickup Time:</span>
                            <span class="font-medium text-accent">
                                @if($order->order_time instanceof \Carbon\Carbon)
                                    {{ $order->order_time->format('M j, Y H:i') }}
                                @elseif(!empty($order->order_time))
                                    {{ \Carbon\Carbon::parse($order->order_time)->format('M j, Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between mt-4 pt-4 border-t border-gray-200">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Ready for pickup</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-primary"></i> Customer Details
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium">{{ $order->customer_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium">{{ $order->customer_phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-basket-shopping mr-2 text-primary"></i> Order Items
            </h3>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 mr-3">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-utensils text-primary"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $item->menuItem->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full">{{ $item->quantity }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900">
                                    LKR {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-gray-900">
                                    LKR {{ number_format($item->total_price, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Order Summary -->
                <div class="bg-gray-50 px-6 py-4">
                    <div class="max-w-md ml-auto">
                        <div class="flex justify-between py-1.5">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">LKR {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-gray-600">Tax (10%):</span>
                            <span class="font-medium">LKR {{ number_format($order->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-lg font-bold mt-2 pt-2 border-t border-gray-300">
                            <span class="text-gray-800">Total:</span>
                            <span class="text-primary">LKR {{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="p-6 border-t border-gray-200 bg-gray-50 print:hidden">
                <div class="flex flex-col sm:flex-row justify-between space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('orders.takeaway.edit', $order->id) }}" class="flex-1 py-3 px-4 bg-secondary hover:bg-blue-800 text-white font-medium rounded-lg flex items-center justify-center transition">
                        <i class="fas fa-edit mr-2"></i> Update Order
                    </a>
                    <a href="{{ route('orders.takeaway.create') }}" class="flex-1 py-3 px-4 bg-success hover:bg-green-600 text-white font-medium rounded-lg flex items-center justify-center transition">
                        <i class="fas fa-plus mr-2"></i> Add Another Order
                    </a>
                    <form action="{{ route('orders.takeaway.destroy', $order->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-3 px-4 bg-white border border-gray-300 hover:bg-gray-100 text-danger font-medium rounded-lg flex items-center justify-center transition">
                            <i class="fas fa-trash mr-2"></i> Delete Order
                        </button>
                    </form>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.orders.takeaway.index') }}" class="flex items-center justify-center text-gray-600 hover:text-gray-800 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Takeaway Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Print functionality
    document.getElementById('print-button').addEventListener('click', function() {
        window.print();
    });

    // Adding simple animations
    document.addEventListener('DOMContentLoaded', function() {
        const card = document.querySelector('.card');
        card.classList.add('opacity-0', 'scale-95', '-translate-y-2');
        
        setTimeout(() => {
            card.classList.add('transition-all', 'duration-500', 'ease-out');
            card.classList.remove('opacity-0', 'scale-95', '-translate-y-2');
            card.classList.add('opacity-100', 'scale-100');
        }, 100);
    });
</script>
@endsection