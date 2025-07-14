@extends('layouts.guest')

@section('title', 'Order Confirmation')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
            <p class="text-gray-600">Thank you for your order. We'll prepare it with care.</p>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Order #{{ $order->order_number ?? $order->id }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $order->status === 'preparing' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Details -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Details</h3>
                        <dl class="space-y-2">
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-24">Name:</dt>
                                <dd class="text-sm text-gray-900">{{ $order->customer_name }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-24">Phone:</dt>
                                <dd class="text-sm text-gray-900">{{ $order->customer_phone }}</dd>
                            </div>
                            @if($order->customer_email)
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-24">Email:</dt>
                                <dd class="text-sm text-gray-900">{{ $order->customer_email }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Order Details -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Details</h3>
                        <dl class="space-y-2">
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-24">Type:</dt>
                                <dd class="text-sm text-gray-900">{{ $order->getOrderTypeLabel() }}</dd>
                            </div>
                            @if($order->pickup_time)
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-24">Pickup:</dt>
                                <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($order->pickup_time)->format('M d, Y \a\t g:i A') }}</dd>
                            </div>
                            @endif
                            <div class="flex">
                                <dt class="text-sm font-medium text-gray-500 w-24">Payment:</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($order->special_instructions)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Special Instructions</h3>
                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">{{ $order->special_instructions }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Branch Information -->
        @if($order->branch)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Pickup Location</h3>
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-indigo-600"></i>
                    </div>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">{{ $order->branch->name }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ $order->branch->address }}</p>
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
        </div>
        @endif

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($order->orderItems as $item)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $item->menuItem->name }}</h4>
                            @if($item->special_instructions)
                            <p class="text-sm text-gray-500 mt-1">{{ $item->special_instructions }}</p>
                            @endif
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-500">Qty: {{ $item->quantity }}</span>
                            <span class="font-medium text-gray-900">${{ number_format($item->total_price, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Order Summary -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900">${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->tax_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span class="text-gray-900">${{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-200">
                            <span class="text-gray-900">Total</span>
                            <span class="text-gray-900">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('guest.order.track', $order->order_number ?? $order->id) }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-eye mr-2"></i>
                Track Order
            </a>
            <a href="{{ route('guest.menu.view', ['branchId' => $order->branch_id]) }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-utensils mr-2"></i>
                Order Again
            </a>
        </div>

        <!-- Contact Support -->
        <div class="text-center mt-8 p-6 bg-blue-50 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">Need Help?</h3>
            <p class="text-sm text-gray-600 mb-4">
                If you have any questions about your order, please contact us.
            </p>
            @if($order->branch && $order->branch->phone)
            <a href="tel:{{ $order->branch->phone }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                <i class="fas fa-phone mr-2"></i>
                {{ $order->branch->phone }}
            </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh order status every 30 seconds
    setInterval(function() {
        fetch(`/guest/order/{{ $order->order_number ?? $order->id }}/status`)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    const statusElement = document.querySelector('.order-status');
                    if (statusElement && statusElement.textContent.trim().toLowerCase() !== data.status.toLowerCase()) {
                        location.reload();
                    }
                }
            })
            .catch(error => console.log('Status check failed:', error));
    }, 30000);
</script>
@endpush
