@extends('layouts.admin')

@section('title', 'Today\'s Orders')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Today's Orders</h1>
                <p class="text-gray-600">{{ now()->format('F j, Y') }} - Real-time order tracking</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.orders.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Order
                </a>
                <button onclick="refreshOrders()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $orders->where('status', 'pending')->count() }}</h3>
                    <p class="text-gray-600">Pending Orders</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $orders->where('status', 'preparing')->count() }}</h3>
                    <p class="text-gray-600">Preparing</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $orders->where('status', 'ready')->count() }}</h3>
                    <p class="text-gray-600">Ready</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-star text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $orders->where('status', 'completed')->count() }}</h3>
                    <p class="text-gray-600">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Today's Orders</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">#{{ $order->id }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $order->customer_name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->customer_phone ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $order->order_type && $order->order_type->isTakeaway() ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $order->order_type ? $order->order_type->getLabel() : 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $order->branch->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $order->total_amount ? '$' . number_format($order->total_amount, 2) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($order->status)
                                        @case('pending')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('preparing')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('ready')
                                            bg-green-100 text-green-800
                                            @break
                                        @case('completed')
                                            bg-purple-100 text-purple-800
                                            @break
                                        @case('cancelled')
                                            bg-red-100 text-red-800
                                            @break
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->created_at->format('g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- View Order -->
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Edit Order -->
                                    @if(in_array($order->status, ['pending', 'preparing']))
                                        <a href="{{ route('admin.orders.edit', $order) }}" 
                                           class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    <!-- Print KOT if order has KOT items and not generated -->
                                    @if($order->has_kot_items && $order->can_generate_kot)
                                        <button onclick="printKOT({{ $order->id }})" 
                                                class="text-orange-600 hover:text-orange-900 bg-none border-none cursor-pointer" title="Print KOT">
                                            <i class="fas fa-print"></i> KOT
                                        </button>
                                    @elseif($order->has_kot_items && $order->kot_generated)
                                        <span class="text-green-600" title="KOT Already Generated">
                                            <i class="fas fa-check-circle"></i> KOT ✓
                                        </span>
                                    @endif

                                    <!-- Mark as Ready (if preparing) -->
                                    @if($order->status === 'preparing')
                                        <button onclick="updateOrderStatus({{ $order->id }}, 'ready')" 
                                                class="text-green-600 hover:text-green-900 bg-none border-none cursor-pointer" title="Mark as Ready">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif

                                    <!-- Mark as Completed (if ready) -->
                                    @if($order->status === 'ready')
                                        <button onclick="updateOrderStatus({{ $order->id }}, 'completed')" 
                                                class="text-purple-600 hover:text-purple-900 bg-none border-none cursor-pointer" title="Complete Order">
                                            <i class="fas fa-flag-checkered"></i>
                                        </button>
                                    @endif

                                    <!-- Print Bill (if can generate bill) -->
                                    @if($order->can_generate_bill)
                                        <a href="{{ route('admin.orders.print-bill', $order) }}" 
                                           class="text-gray-600 hover:text-gray-900" title="Print Bill">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No orders found for today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function refreshOrders() {
    location.reload();
}

function printKOT(orderId) {
    // Check if order has KOT items before printing
    fetch(`/admin/orders/${orderId}/check-kot`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.hasKotItems) {
            // Open KOT print window
            const kotWindow = window.open(`/admin/orders/${orderId}/print-kot`, '_blank', 'width=800,height=600');
            
            // Update order status to preparing after KOT is printed
            fetch(`/admin/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: 'preparing' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page to show updated status
                    setTimeout(() => location.reload(), 2000);
                }
            });
        } else {
            alert('This order has no items that require kitchen preparation (KOT items).');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to check KOT items');
    });
}

function updateOrderStatus(orderId, status) {
    if (confirm(`Are you sure you want to mark this order as ${status}?`)) {
        fetch(`/admin/orders/${orderId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update order status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the order');
        });
    }
}

// Auto-refresh every 30 seconds for real-time updates
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 30000);
</script>
@endsection
