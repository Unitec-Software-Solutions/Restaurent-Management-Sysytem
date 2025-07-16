@extends('layouts.admin')

@section('title', 'Orders Management')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Orders Management</h1>
                <p class="text-gray-600">Manage and track all orders across your organization</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.orders.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Order
                </a>
                <button onclick="exportOrders()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-download mr-2"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.orders.index') }}"
        class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Customer name, phone, or order ID"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <!-- Branch Filter -->
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Branches</option>
                    @foreach($branches ?? [] as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Preparing</option>
                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg w-full">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
            </div>
        </form>

        <!-- Additional Buttons -->
        <div class="flex justify-start mt-4 space-x-2">
            <button onclick="filterTodayOrders()" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-calendar-day mr-2"></i> Today's Orders
            </button>
            <button onclick="filterKOTOrders()" type="button" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-fire mr-2"></i> KOT Orders
            </button>
            <button onclick="clearFilters()" type="button" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-times mr-2"></i> Clear Filters
            </button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders ?? [] as $order)
                        <tr class="hover:bg-gray-50">
                            <!-- Order Details -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-10 h-10">
                                        @php
                                            // Fix: Convert enum to string value for comparison
                                            $orderTypeValue = $order->order_type instanceof \App\Enums\OrderType
                                                ? $order->order_type->value
                                                : (string) $order->order_type;
                                        @endphp

                                        @if(str_contains($orderTypeValue, 'takeaway'))
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-shopping-bag text-blue-600"></i>
                                            </div>
                                        @elseif(str_contains($orderTypeValue, 'dine_in'))
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-utensils text-green-600"></i>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-receipt text-gray-600"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            Order #{{ $order->id }}
                                        </div>
                                        @if($order->reservation)
                                            <div class="text-sm text-gray-500">
                                                Reservation #{{ $order->reservation->id }}
                                            </div>
                                        @endif
                                        @if($order->order_number)
                                            <div class="text-sm text-gray-500">
                                                {{ $order->order_number }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Customer -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $order->customer_name ?? 'Guest Customer' }}
                                </div>
                                @if($order->customer_phone)
                                    <div class="text-sm text-gray-500">
                                        {{ $order->customer_phone }}
                                    </div>
                                @endif
                            </td>

                            <!-- Branch -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $order->branch->name ?? 'N/A' }}
                                </div>
                            </td>

                            <!-- Type -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if(str_contains($orderTypeValue, 'takeaway'))
                                        bg-blue-100 text-blue-800
                                    @elseif(str_contains($orderTypeValue, 'dine_in'))
                                        bg-green-100 text-green-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $order->getOrderTypeLabel() }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @switch($order->status)
                                        @case('pending')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('confirmed')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('preparing')
                                            bg-orange-100 text-orange-800
                                            @break
                                        @case('ready')
                                            bg-indigo-100 text-indigo-800
                                            @break
                                        @case('completed')
                                            bg-green-100 text-green-800
                                            @break
                                        @case('cancelled')
                                            bg-red-100 text-red-800
                                            @break
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>

                            <!-- Total -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    LKR {{ number_format($order->total_amount ?? $order->total ?? 0, 2) }}
                                </div>
                                @if($order->orderItems && $order->orderItems->count() > 0)
                                    <div class="text-sm text-gray-500">
                                        {{ $order->orderItems->count() }} items
                                    </div>
                                @endif
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $order->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $order->created_at->format('H:i A') }}
                                </div>
                            </td>                                <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <!-- View button -->
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-2.5 py-1.5 rounded-md flex items-center"
                                       title="View Order">
                                        <i class="fas fa-eye mr-1"></i>
                                        <span>View</span>
                                    </a>

                                    <!-- Edit button - only for pending or confirmed orders -->
                                    @if(in_array($order->status, ['pending', 'confirmed', 'submitted']))
                                        <a href="{{ route('admin.orders.edit', $order) }}"
                                           class="bg-amber-100 text-amber-700 hover:bg-amber-200 px-2.5 py-1.5 rounded-md flex items-center"
                                           title="Edit Order">
                                            <i class="fas fa-edit mr-1"></i>
                                            <span>Edit</span>
                                        </a>
                                    @endif

                                    <!-- Delete button - only show if not completed -->
                                    @if($order->status !== 'completed')
                                        <button onclick="confirmDeleteOrder({{ $order->id }})"
                                                class="bg-red-100 text-red-700 hover:bg-red-200 px-2.5 py-1.5 rounded-md flex items-center"
                                                title="Delete Order">
                                            <i class="fas fa-trash mr-1"></i>
                                            <span>Delete</span>
                                        </button>
                                    @endif

                                    <!-- Print KOT if order has KOT items -->
                                    @php
                                        $hasKotItems = $order->orderItems()->whereHas('menuItem', function($q) {
                                            $q->where('type', \App\Models\MenuItem::TYPE_KOT);
                                        })->exists();
                                    @endphp

                                    @if($hasKotItems)
                                        <a href="{{ route('admin.orders.print-kot-pdf', $order) }}"
                                           class="text-red-600 hover:text-red-900" title="KOT">
                                            <i class="fas fa-file-pdf"></i> KOT
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-400 text-4xl mb-3">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
                                <p class="text-gray-500 mb-4">Get started by creating your first order.</p>
                                <a href="{{ route('admin.orders.create') }}"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Create Order
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(isset($orders) && $orders->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function updateOrderStatus(orderId, status) {
    if (confirm(`Are you sure you want to ${status} this order?`)) {
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
            window.open(`/admin/orders/${orderId}/print-kot`, '_blank', 'width=800,height=600');
        } else {
            alert('This order has no items that require kitchen preparation (KOT items).');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to check KOT items');
    });
}

// Function to show delete confirmation modal
function confirmDeleteOrder(orderId) {
    if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/orders/${orderId}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function exportOrders() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.location.href = `${window.location.pathname}?${params.toString()}`;
}

function filterTodayOrders() {
    const today = new Date().toISOString().split('T')[0];
    window.location.href = `${window.location.pathname}?date_from=${today}&date_to=${today}`;
}

function filterKOTOrders() {
    window.location.href = `${window.location.pathname}?has_kot=1`;
}

function clearFilters() {
    window.location.href = window.location.pathname;
}
</script>
@endpush
@endsection
