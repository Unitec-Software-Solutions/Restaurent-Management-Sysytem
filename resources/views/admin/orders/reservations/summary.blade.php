@extends('layouts.admin')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Success Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-green-600 to-emerald-700">
                    <div class="flex items-center">
                        <div class="bg-white/20 p-3 rounded-xl mr-4">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Reservation Order Summary</h1>
                            <p class="text-green-100 mt-1">Order created successfully for reservation</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Success Alert -->
                    @if(session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <div>
                                <h3 class="text-green-800 font-semibold">{{ session('success') }}</h3>
                                <p class="text-green-600 text-sm mt-1">Order ID: #{{ $order->id }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Order and Reservation Details Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <!-- Reservation Details -->
                        <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                            <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Reservation Details
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-blue-600 font-medium">Reservation ID:</span>
                                    <span class="text-gray-800">#{{ $reservation->id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-600 font-medium">Customer Name:</span>
                                    <span class="text-gray-800">{{ $reservation->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-600 font-medium">Phone Number:</span>
                                    <span class="text-gray-800">{{ $reservation->phone }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-600 font-medium">Date & Time:</span>
                                    <span class="text-gray-800">
                                        {{ $reservation->date ? \Carbon\Carbon::parse($reservation->date)->format('M d, Y') : 'N/A' }}
                                        {{ $reservation->start_time ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-600 font-medium">Party Size:</span>
                                    <span class="text-gray-800">{{ $reservation->number_of_people ?? 'N/A' }} people</span>
                                </div>
                                @if($reservation->table)
                                <div class="flex justify-between">
                                    <span class="text-blue-600 font-medium">Table:</span>
                                    <span class="text-gray-800">{{ $reservation->table->name ?? 'Table #' . $reservation->table->id }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-receipt mr-2"></i>
                                Order Details
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Order ID:</span>
                                    <span class="text-gray-800">#{{ $order->id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Status:</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $order->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Order Type:</span>
                                    <span class="text-gray-800">{{ $order->getOrderTypeLabel() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Branch:</span>
                                    <span class="text-gray-800">{{ $order->branch->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 font-medium">Created:</span>
                                    <span class="text-gray-800">{{ $order->created_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-8">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-utensils mr-2 text-orange-500"></i>
                                Order Items
                            </h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($order->orderItems as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-utensils text-orange-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $item->menuItem->name ?? 'Item #' . $item->menu_item_id }}
                                                    </div>
                                                    @if(isset($item->menuItem->description))
                                                    <div class="text-sm text-gray-500">{{ Str::limit($item->menuItem->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                                {{ $item->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-900">
                                            LKR {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            LKR {{ number_format($item->total_price, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Totals -->
                    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-6 border border-gray-200 mb-8">
                        <div class="max-w-md ml-auto">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="text-gray-800">LKR {{ number_format($order->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax (10%):</span>
                                    <span class="text-gray-800">LKR {{ number_format($order->tax, 2) }}</span>
                                </div>
                                <div class="border-t border-gray-300 pt-3">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span class="text-gray-800">Total Amount:</span>
                                        <span class="text-blue-600">LKR {{ number_format($order->total, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons with Branching Options -->
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-directions mr-2 text-purple-500"></i>
                            Next Steps
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Submit/Complete Order -->
                            <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="submitted">
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Submit Order
                                </button>
                            </form>

                            <!-- Edit Order -->
                            <a href="{{ route('admin.orders.edit', $order) }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Order
                            </a>

                            <!-- Add Another Order -->
                            <a href="{{ route('admin.orders.create', ['reservation_id' => $reservation->id]) }}" 
                               class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Add Another Order
                            </a>

                            <!-- View All Reservation Orders -->
                            <a href="{{ route('admin.orders.index', ['reservation_id' => $reservation->id]) }}" 
                               class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-list mr-2"></i>
                                View All Orders
                            </a>
                        </div>

                        <!-- Secondary Actions -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex flex-wrap gap-3 justify-center">
                                <button onclick="window.print()" 
                                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-print mr-2"></i>
                                    Print Summary
                                </button>
                                
                                <a href="{{ route('admin.reservations.show', $reservation) }}" 
                                   class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    View Reservation
                                </a>

                                <a href="{{ route('admin.orders.dashboard') }}" 
                                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-home mr-2"></i>
                                    Order Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .print\\:hidden {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .bg-gradient-to-r,
    .bg-gradient-to-br {
        background: white !important;
        color: black !important;
    }
    
    .shadow-xl,
    .shadow-md {
        box-shadow: none !important;
    }
    
    .rounded-2xl,
    .rounded-xl,
    .rounded-lg {
        border-radius: 0 !important;
    }
    
    .border {
        border: 1px solid #ccc !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any interactive functionality here
    console.log('Reservation Order Summary loaded for Order #{{ $order->id }}');
});
</script>
@endsection
