@extends('layouts.admin')

@section('header-title', 'Inventory Stock Management')
{{-- @section('header-subtitle', 'Overview of your metrics') --}}
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Header with buttons -->
        <x-nav-buttons :items="[
                ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                ['name' => 'Items Management', 'link' => route('admin.inventory.items.index')],
                ['name' => 'Stocks Management', 'link' => route('admin.inventory.stock.index')],
                ['name' => 'Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
        ]" active="Stocks Management" />

        <!-- Stats Cards -->
        {{-- <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-partials.cards.stats-card title="Total Items" value="{{ $itemsCount ?? 0 }}" trend="Across all branches"
                icon="fas fa-boxes" color="indigo" />

            <x-partials.cards.stats-card title="In Stock" value="{{ $inStockCount ?? 0 }}" trend="Available items"
                icon="fas fa-check-circle" color="green" />

            <x-partials.cards.stats-card title="Low Stock" value="{{ $nearReorderCount ?? 0 }}" trend="Needs reordering"
                icon="fas fa-exclamation-triangle" color="yellow" />

            <x-partials.cards.stats-card title="Out of Stock" value="{{ $outOfStockCount ?? 0 }}" trend="Restock needed"
                icon="fas fa-times-circle" color="red" />
        </div> --}}

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.inventory.stock.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Item name or code"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock
                        </option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of
                            Stock</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    @if (request()->anyFilled(['search', 'branch_id', 'status']))
                        <a href="{{ route('admin.inventory.stock.index') }}"
                            class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Stock Levels Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Inventory Stock Management</h2>
                    <p class="text-sm text-gray-500">Moniter and Manage Your stock levels</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    {{-- <a href="{{ route('admin.inventory.stock.create') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> Add New Stock
                    </a> --}}
                    <a href="{{ route('admin.inventory.stock.transactions.index') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-history mr-2"></i> View History
                    </a>
                </div>
            </div>
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">Stock Levels</h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">{{ $stocks->total() }} total records</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item
                                Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock
                                Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($stocks as $stock)
                            <tr class="hover:bg-gray-50">
                                <!-- Item Details -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="bg-gray-100 p-3 rounded-lg mr-4">
                                            <i class="fas fa-box text-gray-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $stock['item']->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $stock['item']->item_code }} •
                                                {{ optional($stock['item']->category)->name ?? 'Uncategorized' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Branch -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $stock['branch']->name }}</div>
                                </td>

                                <!-- Stock Level -->
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $stock['current_stock'] }}
                                        <span class="text-gray-500">/ {{ $stock['reorder_level'] }}</span>
                                        <span
                                            class="text-xs text-gray-500 ml-1">{{ $stock['item']->unit_of_measurement }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                        @php
                                            $percentage =
                                                $stock['reorder_level'] > 0
                                                    ? min(
                                                        100,
                                                        ($stock['current_stock'] / $stock['reorder_level']) * 100,
                                                    )
                                                    : 0;

                                            $color = match (true) {
                                                $stock['current_stock'] <= 0 => 'bg-red-500',
                                                $stock['status'] === 'low_stock' => 'bg-yellow-500',
                                                default => 'bg-green-500',
                                            };
                                        @endphp
                                        <div class="h-1.5 rounded-full {{ $color }}"
                                            style="width: {{ $percentage }}%"></div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    @if ($stock['current_stock'] <= 0)
                                        <x-partials.badges.status-badge status="danger" text="Out of Stock" />
                                    @elseif($stock['status'] === 'low_stock')
                                        <x-partials.badges.status-badge status="warning" text="Low Stock" />
                                    @else
                                        <x-partials.badges.status-badge status="success" text="In Stock" />
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.inventory.stock.edit', ['item_id' => $stock['item']->id, 'branch_id' => $stock['branch']->id]) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="Add Transaction">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>

                                        <a href="{{ route('admin.inventory.stock.transactions.index', ['item_id' => $stock['item']->id, 'branch_id' => $stock['branch']->id]) }}"
                                            class="text-purple-600 hover:text-purple-800" title="View History">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No stock items found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t">
                {{ $stocks->links() }}
            </div>
        </div>
    </div>
@endsection
