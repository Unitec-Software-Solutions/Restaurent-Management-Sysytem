@extends('layouts.admin')

@section('header-title', 'Stock Management')
{{-- @section('header-subtitle', 'Overview of your metrics') --}}
@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header with buttons -->
        <x-nav-buttons :items="[
            ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
            ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
            ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
            ['name' => 'Stock Release Notes', 'link' => route('admin.inventory.srn.index')],
            ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
            ['name' => 'Goods Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
            ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
        ]" active="Stock Management" />


        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.inventory.stock.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Item</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Enter item name or code" aria-label="Search items" autocomplete="on"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                        <option value="">All Status</option>
                        <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock
                        </option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of
                            Stock</option>
                    </select>
                </div>
                <!-- Sort By -->
                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-400 mb-1">Sort By</label>
                    <select name="sort_by" id="sort_by"
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-400" disabled>
                        <option value="">Default</option>
                        <option value="name_asc" {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>
                            Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>
                            Name (Z-A)</option>
                        <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>
                            Price (Low to High)</option>
                        <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>
                            Price (High to Low)</option>
                    </select>
                </div>
                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('admin.inventory.stock.index') }}"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Stock List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Inventory Stock Management</h2>
                    <p class="text-sm text-gray-500">
                        @if (
                            $stocks instanceof \Illuminate\Pagination\LengthAwarePaginator ||
                                $stocks instanceof \Illuminate\Pagination\Paginator)
                            Showing {{ $stocks->firstItem() ?? 0 }} to {{ $stocks->lastItem() ?? 0 }} of
                            {{ $stocks->total() ?? 0 }} items
                        @else
                            {{ $stocks->count() }} items
                        @endif
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if(Auth::guard('admin')->user()->is_super_admin)
                            Organization: All Organizations (Super Admin)
                        @elseif(Auth::guard('admin')->user()->organization)
                            Organization: {{ Auth::guard('admin')->user()->organization->name }}
                        @else
                            Organization: Not Assigned
                        @endif
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="#"
                        class="bg-indigo-600 hover:bg-indigo-700 opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none">
                        <i class="fas fa-file-export mr-2"></i> Export
                    </a>
                    <a href="{{ route('admin.inventory.stock.transactions.index') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-history mr-2"></i> View History
                    </a>
                </div>
            </div>

            <!-- Stock Levels Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Item
                                Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock
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
                                            style="width: {{ $percentage }}%">
                                        </div>
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
                                        <a
                                    href="{{ route('admin.inventory.stock.edit', ['item_id' => $stock['item']->id, 'branch_id' => $stock['branch']->id]) }}"
                                    class="text-indigo-600 hover:text-indigo-800" title="Add Transaction">
                                    <i class="fas fa-plus-circle"></i>
                                </a>

                                        <a href="{{ route('admin.inventory.stock.transactions.index', [
                                            'search' => $stock['item']->item_code,
                                            'branch_id' => $stock['branch']->id,
                                            'transaction_type' => '',
                                            'date_from' => '',
                                            'date_to' => '',
                                        ]) }}"
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
