@extends('layouts.admin')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">
                All Orders
                @php $admin = auth('admin')->user(); @endphp
                @if($admin->is_super_admin)
                    <span class="text-sm text-gray-500">(All Organizations)</span>
                @elseif($admin->organization)
                    <span class="text-sm text-gray-500">({{ $admin->organization->name }})</span>
                @elseif($admin->branch)
                    <span class="text-sm text-gray-500">({{ $admin->branch->name }})</span>
                @endif
            </h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.orders.takeaway.create') }}" 
                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Create Takeaway
                </a>

                @if(!$admin->isSuperAdmin())
                    <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary">Create Reservation</a>
                @endif
                <a href="{{ route('admin.orders.dashboard') }}" class="text-blue-500 hover:text-blue-700 flex items-center">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Order #</th>
                        <th class="px-4 py-2 text-left">Type</th>
                        <th class="px-4 py-2 text-left">Reference</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3">#{{ $order->id }}</td>
                        <td class="px-4 py-3">
                            @if(Str::contains($order->order_type, 'dine_in'))
                                Dine-in
                            @else
                                Takeaway
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($order->reservation_id)
                                <a href="{{ route('admin.reservations.show', $order->reservation_id) }}" class="text-blue-500">
                                    Reservation #{{ $order->reservation_id }}
                                </a>
                            @else
                                Takeaway Order
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3 text-right">LKR {{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded 
                                @if($order->status === 'completed') bg-green-100 text-green-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($order->reservation)
                                <a href="{{ route('admin.orders.reservations.edit', ['reservation' => $order->reservation_id, 'order' => $order->id]) }}"
                                   class="text-blue-500 hover:text-blue-700">
                                    Edit
                                </a>
                            @else
                                <a href="{{ route('admin.orders.takeaway.edit', ['order' => $order->id]) }}"
                                   class="text-blue-500 hover:text-blue-700">
                                    Edit
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection