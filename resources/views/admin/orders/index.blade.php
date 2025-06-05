@extends('layouts.admin')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Reservation Orders</h1>
            <a href="{{ route('admin.orders.dashboard') }}" class="text-blue-500 hover:text-blue-700">
                ← Back to Dashboard
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Order #</th>
                        <th class="px-4 py-2 text-left">Reservation</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                        <th class="px-4 py-2 text-left">Branch</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3">#{{ $order->id }}</td>
                        <td class="px-4 py-3">
                            @if($order->reservation)
                                <a href="{{ route('admin.reservations.show', $order->reservation) }}" class="text-blue-500">
                                    #{{ $order->reservation->id }}
                                </a>
                            @else
                                <span class="text-gray-500">Order ID: #{{ $order->id }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3">{{ $order->branch->name }}</td>
                        <td class="px-4 py-3 text-right">LKR {{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.orders.reservations.edit', ['reservation' => $order->reservation_id, 'order' => $order]) }}"
                               class="text-blue-500 hover:text-blue-700">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">No reservation orders found</td>
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