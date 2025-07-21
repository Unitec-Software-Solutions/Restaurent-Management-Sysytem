@extends('layouts.app')
@section('content')
@php
    $reservationId = $reservationId ?? null;
@endphp
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">
                @if($reservationId)
                    Orders for Reservation #{{ $reservationId }}
                @else
                    Order Management
                @endif
            </h1>
            <div class="flex gap-3">
                <a href="{{ route('orders.create', ['reservation' => $reservationId]) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Order
                </a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="border-t pt-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <input type="hidden" name="reservation_id" value="{{ $reservationId }}">
                <input type="hidden" name="phone" value="{{ $phone }}">

                <!-- Date Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $filters['startDate'] }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $filters['endDate'] }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="submitted" {{ $filters['status'] === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="preparing" {{ $filters['status'] === 'preparing' ? 'selected' : '' }}>Preparing</option>
                        <option value="ready" {{ $filters['status'] === 'ready' ? 'selected' : '' }}>Ready</option>
                        <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $filters['branchId'] == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('orders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                        Reset
                    </a>
                </div>

                <!-- Export Actions -->
                <div class="flex items-end space-x-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-file-csv mr-2"></i> CSV
                    </a>
                </div>
            </form>
        </div>

        @if($reservationId)
        <div class="mt-6 border-t pt-4">
            <h2 class="text-lg font-semibold mb-2">Reservation Details:</h2>
            @php
                $reservation = $orders->first()?->reservation ?? null;
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-gray-600">Customer Name:</p>
                    <p class="font-medium">{{ $reservation?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Scheduled Time:</p>
                    <p class="font-medium">
                        @if($reservation && $reservation?->scheduled_time)
                            {{ $reservation->scheduled_time->format('M j, Y H:i') }}
                        @else
                            Not scheduled
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-600">Total Orders:</p>
                    <p class="font-medium">{{ $orders?->count() ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Grand Total:</p>
                    <p class="font-medium text-green-600">
                        LKR  {{ number_format($grandTotals['total'] ?? 0, 2) }}
                    </p>
                </div>
            </div>
            {{-- Proceed to Payment Button --}}
            <div class="mt-6">
                <a href="{{ route('reservations.payment', ['reservation' => $reservationId]) }}"
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Proceed to Payment
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Orders Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Orders List</h2>
            <p class="text-sm text-gray-500 mt-1">
                Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() ?? 0 }} orders
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Steward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <!-- Order Details -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="font-medium text-gray-900">#{{ $order->order_number }}</div>
                                <div class="text-sm text-gray-500">{{ $order->order_date->format('M d, Y H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $order->branch->name ?? 'N/A' }}</div>
                            </div>
                        </td>

                        <!-- Customer -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="font-medium text-gray-900">{{ $order->customer_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $order->customer_phone ?? 'N/A' }}</div>
                                @if($order->reservation && $order->reservation->table_number)
                                    <div class="text-xs text-gray-400">Table: {{ $order->reservation->table_number }}</div>
                                @endif
                            </div>
                        </td>

                        <!-- Steward -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($order->steward)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $order->steward->first_name }} {{ $order->steward->last_name }}
                                </div>
                            @else
                                <span class="text-gray-400">Not assigned</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($order->status === 'completed') bg-green-100 text-green-800
                                @elseif($order->status === 'preparing') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'ready') bg-blue-100 text-blue-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                @if($order->kot_generated)
                                    <a href="{{ route('orders.print-kot-pdf', $order) }}" class="text-orange-600 hover:text-orange-900 bg-none border-none cursor-pointer" title="Print KOT PDF">
                                        <i class="fas fa-print"></i> KOT Print
                                    </a>
                                @endif
                                @if($order->bill_generated)
                                    <i class="fas fa-check text-blue-500"></i> Bill
                                @endif
                            </div>
                        </td>

                        <!-- Items -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $order->items->count() }} items</div>
                            <div class="text-xs text-gray-500">
                                {{ $order->items->sum('quantity') }} total qty
                            </div>
                        </td>

                        <!-- Total -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                Rs. {{ number_format($order->total, 2) }}
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="text-indigo-600 hover:text-indigo-900" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($order->status === 'submitted')
                                    <a href="{{ route('orders.mark-preparing', $order->id) }}"
                                       class="text-yellow-600 hover:text-yellow-900" title="Start Preparing">
                                        <i class="fas fa-play"></i>
                                    </a>
                                @elseif($order->status === 'preparing')
                                    <a href="{{ route('orders.mark-ready', $order->id) }}"
                                       class="text-blue-600 hover:text-blue-900" title="Mark Ready">
                                        <i class="fas fa-check"></i>
                                    </a>
                                @elseif($order->status === 'ready')
                                    <a href="{{ route('orders.complete', $order->id) }}"
                                       class="text-green-600 hover:text-green-900" title="Complete & Bill">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                @endif

                                @if($order->status === 'completed' || $order->bill_generated)
                                    <a href="{{ route('orders.print-bill', $order->id) }}"
                                       class="text-green-600 hover:text-green-900" title="Print Bill">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                @endif

                                <a href="{{ route('orders.edit', $order->id) }}"
                                   class="text-gray-600 hover:text-gray-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-utensils text-gray-300 text-4xl mb-2"></i>
                                <p>No orders found for the selected criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- Return to Dashboard Button --}}
    <div class="mt-6 flex justify-center">
        <a href="/customer-dashboard"
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded transition">
            <i class="fas fa-home mr-2"></i> Return to Dashboard
        </a>
    </div>
                    <th class="px-4 py-2 text-left">Order #</th>
                    <th class="px-4 py-2 text-right">Items</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders ?? [] as $order)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $order->id }}</td>
                    <td class="px-4 py-3 text-right">
                        {{ $order->items?->sum('quantity') ?? 0 }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        LKR  {{ number_format($order->total ?? 0, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route(('orders.summary'), $order->id) }}"
                               class="text-blue-500 hover:text-blue-700"
                               title="View Order">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>

                            <a href="{{ route('orders.edit', $order->id) }}?reservation_id={{ $reservationId }}"
                               class="text-yellow-500 hover:text-yellow-700"
                               title="Edit Order">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>

                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="reservation_id" value="{{ $reservationId }}">
                                <button type="submit"
                                        class="text-red-500 hover:text-red-700"
                                        onclick="return confirm('Delete this order permanently?')"
                                        title="Delete Order">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                        No orders found for this reservation
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="mt-6">
        {{ $orders->appends(['reservation_id' => $reservationId])->links() }}
    </div>
    @endif
</div>

<!-- Print KOT if order has KOT items -->
@php
    $hasKotItems = $order->orderItems()->whereHas('menuItem', function($q) {
        $q->where('type', \App\Models\MenuItem::TYPE_KOT);
    })->exists();
@endphp

@if($hasKotItems)
    <div class="flex gap-1">
        <button onclick="printKOT({{ $order->id }})"
                class="text-orange-600 hover:text-orange-900" title="Print KOT">
            <i class="fas fa-print"></i> KOT
        </button>
        <a href="{{ route('admin.orders.print-kot-pdf', $order) }}"

           class="text-red-600 hover:text-red-900" title="Download KOT PDF">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
    </div>
@endif
@endsection

<script>
function printKOT(orderId) {
    // Check if order has KOT items before printing
    fetch(`/admin/orders/${orderId}/check-kot`, {
        // ...
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
    .catch(() => {
        alert('Failed to check KOT items');
    });
}
</script>

