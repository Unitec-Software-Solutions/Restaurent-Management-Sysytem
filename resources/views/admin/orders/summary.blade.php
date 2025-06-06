<!-- resources/views/orders/takeaway/summary.blade.php -->
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8 bg-gray-50">
    <!-- Receipt Card -->
    <div class="max-w-4xl mx-auto bg-white rounded-xl overflow-hidden shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-700 py-6 px-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">Order Summary</h1>
                    <p class="text-blue-100 mt-1">Thank you for your order!</p>
                </div>
                <div class="bg-white/10 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Order Information</h2>
                    <div class="space-y-3">
                        <div class="flex">
                            <div class="w-32 text-gray-500">Order ID</div>
                            <div>: {{ $order->takeaway_id }}</div>
                        </div>
                        <div class="flex">
                            <div class="w-32 text-gray-500">Branch</div>
                            <div>: {{ $order->branch->name }}</div>
                        </div>
                        <div class="flex">
                            <div class="w-32 text-gray-500">Status</div>
                            <div>: <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Ready for pickup</span></div>
                        </div>
                        <div class="flex">
                            <div class="w-32 text-gray-500">Pickup Time</div>
                            <div>: 
                                @if($order->order_time instanceof \Carbon\Carbon)
                                    {{ $order->order_time->format('M j, Y - H:i') }}
                                @elseif(!empty($order->order_time))
                                    {{ \Carbon\Carbon::parse($order->order_time)->format('M j, Y - H:i') }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Customer Information</h2>
                    <div class="space-y-3">
                        <div class="flex">
                            <div class="w-32 text-gray-500">Name</div>
                            <div>: {{ $order->customer_name }}</div>
                        </div>
                        <div class="flex">
                            <div class="w-32 text-gray-500">Phone</div>
                            <div>: {{ $order->customer_phone }}</div>
                        </div>
                        <div class="flex">
                            <div class="w-32 text-gray-500">Order Type</div>
                            <div>: <span class="text-blue-600">Takeaway</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mt-10">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Order Items</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->menuItem->name }}</div>
                                            @if($item->special_requests)
                                                <div class="text-sm text-gray-500">{{ $item->special_requests }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">LKR {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">LKR {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="mt-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-right text-gray-600">Subtotal:</div>
                        <div class="font-medium">LKR {{ number_format($order->subtotal, 2) }}</div>
                        <div class="text-right text-gray-600">Tax (10%):</div>
                        <div class="font-medium">LKR {{ number_format($order->tax, 2) }}</div>
                        @if($order->discount > 0)
                        <div class="text-right text-gray-600">Discount:</div>
                        <div class="font-medium text-green-600">- LKR {{ number_format($order->discount, 2) }}</div>
                        @endif
                    </div>
                    <div class="border-b border-dashed border-gray-200 my-4"></div>
                    <div class="flex justify-between items-center pt-2">
                        <div class="text-lg font-semibold">Total Amount:</div>
                        <div class="text-2xl font-bold text-blue-600">LKR {{ number_format($order->total, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-wrap gap-3 no-print">
                <a href="{{ route('orders.takeaway.edit', $order->id) }}" class="flex-1 md:flex-none bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i> Update Order
                </a>
                <a href="{{ route('orders.takeaway.create') }}" class="flex-1 md:flex-none bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i> Add Another Order
                </a>
                <form action="{{ route('orders.takeaway.destroy', $order->id) }}" method="POST" class="flex-1 md:flex-none">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i> Delete Order
                    </button>
                </form>
                <button onclick="window.print()" class="flex-1 md:flex-none bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-print mr-2"></i> Print Receipt
                </button>
                <a href="{{ route('admin.orders.takeaway.index') }}" class="flex-1 md:flex-none bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                <div>Thank you for choosing our restaurant!</div>
                <div>For any inquiries, call: {{ $order->branch->phone ?? '+94 11 234-5678' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style media="print">
    body {
        background: white;
    }
    .bg-gradient-to-r {
        background: #3b82f6 !important;
    }
    .shadow-\[0_10px_30px_-5px_rgba\(0\,0\,0\,0\.2\)\] {
        box-shadow: none !important;
    }
    .no-print {
        display: none !important;
    }
    @page {
        size: auto;
        margin: 0mm;
    }
</style>
@endsection