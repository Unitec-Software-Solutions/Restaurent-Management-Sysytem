@extends('layouts.admin')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Reservation Orders</h1>
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <span class="text-gray-600">Showing latest reservation orders</span>
                </div>
                <!-- Optionally, add a filter/search form here -->
            </div>
            @if($orders->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border-b">Order #</th>
                                <th class="px-4 py-2 border-b">Reservation</th>
                                <th class="px-4 py-2 border-b">Customer</th>
                                <th class="px-4 py-2 border-b">Table</th>
                                <th class="px-4 py-2 border-b">Status</th>
                                <th class="px-4 py-2 border-b">Created</th>
                                <th class="px-4 py-2 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b font-mono">{{ $order->id }}</td>
                                    <td class="px-4 py-2 border-b">
                                        @if($order->reservation)
                                            #{{ $order->reservation->id }}<br>
                                            {{ $order->reservation->reservation_time->format('M d, Y H:i') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if($order->reservation && $order->reservation->customer)
                                            {{ $order->reservation->customer->name }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if($order->reservation && $order->reservation->table)
                                            {{ $order->reservation->table->name ?? $order->reservation->table->id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        <span class="inline-block px-2 py-1 rounded text-xs {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $order->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-2 border-b">
                                        @if(optional($order->reservation)->exists)
                                            <a href="{{ route('admin.reservations.show', ['reservation' => $order->reservation->id]) }}" 
                                               class="inline-block text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded px-3 py-1 transition">
                                                View
                                            </a>
                                        @else
                                            <span class="inline-block text-sm text-gray-500 italic">No Reservation</span>
                                        @endif
                                        @if($order->reservation)
                                            <a href="{{ route('admin.orders.reservations.edit', ['reservation' => $order->reservation->id, 'order' => $order->id]) }}"
                                               class="inline-block ml-2 text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded px-3 py-1 transition">
                                                Edit
                                            </a>
                                        @else
                                            <a href="{{ route('admin.orders.reservations.edit', ['reservation' => null, 'order' => $order->id]) }}"
                                               class="inline-block ml-2 text-sm font-medium text-white bg-yellow-400 hover:bg-yellow-500 rounded px-3 py-1 transition">
                                                Edit (No Res.)
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-gray-500">No reservation orders found.</div>
            @endif
        </div>
    </div>
</div>
@endsection
