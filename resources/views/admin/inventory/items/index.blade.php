@extends('layouts.admin')

@section('header-title', 'Item Master - Inventory Management')

@section('content')
    <!-- Breadcrumb Navigation -->
    {{-- <x-breadcrumb
        :items="[
            ['name' => 'Inventory Management', 'url' => route('admin.inventory.index')],
            ['name' => 'Item Master']
        ]"
        current="Inventory Items"
        type="inventory" /> --}}

    <div class="p-4 rounded-lg">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                    ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                    ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                    ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                    ['name' => 'Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                    ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
                ]" active="Item Management" />
            </div>
        </div>

        <!-- Inventory System Clarification -->
        {{-- <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Item Master - Inventory Management</h1>
                    <p class="text-gray-600 mb-3">Manage buy & sell items with inventory tracking, stock levels, and supplier information</p>
                    <div class="flex items-center text-sm">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs mr-3">
                            📦 Buy & Sell Items Only
                        </span>
                        <span class="text-gray-500">For KOT recipes (dishes), use</span>
                        <a href="{{ route('admin.menu-items.enhanced.index') }}" class="text-orange-600 hover:text-orange-700 ml-1 font-medium">Menu Items →</a>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.menu-items.enhanced.index') }}"
                       class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 transition-colors">
                        <i class="fas fa-utensils mr-2"></i>Menu Items
                    </a>
                    <a href="{{ route('admin.inventory.items.create') }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Inventory Item
                    </a>
                </div>
            </div>
        </div> --}}

        <!-- System Note -->
        {{-- <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3"></i>
                <div>
                    <h3 class="font-medium text-blue-900 mb-1">Inventory Items vs Menu Items</h3>
                    <p class="text-sm text-blue-800">
                        <strong>This section is for inventory items</strong> that are bought and sold directly (like beverages, packaged foods).
                        For <strong>KOT recipes/dishes</strong> that are cooked using ingredients, create them in the
                        <a href="{{ route('admin.menu-items.enhanced.index') }}" class="text-orange-600 hover:text-orange-700 font-medium">Menu Items section</a>.
                    </p>
                </div>
            </div>
        </div> --}}

            <!-- Enhanced Filters Section -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-filter mr-2 text-indigo-600"></i>
                            Filters & Search
                        </h3>
                        <button type="button" id="toggleFilters" class="text-indigo-600 hover:text-indigo-800">
                            <i class="fas fa-chevron-down" id="filterToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.inventory.items.index') }}" id="filtersForm">
                    <div class="p-4" id="filtersContent">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-search mr-1"></i>Search
                                </label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Name, code, description...">
                            </div>

                            <!-- Category Filter -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-tags mr-1"></i>Category
                                </label>
                                <select name="category" id="category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-toggle-on mr-1"></i>Status
                                </label>
                                <select name="status" id="status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>

                            <!-- Sort By -->
                            <div>
                                <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-sort mr-1"></i>Sort By
                                </label>
                                <select name="sort_by" id="sort_by"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="created_at"
                                        {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>Date Added
                                    </option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name
                                    </option>
                                    <option value="item_code" {{ request('sort_by') == 'item_code' ? 'selected' : '' }}>Item
                                        Code</option>
                                    <option value="selling_price"
                                        {{ request('sort_by') == 'selling_price' ? 'selected' : '' }}>Selling Price
                                    </option>
                                    <option value="buying_price"
                                        {{ request('sort_by') == 'buying_price' ? 'selected' : '' }}>Cost Price</option>
                                </select>
                            </div>
                        </div>

                        <!-- Advanced Filters Row -->
                        {{-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                            <!-- Sort Order -->
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-sort-amount-down mr-1"></i>Order
                                </label>
                                <select name="sort_order" id="sort_order"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>
                                        Newest First</option>
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Oldest
                                        First</option>
                                </select>
                            </div>

                            <!-- Menu Item Filter -->
                            <div>
                                <label for="menu_item" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-utensils mr-1"></i>Menu Items
                                </label>
                                <select name="menu_item" id="menu_item"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Items</option>
                                    <option value="1" {{ request('menu_item') == '1' ? 'selected' : '' }}>Menu Items
                                        Only</option>
                                    <option value="0" {{ request('menu_item') == '0' ? 'selected' : '' }}>Non-Menu
                                        Items</option>
                                </select>
                            </div>

                            <!-- Perishable Filter -->
                            <div>
                                <label for="perishable" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clock mr-1"></i>Perishable
                                </label>
                                <select name="perishable" id="perishable"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">All Items</option>
                                    <option value="1" {{ request('perishable') == '1' ? 'selected' : '' }}>Perishable
                                        Only</option>
                                    <option value="0" {{ request('perishable') == '0' ? 'selected' : '' }}>
                                        Non-Perishable</option>
                                </select>
                            </div>

                            <!-- Quick Date Filters -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar mr-1"></i>Quick Filters
                                </label>
                                <div class="flex space-x-1">
                                    <button type="button" onclick="setDateFilter('today')"
                                        class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                                        Today
                                    </button>
                                    <button type="button" onclick="setDateFilter('week')"
                                        class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded hover:bg-green-200">
                                        This Week
                                    </button>
                                    <button type="button" onclick="setDateFilter('month')"
                                        class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded hover:bg-purple-200">
                                        This Month
                                    </button>
                                </div>
                            </div>
                        </div> --}}

                        <!-- Price Range and Date Range -->
                        {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Price Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-dollar-sign mr-1"></i>Price Range (Rs.)
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" name="price_min" value="{{ request('price_min') }}"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Min price" step="0.01">
                                    <input type="number" name="price_max" value="{{ request('price_max') }}"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Max price" step="0.01">
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar-alt mr-1"></i>Date Range
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div> --}}

                        <!-- Filter Actions -->
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-filter mr-2"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.inventory.items.index') }}"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-times mr-2"></i> Clear All
                            </a>
                            <button type="submit" name="export" value="1"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-file-export mr-2"></i> Export
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Item List -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Item Master (Inventory Items)</h2>
                        <p class="text-sm text-gray-500">
                            @if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator || $items instanceof \Illuminate\Pagination\Paginator)
                                Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of
                                {{ $items->total() ?? 0 }} items
                            @else
                                {{ $items->count() }} items
                            @endif
                            @if (request()->hasAny([
                                'search',
                                'category',
                                'status',
                                'menu_item',
                                'perishable',
                                'price_min',
                                'price_max',
                                'date_from',
                                'date_to',
                            ]))
                                <span class="text-indigo-600 font-medium">(filtered)</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            @if (Auth::guard('admin')->user()->is_super_admin)
                                Organization: All Organizations (Super Admin)
                            @elseif(Auth::guard('admin')->user()->organization)
                                Organization: {{ Auth::guard('admin')->user()->organization->name }}
                            @else
                                Organization: Not Assigned
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('admin.menu-items.enhanced.index') }}"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-utensils mr-2"></i>Menu Items
                        </a>
                        <a href="{{ route('admin.item-categories.create') }}"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> New Item Category
                        </a>
                        <a href="{{ route('admin.inventory.items.create') }}"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> New Item
                        </a>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Code
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name & Details
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cost Price
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Selling Price
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Added
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50 cursor-pointer"
                                    onclick="window.location='{{ route('admin.inventory.items.show', $item->id) }}'">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-indigo-600">{{ $item->item_code }}</span>
                                            @if ($item->created_at->isToday())
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                                    <i class="fas fa-plus-circle mr-1"></i>New Today
                                                </span>
                                            @elseif($item->created_at->isCurrentWeek())
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                                    <i class="fas fa-clock mr-1"></i>This Week
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                                @if ($item->is_menu_item)
                                                    <i class="fas fa-utensils text-indigo-600"></i>
                                                @else
                                                    <i class="fas fa-box text-indigo-600"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                                <div class="text-sm text-gray-500 flex items-center gap-2">
                                                    <span>{{ $item->unit_of_measurement }}</span>
                                                    @if ($item->is_menu_item)
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            Menu Item
                                                        </span>
                                                    @endif
                                                    @if ($item->is_perishable)
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                            Perishable
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                            {{ $item->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-medium">Rs. {{ number_format($item->buying_price, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-medium">Rs. {{ number_format($item->selling_price, 2) }}</span>
                                        @if ($item->selling_price > $item->buying_price)
                                            <div class="text-xs text-green-600">
                                                {{ number_format((($item->selling_price - $item->buying_price) / $item->buying_price) * 100, 1) }}%
                                                margin
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-500">
                                        <div>{{ $item->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs">{{ $item->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-3">
                                            <a href="{{ route('admin.inventory.items.edit', $item->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Items Found</h3>
                                            <p class="text-gray-500 mb-4">
                                                @if (request()->hasAny(['search', 'category', 'status']))
                                                    No items match your current filters.
                                                @else
                                                    You haven't added any items yet.
                                                @endif
                                            </p>
                                            <a href="{{ route('admin.inventory.items.create') }}"
                                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                                <i class="fas fa-plus mr-2"></i> Add Your First Item
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $items->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle filters visibility
                const toggleButton = document.getElementById('toggleFilters');
                const filtersContent = document.getElementById('filtersContent');
                const toggleIcon = document.getElementById('filterToggleIcon');

                toggleButton.addEventListener('click', function() {
                    const isHidden = filtersContent.style.display === 'none';
                    filtersContent.style.display = isHidden ? 'block' : 'none';
                    toggleIcon.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
                });

                // Auto-submit on filter change
                const autoSubmitFields = ['category', 'status', 'menu_item', 'perishable', 'sort_by', 'sort_order'];
                autoSubmitFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field) {
                        field.addEventListener('change', function() {
                            document.getElementById('filtersForm').submit();
                        });
                    }
                });

                // Search with delay
                let searchTimeout;
                const searchField = document.getElementById('search');
                if (searchField) {
                    searchField.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            document.getElementById('filtersForm').submit();
                        }, 1000);
                    });
                }
            });

            // Quick date filter functions
            function setDateFilter(period) {
                const today = new Date();
                const dateFrom = document.querySelector('input[name="date_from"]');
                const dateTo = document.querySelector('input[name="date_to"]');

                switch (period) {
                    case 'today':
                        dateFrom.value = today.toISOString().split('T')[0];
                        dateTo.value = today.toISOString().split('T')[0];
                        break;
                    case 'week':
                        const weekStart = new Date(today.setDate(today.getDate() - today.getDay()));
                        dateFrom.value = weekStart.toISOString().split('T')[0];
                        dateTo.value = new Date().toISOString().split('T')[0];
                        break;
                    case 'month':
                        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                        dateFrom.value = monthStart.toISOString().split('T')[0];
                        dateTo.value = new Date().toISOString().split('T')[0];
                        break;
                }
                document.getElementById('filtersForm').submit();
            }
        </script>
    @endpush
@endsection
