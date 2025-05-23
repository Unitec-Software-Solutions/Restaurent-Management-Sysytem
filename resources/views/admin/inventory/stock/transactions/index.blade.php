@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Transactions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">History of all inventory movements</p>
        </div>
        <a href="{{ route('admin.inventory.stock.create') }}" 
           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add Transaction
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
        <form method="GET" action="{{ route('admin.inventory.stock.transactions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Item name"
                       class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <!-- Branch Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                </select>
            </div>
            
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 mt-1">
            </div>
            
            <!-- Filter Buttons -->
            <div class="flex items-end space-x-2 md:col-span-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    Apply Filters
                </button>
                @if(request()->anyFilled(['search', 'branch_id', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('admin.inventory.stock.transactions.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg flex items-center justify-center">
                        Clear
                    </a>
                @endif
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg flex items-center justify-center ml-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                    </svg>
                    Print
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Date
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Item
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Branch
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Quantity
                    </th>
                    {{-- <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Net Stock
                    </th> --}}
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                @forelse ($transactions as $tx)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <!-- Date -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $tx->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $tx->created_at->format('h:i A') }}
                            </div>
                        </td>
                        
                        <!-- Item -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $tx->item->name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $tx->item->item_code }}
                            </div>
                        </td>
                        
                        <!-- Branch -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ optional($tx->branch)->name ?? '-' }}
                            </div>
                        </td>
                        
                        @php
                            $inTypes = ['purchase_order', 'return', 'adjustment', 'audit', 'transfer_in'];
                            $outTypes = ['sales_order', 'write_off', 'transfer', 'usage', 'transfer_out'];
                            $isIn = in_array($tx->transaction_type, $inTypes);
                        @endphp

                        <!-- Type -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full 
                                {{ $isIn ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' }}">
                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 {{ $isIn ? 'text-green-500 dark:text-green-300' : 'text-red-500 dark:text-red-300' }}" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                {{ ucwords(str_replace('_', ' ', $tx->transaction_type)) }}
                            </span>
                        </td>

                        <!-- Quantity -->
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm {{ $isIn ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $isIn ? '+' : '-' }}{{ number_format($tx->quantity, 2) }}
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->item->unit_of_measurement }}</span>
                            </div>
                        </td>
                        
                        <!-- Net Stock -->
                        {{-- <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ number_format($tx->net_quantity, 2) }}
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->item->unit_of_measurement }}</span>
                            </div>
                        </td> --}}
                        
                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('admin.inventory.stock.show', $tx->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    View
                                </a>
                                {{-- <a href="{{ route('admin.inventory.stock.edit', $tx->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                    Edit
                                </a>
                                <form action="{{ route('admin.inventory.stock.destroy', $tx->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this transaction?')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </form> --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No transactions found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
@endsection