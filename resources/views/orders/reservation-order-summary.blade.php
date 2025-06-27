@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                    <div class="flex items-center">
                        <div class="bg-white/20 p-3 rounded-xl mr-4">
                            <i class="fas fa-utensils text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Reservation Order Summary</h1>
                            <p class="text-blue-100 mt-1">Your dine-in order has been confirmed</p>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- Main Content -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <!-- Order and Reservation Details Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Order Details -->
                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-receipt mr-2"></i>
                            Order Details
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-blue-600 font-medium">Order ID:</span>
                                <span class="text-gray-800">#{{ $order->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600 font-medium">Order Type:</span>
                                <span class="text-gray-800">Dine-In (Reservation)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600 font-medium">Branch:</span>
                                <span class="text-gray-800">{{ $order->branch->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600 font-medium">Status:</span>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600 font-medium">Order Date:</span>
                                <span class="text-gray-800">{{ $order->created_at->format('M j, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    @if($order->reservation)
                    <div class="bg-purple-50 rounded-xl p-6 border border-purple-200">
                        <h3 class="text-lg font-semibold text-purple-800 mb-4 flex items-center">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Reservation Details
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-purple-600 font-medium">Reservation ID:</span>
                                <span class="text-gray-800">#{{ $order->reservation->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-purple-600 font-medium">Customer Name:</span>
                                <span class="text-gray-800">{{ $order->reservation->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-purple-600 font-medium">Phone:</span>
                                <span class="text-gray-800">{{ $order->reservation->phone }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-purple-600 font-medium">Date & Time:</span>
                                <span class="text-gray-800">
                                    {{ \Carbon\Carbon::parse($order->reservation->date)->format('M j, Y') }} 
                                    {{ \Carbon\Carbon::parse($order->reservation->start_time)->format('H:i') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-purple-600 font-medium">Guests:</span>
                                <span class="text-gray-800">{{ $order->reservation->guest_count }}</span>
                            </div>
                            @if($order->reservation->table)
                            <div class="flex justify-between">
                                <span class="text-purple-600 font-medium">Table:</span>
                                <span class="text-gray-800">{{ $order->reservation->table->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Order Items -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-list mr-2 text-blue-600"></i>
                        Order Items
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($order->items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->menuItem->name }}</div>
                                        @if($item->menuItem->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($item->menuItem->description, 50) }}</div>
                                        @endif
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

                <!-- Order Summary -->
                <div class="bg-gray-50 rounded-xl p-6 mb-8">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="text-gray-800">LKR {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->service_charge > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Service Charge (10%):</span>
                            <span class="text-gray-800">LKR {{ number_format($order->service_charge, 2) }}</span>
                        </div>
                        @endif
                        @if($order->tax > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (13%):</span>
                            <span class="text-gray-800">LKR {{ number_format($order->tax, 2) }}</span>
                        </div>
                        @endif
                        @if($order->discount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Discount:</span>
                            <span class="text-red-600">- LKR {{ number_format($order->discount, 2) }}</span>
                        </div>
                        @endif
                        <hr class="border-gray-300">
                        <div class="flex justify-between text-lg font-semibold">
                            <span class="text-gray-800">Total:</span>
                            <span class="text-blue-600">LKR {{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    @if($order->status === 'draft' || $order->status === 'active')
                    <!-- Order is still editable -->
                    <div class="flex flex-col md:flex-row gap-3">
                        <a href="{{ route('orders.edit', $order) }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-edit mr-2"></i> Update Order
                        </a>
                        
                        <form action="{{ route('orders.submit', $order) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center transition-colors">
                                <i class="fas fa-check mr-2"></i> Submit Order
                            </button>
                        </form>
                        
                        <a href="{{ route('orders.create', ['reservation' => $order->reservation_id]) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-plus mr-2"></i> Add Another Order
                        </a>
                    </div>
                    @else
                    <!-- Order is submitted -->
                    <div class="text-center">
                        <p class="text-green-600 font-semibold text-lg mb-2">
                            <i class="fas fa-check-circle mr-2"></i>Order Submitted Successfully!
                        </p>
                        <p class="text-gray-600">Your order is being prepared. You can track its progress below.</p>
                    </div>
                    @endif

                    <!-- Navigation buttons -->
                    <div class="flex gap-3">
                        <a href="{{ route('orders.index', ['phone' => $order->customer_phone]) }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-list mr-2"></i> View All Orders
                        </a>
                        
                        @if($order->reservation)
                        <a href="{{ route('reservations.show', $order->reservation) }}" 
                           class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-calendar mr-2"></i> View Reservation
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Print functionality
    function printOrder() {
        window.print();
    }
    
    // Confirmation for order submission
    document.addEventListener('DOMContentLoaded', function() {
        const submitForm = document.querySelector('form[action*="submit"]');
        if (submitForm) {
            submitForm.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to submit this order? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush
