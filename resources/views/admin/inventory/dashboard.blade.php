@php
use App\Models\ItemTransaction;
@endphp

@extends('layouts.admin')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="">
<!-- Header with navigation buttons -->
<div class="flex justify-between items-center mb-4">
    <div>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                        </svg>
                        Home
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Inventory Dashboard</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
    <div class="flex space-x-2">
        <!-- Items Management Button -->
        <a href="{{ route('admin.inventory.items.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-boxes mr-2"></i> Items
        </a>
        
        <!-- Stock Management Button -->
        <a href="{{ route('admin.inventory.stock.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-warehouse mr-2"></i> Stock
        </a>
        
        <!-- Transactions Button -->
        <a href="{{ route('admin.inventory.stock.transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-exchange-alt mr-2"></i> Transactions
        </a>
        

    </div>
</div>

    <!-- KPI Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Items Card -->
        <div class="bg-red-600 rounded-lg shadow dark:bg-red-700 p-4 text-white">
            <div class="flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-full bg-red-500 dark:bg-red-600">
                        <i class="fas fa-box-open fa-lg"></i>
                    </div>
                    <h5 class="text-lg font-semibold">Total Items</h5>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-1">{{ $totalItems }}</h2>
                    <p class="text-red-100 text-sm">+{{ $newItemsToday }} from today</p>
                </div>
            </div>
        </div>

        <!-- Total Stock Value Card -->
        <div class="bg-yellow-400 rounded-lg shadow dark:bg-yellow-600 p-4 text-gray-900 dark:text-white">
            <div class="flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-full bg-yellow-300 dark:bg-yellow-500">
                        <i class="fas fa-dollar-sign fa-lg"></i>
                    </div>
                    <h5 class="text-lg font-semibold">Total Stock Value</h5>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-1">Rs. {{ number_format($totalStockValue, 2) }}</h2>
                    <p class="text-yellow-800 dark:text-yellow-100 text-sm">+{{ $stockValueChange }} from yesterday</p>
                </div>
            </div>
        </div>

        <!-- Purchase Orders Card -->
        <div class="bg-green-600 rounded-lg shadow dark:bg-green-700 p-4 text-white">
            <div class="flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-full bg-green-500 dark:bg-green-600">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                    </div>
                    <h5 class="text-lg font-semibold">Purchase Orders</h5>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-1">${{ number_format($purchaseOrdersTotal, 2) }}</h2>
                    <p class="text-green-100 text-sm">+{{ $purchaseOrdersCount }} from yesterday</p>
                </div>
            </div>
        </div>

        <!-- Sales Orders Card -->
        <div class="bg-blue-600 rounded-lg shadow dark:bg-blue-700 p-4 text-white">
            <div class="flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-full bg-blue-500 dark:bg-blue-600">
                        <i class="fas fa-chart-line fa-lg"></i>
                    </div>
                    <h5 class="text-lg font-semibold">Sales Orders</h5>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-1">${{ number_format($salesOrdersTotal, 2) }}</h2>
                    <p class="text-blue-100 text-sm">+{{ $salesOrdersCount }} from yesterday</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <!-- Product Details Table -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow dark:bg-gray-800 p-4">
        <div class="flex justify-between items-center mb-4">
            <h5 class="text-xl font-bold text-gray-900 dark:text-white">Product Details</h5>
            <a href="#" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                        <th class="py-3 px-6 text-left">Code</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Category</th>
                        <th class="py-3 px-6 text-left">Unit</th>
                        <th class="py-3 px-6 text-right">Cost Price</th>
                        <th class="py-3 px-6 text-right">Selling Price</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($items as $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6">{{ $item->item_code }}</td>
                            <td class="py-3 px-6">{{ $item->name }}</td>
                            <td class="py-3 px-6">{{ $item->category->name ?? 'N/A' }}</td>
                            <td class="py-3 px-6">{{ $item->unit_of_measurement }}</td>
                            <td class="py-3 px-6 text-right">{{ number_format($item->buying_price, 2) }}</td>
                            <td class="py-3 px-6 text-right">{{ number_format($item->selling_price, 2) }}</td>
                            <td class="py-3 px-6 text-center">
                                @if($item->deleted_at)
                                    <span class="bg-red-200 text-red-800 py-1 px-3 rounded-full text-xs">Deleted</span>
                                @else
                                    <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs">Active</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex justify-center space-x-3">
                                    <a href="{{ route('admin.inventory.items.show', $item->id) }}" class="hover:text-blue-500">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.inventory.items.edit', $item->id) }}" class="hover:text-yellow-500">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.inventory.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:text-red-500">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-500">No items found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

        <!-- Top Selling Items -->
        <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white mb-4">Top Selling Items</h5>
            <div class="flow-root">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Sold</th>
                                <th scope="col" class="px-6 py-3">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topSellingItems as $item)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $item->name }}</td>
                                <td class="px-6 py-4">{{ $item->quantity_sold }}</td>
                                <td class="px-6 py-4">${{ number_format($item->revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Purchase Order Summary -->
        <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white mb-4">Purchase Order Summary</h5>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $purchaseOrderQuantity }}</h3>
                    <p class="text-gray-500 dark:text-gray-400">Quantity Ordered</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Rs. {{ number_format($purchaseOrderTotalCost, 2) }}</h3>
                    <p class="text-gray-500 dark:text-gray-400">Total Cost</p>
                </div>
            </div>
        </div>

        <!-- Sales Orders -->
        <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white mb-4">Recent Sales Orders</h5>
            <div class="flow-root">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Category</th>
                                <th scope="col" class="px-6 py-3">Qty</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salesOrders as $order)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4">{{ $order->category->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $order->quantity }}</td>
                                <td class="px-6 py-4">
                                    @if ($order->status === 'pending')
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Pending</span>
                                    @elseif ($order->status === 'shipped')
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Shipped</span>
                                    @elseif ($order->status === 'delivered')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Delivered</span>
                                    @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Cancelled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection