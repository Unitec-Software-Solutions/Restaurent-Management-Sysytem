@php
    use App\Models\ItemTransaction;
@endphp

@extends('layouts.admin')

@section('header-title', 'Inventory Dashboard')

@section('content')

{{-- Header Navigation --}}
{{-- @if(config('app.debug'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center">
            <h3 class="text-sm font-medium text-green-800">🔍 Inventory Dashboard Debug Info</h3>
            <a href="{{ route('admin.inventory.dashboard', ['debug' => 1]) }}"
               class="text-xs text-green-600 hover:text-green-800">
                Full Debug (@dd)
            </a>
        </div>
        <div class="text-xs text-green-700 mt-2 grid grid-cols-4 gap-4">
            <div>
                <p><strong>Items Variable:</strong> {{ isset($items) ? 'Set (' . $items->count() . ')' : 'NOT SET' }}</p>
                <p><strong>DB ItemMaster:</strong> {{ \App\Models\ItemMaster::count() }}</p>
            </div>
            <div>
                <p><strong>Categories Variable:</strong> {{ isset($categories) ? 'Set (' . $categories->count() . ')' : 'NOT SET' }}</p>
                <p><strong>DB Categories:</strong> {{ \App\Models\ItemCategory::count() }}</p>
            </div>
            <div>
                <p><strong>Top Selling:</strong> {{ isset($topSellingItems) ? 'Set (' . count($topSellingItems) . ')' : 'NOT SET' }}</p>
                <p><strong>Transactions:</strong> {{ \App\Models\ItemTransaction::count() }}</p>
            </div>
            <div>
                <p><strong>Admin:</strong> {{ auth('admin')->check() ? auth('admin')->user()->name : 'NOT AUTH' }}</p>
                <p><strong>Branch:</strong> {{ auth('admin')->user()->branch->name ?? 'None' }}</p>
            </div>
        </div>
    </div>
@endif --}}

    <div class="p-4 rounded-lg">
        <!-- Header with navigation buttons -->
        <div class="  justify-between items-center mb-4">
            <div class="rounded-lg ">
                <x-nav-buttons :items="[
                    ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                    ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                    ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                    ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                    ['name' => 'Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                    ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
                ]" active="Dashboard" />

                <!-- Search and Filter -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search Item</label>
                            <div class="relative">
                                <input type="text" placeholder="Search by name"
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Category</label>
                            <select
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option>All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end space-x-2">
                            <a href="{{ route('admin.inventory.items.index') }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-boxes mr-2"></i> Items
                            </a>
                            <a href="{{ route('admin.inventory.stock.index') }}"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-warehouse mr-2"></i> Stock
                            </a>
                            <a href="{{ route('admin.inventory.stock.transactions.index') }}"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-exchange-alt mr-2"></i> Transactions
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Inventory Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Product Details -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:col-span-2">
                        <div class="p-6 border-b flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Product Details</h2>
                            <div class="flex space-x-2">
                                <select class="text-sm border rounded px-2 py-1 focus:outline-none">
                                    <option>All Status</option>
                                    <option>Active</option>
                                    <option>Inactive</option>
                                </select>
                                <a href="{{ route('admin.inventory.items.create') }}"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                                    <i class="fas fa-plus mr-1"></i> Add Item
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Code</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cost</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">{{ $item->item_code }}</td>
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->name }}</td>
                                            <td class="px-6 py-4">{{ $item->category->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4">{{ $item->unit_of_measurement }}</td>
                                            <td class="px-6 py-4 text-right">Rs.
                                                {{ number_format($item->buying_price, 2) }}</td>
                                            <td class="px-6 py-4 text-right">Rs.
                                                {{ number_format($item->selling_price, 2) }}</td>
                                            <td class="px-6 py-4 text-center">
                                                @if ($item->deleted_at)
                                                    <x-partials.badges.status-badge status="danger" text="Deleted" />
                                                @else
                                                    <x-partials.badges.status-badge status="success" text="Active" />
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex justify-center space-x-3">
                                                    <a href="{{ route('admin.inventory.items.show', $item->id) }}"
                                                        class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.inventory.items.edit', $item->id) }}"
                                                        class="text-yellow-600 hover:text-yellow-800">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.inventory.items.destroy', $item->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this item?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No items found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top Selling Items -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6 border-b flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Top Selling Items</h2>
                            <a href="{{ route('admin.inventory.stock.transactions.index') }}"
                                class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                        </div>

                        <div class="divide-y divide-gray-200">
                            @foreach ($topSellingItems as $item)
                                <div class="p-4 flex items-center">
                                    <div class="bg-indigo-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-box text-indigo-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium">{{ $item->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $item->category->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">{{ $item->quantity_sold }} sold</p>
                                        <p class="text-sm text-green-500">Rs. {{ number_format($item->revenue, 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
