@extends('layouts.admin')

@section('header-title', 'Item Management')

@section('content')
    <div class="p-4 rounded-lg">
        {{-- Debug Info Card for Inventory Items --}}
        {{-- @if(config('app.debug'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-medium text-green-800">🔍 Inventory Items Debug Info</h3>
                    <a href="{{ route('admin.inventory.items.index', ['debug' => 1]) }}"
                        class="text-xs text-green-600 hover:text-green-800">
                        Full Debug (debug=1)
                    </a>
                </div>
                <div class="text-xs text-green-700 mt-2 grid grid-cols-3 gap-4">
                    <div>
                        <p><strong>Items Variable:</strong>
                            {{ isset($items) ? 'Set (' . $items->count() . ')' : 'NOT SET' }}</p>
                        <p><strong>DB Total Items:</strong> {{ \App\Models\ItemMaster::count() }}</p>
                    </div>
                    <div>
                        <p><strong>Categories Variable:</strong>
                            {{ isset($categories) ? 'Set (' . $categories->count() . ')' : 'NOT SET' }}</p>
                        <p><strong>DB Total Categories:</strong> {{ \App\Models\ItemCategory::count() }}</p>
                    </div>
                    <div>
                        <p><strong>Admin:</strong> {{ auth('admin')->check() ? 'Authenticated' : 'NOT AUTH' }}</p>
                        <p><strong>Branch:</strong> {{ auth('admin')->user()->branch->name ?? 'None' }}</p>
                    </div>
                </div>
            </div>
        @endif --}}

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
                ]" active="Item Management" />
            </div>

            <!-- Filters with Export -->
            <x-module-filters
                :action="route('admin.inventory.items.index')"
                :export-permission="'export_inventory_items'"
                :export-filename="'inventory_items_export.xlsx'">

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" id="category"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </x-module-filters>

            <!-- Item List -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Inventory Items</h2>
                        <p class="text-sm text-gray-500">
                            @if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator || $items instanceof \Illuminate\Pagination\Paginator)
                                Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of
                                {{ $items->total() ?? 0 }} items
                            @else
                                {{ $items->count() }} items
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Organization: {{ Auth::user()->organization->name }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="#"
                            class="bg-indigo-600 hover:bg-indigo-700 opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none">
                            <i class="fas fa-file-export mr-2"></i> Export
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
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cost
                                    / Buying price
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price</th>
                                {{-- <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th> --}}
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50 cursor-pointer"
                                    onclick="window.location='{{ route('admin.inventory.items.show', $item->id) }}'">
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
                                                <div class="text-sm text-gray-500">{{ $item->unit_of_measurement }}
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
                                        Rs. {{ number_format($item->buying_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        Rs. {{ number_format($item->selling_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-3">
                                            {{-- <a href="{{ route('admin.inventory.items.show', $item->id) }}"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a> --}}
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
