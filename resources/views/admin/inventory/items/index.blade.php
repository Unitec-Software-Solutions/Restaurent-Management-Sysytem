@extends('layouts.admin')
@section('header-title', 'Items Management')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Navigation Buttons -->
        <x-nav-buttons :items="[
            ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
            ['name' => 'Items Management', 'link' => route('admin.inventory.items.index')],
            ['name' => 'Stocks Management', 'link' => route('admin.inventory.stock.index')],
            ['name' => 'Transactions Management', 'link' => route('admin.inventory.stock.transactions.index')],
        ]" active="Items Management" />

        <!-- Header with KPI Cards -->
        {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Items Card -->
            <x-partials.cards.stats-card title="Total Items" value="{{ $totalItems }}"
                trend="{{ $newItemsToday > 0 ? '+' : '' }}{{ $newItemsToday }} today" icon="fas fa-box-open" color="indigo" />

            <!-- Active Items Card -->
            <x-partials.cards.stats-card title="Active Items" value="{{ $activeItems }}"
                trend="{{ $activeItemsChange > 0 ? '+' : '' }}{{ $activeItemsChange }} from yesterday"
                icon="fas fa-check-circle" color="green" />

            <!-- Inactive Items Card -->
            <x-partials.cards.stats-card title="Inactive Items" value="{{ $inactiveItems }}"
                trend="{{ $inactiveItemsChange > 0 ? '+' : '' }}{{ $inactiveItemsChange }} from yesterday"
                icon="fas fa-times-circle" color="red" />
        </div> --}}


        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header with Actions -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Item Management</h2>
                    <p class="text-sm text-gray-500">Manage all inventory items in your system</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.inventory.items.create') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> Add New Item
                    </a>
                    <a href="{{ route('admin.inventory.stock.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-warehouse mr-2"></i> View Stock
                    </a>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="p-6 border-b">
                <form method="GET" action="{{ route('admin.inventory.items.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search Input -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <div class="relative">
                                <input type="text" id="search" name="search" placeholder="Search items..."
                                    value="{{ request('search') }}"
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="category" name="category"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ request('category') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            {{-- <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                             <select id="status" name="status"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select> --}}
                        </div>

                        <!-- Filter Button -->
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                                <i class="fas fa-filter mr-2"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost / Buying price
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price</th>
                            {{-- <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th> --}}
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-indigo-600">{{ $item->item_code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->unit_of_measurement }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                        {{ $item->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    Rs. {{ number_format($item->buying_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    Rs. {{ number_format($item->selling_price, 2) }}
                                </td>
                                {{-- <td class="px-6 py-4 text-center">
                                    @if ($item->deleted_at)
                                        <x-partials.badges.status-badge status="danger" text="Inactive" />
                                    @else
                                        <x-partials.badges.status-badge status="success" text="Active" />
                                    @endif
                                </td> --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-3">
                                        <a href="{{ route('admin.inventory.items.show', $item->id) }}"
                                                        class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.items.edit', $item->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        {{-- @if ($item->deleted_at)
                                            <form action="{{ route('admin.inventory.items.restore', $item->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="text-green-600 hover:text-green-900"
                                                    title="Restore"
                                                    onclick="return confirm('Are you sure you want to restore this item?')">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.inventory.items.destroy', $item->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No items found matching your criteria
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
