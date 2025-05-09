@extends('layouts.app')

@section('content')
<div class="grid gap-4 mb-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory Activity Dashboard</h1>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <!-- Total Items Card -->
        <x-sales-card 
            title="Total Items" 
            value="{{ $totalItems }}" 
            change="0%"
            changeType="up"
            icon="M4 3a2 2 0 100 4h12a2 2 0 100-4H4zm0 5a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2v-6a2 2 0 00-2-2H4z"
        />

        <!-- Total Stock Value -->
        <x-sales-card 
            title="Total Stock Value" 
            value="${{ number_format($totalStockValue, 2) }}" 
            change="0%"
            changeType="up"
            icon="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
        />

        <!-- Purchase Orders -->
        <x-sales-card 
            title="Purchase Orders" 
            value="{{ $purchaseOrdersCount }}" 
            change="0%"
            changeType="up"
            icon="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
        />

        <!-- Sales Orders -->
        <x-sales-card 
            title="Sales Orders" 
            value="{{ $salesOrdersCount }}" 
            change="0%"
            changeType="up"
            icon="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex justify-end mb-4">
                <a href="{{ route('inventory.items.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    + Add Item
                </a>
            </div>
            <!-- Product Details Section -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">PRODUCT DETAILS</h3>
                </div>
                <div class="p-4">
                    <div class="flex justify-between mb-4">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Low Stock Items</h4>
                            <div class="flex items-center mt-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">All Item Groups</span>
                                <span class="mx-2 text-gray-400">•</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">All Items</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md">View All</button>
                        </div>
                    </div>

                    <!-- Low Stock Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Item Name</th>
                                    <th scope="col" class="px-6 py-3">Current Stock</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems as $item)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                                        <td class="px-6 py-4">{{ $item->stocks->sum('current_quantity') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->stock_status === 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($item->stock_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center">No low stock items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Active Items Section -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Items</h3>
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($activeItems as $item)
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">{{ $item }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-4">
            <!-- Top Selling Items -->
            <x-top-selling-items :items="$topSellingItems" />

            <!-- Purchase Order Summary -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">PURCHASE ORDER</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Quantity Ordered</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $purchaseOrderSummary['quantity_ordered'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Cost</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">${{ number_format($purchaseOrderSummary['total_cost'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Order Summary -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">SALES ORDER</h3>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Channel</th>
                                    <th scope="col" class="px-6 py-3">Draft</th>
                                    <th scope="col" class="px-6 py-3">Confirmed</th>
                                    <th scope="col" class="px-6 py-3">Packed</th>
                                    <th scope="col" class="px-6 py-3">Shipped</th>
                                    <th scope="col" class="px-6 py-3">Invoiced</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesOrderChannels as $channel)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $channel['name'] }}</td>
                                        <td class="px-6 py-4">{{ $channel['draft'] }}</td>
                                        <td class="px-6 py-4">{{ $channel['confirmed'] }}</td>
                                        <td class="px-6 py-4">{{ $channel['packed'] }}</td>
                                        <td class="px-6 py-4">{{ $channel['shipped'] }}</td>
                                        <td class="px-6 py-4">{{ $channel['invoiced'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection