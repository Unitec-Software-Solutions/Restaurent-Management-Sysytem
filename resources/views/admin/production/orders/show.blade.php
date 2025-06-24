@extends('layouts.admin')

@section('title', 'Production Order Details')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Production Order #{{ $productionOrder->id }}
                    </h1>
                    <p class="text-gray-600 mt-1">{{ $productionOrder->production_order_number }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.production.orders.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productionOrder->getStatusBadgeClass() }}">
                            {{ ucfirst(str_replace('_', ' ', $productionOrder->status)) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Production Date</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->production_date->format('M d, Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Items</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->items->count() }} unique items</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Quantity</label>
                        <p class="text-sm text-gray-900">{{ number_format($productionOrder->getTotalQuantityOrdered()) }}
                        </p>
                    </div>
                </div>

                @if ($productionOrder->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $productionOrder->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Items to Produce -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Items to Produce</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity to Produce</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity Produced</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($productionOrder->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->item->unit_of_measurement }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->quantity_to_produce, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->quantity_produced, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $progress =
                                                $item->quantity_to_produce > 0
                                                    ? ($item->quantity_produced / $item->quantity_to_produce) * 100
                                                    : 0;
                                        @endphp
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $progress }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($progress, 1) }}%</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $item->notes ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if ($productionOrder->canBeApproved())
                        <form method="POST" action="{{ route('admin.production.orders.approve', $productionOrder) }}"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200">
                                <i class="fas fa-check mr-2"></i>Approve Order
                            </button>
                        </form>
                    @endif

                    @if ($productionOrder->canStartProduction())
                        <a href="{{ route('admin.production.sessions.create', ['order_id' => $productionOrder->id]) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-play mr-2"></i>Start Production
                        </a>
                    @endif

                    @if ($productionOrder->canBeCancelled())
                        <form method="POST" action="{{ route('admin.production.orders.cancel', $productionOrder) }}"
                            class="inline" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200">
                                <i class="fas fa-times mr-2"></i>Cancel Order
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
