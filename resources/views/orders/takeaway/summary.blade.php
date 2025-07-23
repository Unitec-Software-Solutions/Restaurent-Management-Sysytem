@extends('layouts.app')

@section('content')
<div class="mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Order Confirmation</h2>
                    <p class="text-green-100 mt-1">Order ID: {{ $order->takeaway_id }}</p>
                </div>
                <div class="text-right">
                    <div class="bg-white/20 rounded-lg px-3 py-2">
                        <i class="fas fa-clock text-white mr-2"></i>
                        <span class="text-white font-medium">{{ now()->format('H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert for Order Status -->
        @if($order->status === 'pending')
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Order Pending:</strong> Please review your order details below and confirm to submit your order to the kitchen.
                    </p>
                </div>
            </div>
        </div>
        @elseif($order->status === 'submitted')
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Order Confirmed:</strong> Your order has been submitted to the kitchen. You'll be notified when it's ready for pickup.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Card Body -->
        <div class="p-6">
            <!-- Order and Customer Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Order Details -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4 flex items-center">
                        <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                        Order Details
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-store text-gray-400 mr-2"></i>
                                Branch:
                            </span>
                            <span class="font-medium">{{ $order->branch->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-clock text-gray-400 mr-2"></i>
                                Pickup Time:
                            </span>
                            <span class="font-medium">
                                @if ($order->order_time instanceof \Carbon\Carbon)
                                    {{ $order->order_time->format('M j, Y H:i') }}
                                @elseif(!empty($order->order_time))
                                    {{ \Carbon\Carbon::parse($order->order_time)->format('M j, Y H:i') }}
                                @else
                                    ASAP
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-info-circle text-gray-400 mr-2"></i>
                                Status:
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $order->status === 'submitted' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $order->status === 'preparing' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                Order Date:
                            </span>
                            <span class="font-medium">{{ $order->created_at->format('M j, Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4 flex items-center">
                        <i class="fas fa-user text-green-600 mr-2"></i>
                        Customer Details
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-user-circle text-gray-400 mr-2"></i>
                                Name:
                            </span>
                            <span class="font-medium">{{ $order->customer_name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-phone text-gray-400 mr-2"></i>
                                Phone:
                            </span>
                            <span class="font-medium">{{ $order->customer_phone }}</span>
                        </div>
                        @if($order->special_instructions)
                        <div class="pt-2 border-t border-gray-200">
                            <span class="text-gray-600 flex items-start">
                                <i class="fas fa-sticky-note text-gray-400 mr-2 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-700 mb-1">Special Instructions:</div>
                                    <div class="text-sm bg-white p-2 rounded border">{{ $order->special_instructions }}</div>
                                </div>
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($order->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->menuItem->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">LKR {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">LKR {{ number_format($item->subtotal ?? ($item->quantity * $item->unit_price), 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-sm font-medium text-gray-900 text-right">Subtotal</td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">LKR {{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-sm font-medium text-gray-900 text-right">Tax (10%)</td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">LKR {{ number_format($order->tax, 2) }}</td>
                            </tr>
                            <tr class="border-t-2 border-gray-200">
                                <td colspan="3" class="px-6 py-3 text-sm font-bold text-gray-900 text-right">Total</td>
                                <td class="px-6 py-3 text-sm font-bold text-gray-900">LKR {{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 space-y-4">
                @if($order->status === 'pending')
                <!-- Pending Order Actions -->
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-6 rounded-lg border border-yellow-200">
                    <div class="text-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Ready to confirm your order?</h4>
                        <p class="text-gray-600 text-sm">Review your items above and confirm to send your order to the kitchen.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <!-- Edit Order Button -->
                        <a href="{{ route('orders.takeaway.edit', $order->id) }}"
                           class="flex-1 max-w-xs inline-flex items-center justify-center px-6 py-4 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 touch-manipulation">
                            <i class="fas fa-edit mr-3 text-lg"></i>
                            <span class="text-lg">Edit Order</span>
                        </a>

                        <!-- Confirm Order Button -->
                        <form action="{{ route('orders.takeaway.submit', $order->id) }}" method="POST" class="flex-1 max-w-xs">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-6 py-4 bg-green-600 border border-transparent rounded-lg font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 touch-manipulation confirm-order-btn">
                                <i class="fas fa-check-circle mr-3 text-lg"></i>
                                <span class="text-lg">Confirm Order</span>
                            </button>
                        </form>
                    </div>

                    <!-- Cancel Order Button -->
                    <div class="mt-4 text-center">
                        <form action="{{ route('orders.takeaway.destroy', ['order' => $order->id]) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 text-red-600 hover:text-red-800 transition-colors touch-manipulation"
                                    onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-times mr-2"></i>
                                Cancel Order
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <!-- Submitted Order Actions -->
                <div class="bg-gradient-to-r from-green-50 to-blue-50 p-6 rounded-lg border border-green-200">
                    <div class="text-center mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <i class="fas fa-check text-green-600 text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">Order Confirmed!</h4>
                        <p class="text-gray-600">Your order has been sent to the kitchen. We'll notify you when it's ready for pickup.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <!-- Dashboard Button -->
                        <a href="{{ route('home') }}"
                           class="flex-1 max-w-xs inline-flex items-center justify-center px-6 py-4 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 touch-manipulation">
                            <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                            <span class="text-lg">Go to Dashboard</span>
                        </a>

                        <!-- New Order Button -->
                        <a href="{{ route('orders.takeaway.create') }}"
                           class="flex-1 max-w-xs inline-flex items-center justify-center px-6 py-4 bg-green-600 border border-transparent rounded-lg font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 touch-manipulation">
                            <i class="fas fa-plus mr-3 text-lg"></i>
                            <span class="text-lg">New Order</span>
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add confirmation dialog for order submission
    const confirmBtn = document.querySelector('.confirm-order-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const confirmation = confirm('Are you sure you want to confirm this order? Once confirmed, it will be sent to the kitchen for preparation.');

            if (confirmation) {
                // Add loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-3 text-lg"></i><span class="text-lg">Confirming...</span>';

                // Submit the form
                this.closest('form').submit();
            }
        });
    }

    // Auto-refresh for order status updates (if needed)
    @if($order->status !== 'pending')
    setInterval(function() {
        // Could implement real-time status updates here
        console.log('Checking order status...');
    }, 30000); // Check every 30 seconds
    @endif
});
</script>

<style>
/* Touch-friendly buttons */
.touch-manipulation {
    touch-action: manipulation;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.touch-manipulation:active {
    transform: scale(0.98);
}

/* Responsive design */
@media (max-width: 640px) {
    .touch-manipulation {
        min-height: 50px;
        font-size: 1.1rem;
    }

    .touch-manipulation i {
        font-size: 1.2rem;
    }
}

/* Loading state */
.touch-manipulation:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Enhanced shadows for touch feedback */
.touch-manipulation:hover:not(:disabled) {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.touch-manipulation:active:not(:disabled) {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transform: translateY(0);
}

/* Status badges animation */
.status-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

/* Order confirmation animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}
</style>
@endsection
