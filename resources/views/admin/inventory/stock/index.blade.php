@extends('layouts.main')
@section('content')
    <div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <!-- Header with Stats -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory Dashboard</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Real-time stock levels across branches</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.inventory.stock.create') }}"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Add Stock
                    </a>
                    <a href="{{ route('admin.inventory.stock.transactions.index') }}"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd"
                                d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                clip-rule="evenodd" />
                        </svg>
                        View History
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg border border-blue-100 dark:border-blue-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Items</p>
                            <p class="text-2xl font-bold text-blue-900 dark:text-white">{{ $itemsCount ?? 0 }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-800/50 text-blue-600 dark:text-blue-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg border border-green-100 dark:border-green-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">In Stock</p>
                            <p class="text-2xl font-bold text-green-900 dark:text-white">{{ $inStockCount ?? 0 }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-800/50 text-green-600 dark:text-green-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-yellow-50 dark:bg-yellow-900/30 p-4 rounded-lg border border-yellow-100 dark:border-yellow-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Low Stock</p>
                            <p class="text-2xl font-bold text-yellow-900 dark:text-white">{{ $nearReorderCount ?? 0 }}</p>
                        </div>
                        <div
                            class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-800/50 text-yellow-600 dark:text-yellow-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-lg border border-red-100 dark:border-red-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">Out of Stock</p>
                            <p class="text-2xl font-bold text-red-900 dark:text-white">{{ $outOfStockCount ?? 0 }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-red-100 dark:bg-red-800/50 text-red-600 dark:text-red-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
            <form method="GET" action="{{ route('admin.inventory.stock.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Item name or code"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                    <select name="branch_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
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
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center justify-center w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                clip-rule="evenodd" />
                        </svg>
                        Filter
                    </button>
                    @if (request()->anyFilled(['search', 'branch_id', 'status']))
                        <a href="{{ route('admin.inventory.stock.index') }}"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg flex items-center justify-center w-full">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Stock Levels Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Item Details
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Branch
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Stock Level
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($stocks as $stock)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <!-- Item Details -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-gray-200 dark:bg-gray-600 rounded-md flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 text-gray-600 dark:text-gray-300" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $stock['item']->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $stock['item']->item_code }} •
                                            {{ optional($stock['item']->category)->name ?? 'Uncategorized' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Branch -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $stock['branch']->name }}</div>
                            </td>

                            <!-- Stock Level -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">{{ $stock['current_stock'] }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/ {{ $stock['reorder_level'] }}</span>
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400 ml-1">{{ $stock['item']->unit_of_measurement }}</span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700 mt-1">
                                    @php
                                        $percentage =
                                            $stock['reorder_level'] > 0
                                                ? min(100, ($stock['current_stock'] / $stock['reorder_level']) * 100)
                                                : 0;

                                        if ($stock['current_stock'] <= 0) {
                                            $color = 'bg-red-500';
                                        } elseif ($stock['status'] === 'low_stock') {
                                            $color = 'bg-yellow-500';
                                        } else {
                                            $color = 'bg-green-500';
                                        }
                                    @endphp
                                    <div class="h-1.5 rounded-full {{ $color }}"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($stock['current_stock'] <= 0)
                                    <span
                                        class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-500 dark:text-red-300"
                                            fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Out of Stock
                                    </span>
                                @elseif($stock['status'] === 'low_stock')
                                    <span
                                        class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-500 dark:text-yellow-300"
                                            fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Low Stock
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-500 dark:text-green-300"
                                            fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        In Stock
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.inventory.stock.create', ['item_id' => $stock['item']->id, 'branch_id' => $stock['branch']->id]) }}"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30"
                                        title="Add Transaction">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.inventory.stock.transactions.index', ['item_id' => $stock['item']->id, 'branch_id' => $stock['branch']->id]) }}"
                                        class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 px-2 py-1 rounded hover:bg-purple-50 dark:hover:bg-purple-900/30"
                                        title="View History">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                            <path fill-rule="evenodd"
                                                d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No stock items found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $stocks->links() }}
        </div>
    </div>
@endsection
