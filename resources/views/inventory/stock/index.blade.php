@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">

    <!-- Delete Notification -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Inventory Stock Management</h2>
        <a href="{{ route('inventory.stock.create') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add Stock</a>
    </div>
    <!-- Delete Notification -->

    <!-- Filters -->
    <div class="mb-6">
        <form method="GET" action="{{ route('inventory.stock.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Item</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or SKU"
                       class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Branch Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Branch</label>
                <select name="branch_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Button -->
            <div class="flex items-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Apply Filters
                </button>
                @if(request('search') || request('branch_id'))
                    <a href="{{ route('inventory.stock.index') }}" 
                       class="ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
    <!-- Filters -->
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Item
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Branch
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Current Stock
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Available
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                @forelse($stocks as $stock)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $stock->item->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stock->item->sku }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $stock->branch->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ number_format($stock->current_quantity, 3) }} {{ $stock->item->unit_of_measurement }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ number_format($stock->available_quantity, 3) }} {{ $stock->item->unit_of_measurement }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($stock->item->isLowStock($stock->branch_id))
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                           {{ $stock->current_quantity <= ($stock->item->reorder_level / 2) 
                                              ? 'bg-red-100 text-red-800' 
                                              : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $stock->current_quantity <= ($stock->item->reorder_level / 2) ? 'Critical' : 'Low Stock' }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Normal
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-3">
                                <a href="{{ route('inventory.stock.edit', $stock) }}" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    Adjust
                                </a>
                                <form action="{{ route('inventory.stock.destroy', $stock) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Are you sure you want to delete this stock item?')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No stock records found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $stocks->links() }}
    </div>
</div>
@endsection