@extends('layouts.guest')

@section('title', 'Track Your Order')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Track Your Order</h1>
            <p class="text-gray-600">Stay updated on your order's progress</p>
        </div>

        @if(isset($order))
        <!-- Order Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Order #{{ $order->order_number ?? $order->id }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full order-status
                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $order->status === 'preparing' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Order Progress</h3>
                
                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    
                    <!-- Timeline Steps -->
                    <div class="space-y-6">
                        <!-- Order Placed -->
                        <div class="relative flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center relative z-10">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium text-gray-900">Order Placed</h4>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('g:i A') }}</p>
                            </div>
                        </div>

                        <!-- Order Confirmed -->
                        <div class="relative flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center relative z-10
                                {{ in_array($order->status, ['confirmed', 'preparing', 'ready', 'completed']) ? 'bg-green-500' : 'bg-gray-300' }}">
                                <i class="fas fa-{{ in_array($order->status, ['confirmed', 'preparing', 'ready', 'completed']) ? 'check' : 'clock' }} text-white text-sm"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium {{ in_array($order->status, ['confirmed', 'preparing', 'ready', 'completed']) ? 'text-gray-900' : 'text-gray-500' }}">
                                    Order Confirmed
                                </h4>
                                <p class="text-sm text-gray-500">
                                    {{ in_array($order->status, ['confirmed', 'preparing', 'ready', 'completed']) ? $order->updated_at->format('g:i A') : 'Pending confirmation' }}
                                </p>
                            </div>
                        </div>

                        <!-- Preparing -->
                        <div class="relative flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center relative z-10
                                {{ in_array($order->status, ['preparing', 'ready', 'completed']) ? 'bg-orange-500' : 'bg-gray-300' }}">
                                <i class="fas fa-{{ in_array($order->status, ['preparing', 'ready', 'completed']) ? 'utensils' : 'clock' }} text-white text-sm"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium {{ in_array($order->status, ['preparing', 'ready', 'completed']) ? 'text-gray-900' : 'text-gray-500' }}">
                                    Preparing Your Order
                                </h4>
                                <p class="text-sm text-gray-500">
                                    {{ $order->status === 'preparing' ? 'Currently being prepared' : (in_array($order->status, ['ready', 'completed']) ? 'Preparation completed' : 'Not started yet') }}
                                </p>
                            </div>
                        </div>

                        <!-- Ready for Pickup -->
                        <div class="relative flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center relative z-10
                                {{ in_array($order->status, ['ready', 'completed']) ? 'bg-green-500' : 'bg-gray-300' }}">
                                <i class="fas fa-{{ in_array($order->status, ['ready', 'completed']) ? 'hand-holding' : 'clock' }} text-white text-sm"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium {{ in_array($order->status, ['ready', 'completed']) ? 'text-gray-900' : 'text-gray-500' }}">
                                    Ready for {{ $order->getOrderTypeLabel() }}
                                </h4>
                                <p class="text-sm text-gray-500">
                                    {{ $order->status === 'ready' ? 'Your order is ready!' : ($order->status === 'completed' ? 'Order completed' : 'In preparation') }}
                                </p>
                                @if($order->status === 'ready' && $order->pickup_time)
                                <p class="text-sm text-indigo-600 font-medium">
                                    Pickup by: {{ \Carbon\Carbon::parse($order->pickup_time)->format('g:i A') }}
                                </p>
                                @endif
                            </div>
                        </div>

                        @if($order->status === 'cancelled')
                        <!-- Cancelled -->
                        <div class="relative flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center relative z-10">
                                <i class="fas fa-times text-white text-sm"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium text-red-700">Order Cancelled</h4>
                                <p class="text-sm text-gray-500">{{ $order->updated_at->format('g:i A') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($order->status === 'ready')
                <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center">
                        <i class="fas fa-bell text-green-600 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-green-800">Your order is ready!</h4>
                            <p class="text-sm text-green-700">Please come to the restaurant to pick up your order.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Order Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Items -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
                <div class="space-y-3">
                    @foreach($order->orderItems as $item)
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $item->menuItem->name }}</h4>
                            <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                            @if($item->special_instructions)
                            <p class="text-xs text-gray-400 mt-1">{{ $item->special_instructions }}</p>
                            @endif
                        </div>
                        <span class="font-medium text-gray-900">${{ number_format($item->total_price, 2) }}</span>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between font-semibold text-gray-900">
                        <span>Total</span>
                        <span>${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Branch Info -->
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Details</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="text-sm text-gray-900">{{ $order->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="text-sm text-gray-900">{{ $order->customer_phone }}</dd>
                        </div>
                        @if($order->customer_email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">{{ $order->customer_email }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Branch Info -->
                @if($order->branch)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $order->getOrderTypeLabel() }} Location</h3>
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-900">{{ $order->branch->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $order->branch->address }}</p>
                        @if($order->branch->phone)
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-phone mr-1"></i>
                            {{ $order->branch->phone }}
                        </p>
                        @endif
                        @if($order->branch->opening_time && $order->branch->closing_time)
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ $order->branch->opening_time }} - {{ $order->branch->closing_time }}
                        </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        @else
        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Find Your Order</h2>
            <form method="GET" action="{{ route('guest.order.track') }}" class="space-y-4">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Order Number or Phone Number
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="order_number" 
                               name="order_number" 
                               value="{{ request('order_number') }}"
                               placeholder="Enter order number or phone number"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pl-10"
                               required>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Enter your order number (e.g., ORD123) or the phone number used for the order
                    </p>
                </div>
                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Track Order
                </button>
            </form>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @if(isset($order))
            <a href="{{ route('guest.menu.view', ['branchId' => $order->branch_id]) }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-utensils mr-2"></i>
                Order Again
            </a>
            @endif
            <a href="{{ route('guest.menu.branch-selection') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>
                New Order
            </a>
        </div>

        <!-- Help Section -->
        @if(isset($order))
        <div class="text-center mt-8 p-6 bg-blue-50 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">Need Help with Your Order?</h3>
            <p class="text-sm text-gray-600 mb-4">
                Contact the restaurant directly if you have any questions or concerns.
            </p>
            @if($order->branch && $order->branch->phone)
            <a href="tel:{{ $order->branch->phone }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                <i class="fas fa-phone mr-2"></i>
                {{ $order->branch->phone }}
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(isset($order) && in_array($order->status, ['pending', 'confirmed', 'preparing']))
    // Auto-refresh for active orders every 30 seconds
    setInterval(function() {
        fetch(`/guest/order/{{ $order->order_number ?? $order->id }}/status`)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    const currentStatus = document.querySelector('.order-status').textContent.trim().toLowerCase();
                    if (currentStatus !== data.status.toLowerCase()) {
                        location.reload();
                    }
                }
            })
            .catch(error => console.log('Status check failed:', error));
    }, 30000);
    @endif

    // Auto-focus on search input if no order is displayed
    @if(!isset($order))
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('order_number');
        if (searchInput) {
            searchInput.focus();
        }
    });
    @endif
</script>
@endpush
