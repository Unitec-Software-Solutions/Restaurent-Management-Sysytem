@extends('layouts.admin')

@section('title', 'Stock Transactions')
@section('content')
<div class="p-4 rounded-lg">
    <div class="max-w-7xl mx-auto bg-white rounded-xl shadow-sm p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Stock Transactions</h2>
                <p class="text-sm text-gray-500">History of all inventory movements</p>
            </div>
            <a href="{{ route('admin.inventory.stock.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus-circle mr-2"></i> Add Transaction
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('admin.inventory.stock.transactions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Item name"
                               class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <div class="relative">
                        <select name="branch_id" class="w-full pl-4 pr-8 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-store absolute right-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <div class="relative">
                        <select name="type" class="w-full pl-4 pr-8 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                            <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                        </select>
                        <i class="fas fa-filter absolute right-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="md:col-span-4 flex justify-between items-center space-x-3">
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center">
                            <i class="fas fa-filter mr-2"></i> Apply Filters
                        </button>
                        @if(request()->anyFilled(['search', 'branch_id', 'type', 'date_from', 'date_to']))
                            <a href="{{ route('admin.inventory.stock.transactions.index') }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg flex items-center">
                                Clear
                            </a>
                        @endif
                    </div>
                    <button type="button" onclick="window.print()" 
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg flex items-center">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $tx->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $tx->created_at->format('h:i A') }}</div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $tx->item->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $tx->item->item_code }}</div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ optional($tx->branch)->name ?? '-' }}
                                </td>
                                
                                @php
                                    $inTypes = ['purchase_order', 'return', 'adjustment', 'audit', 'transfer_in'];
                                    $isIn = in_array($tx->transaction_type, $inTypes);
                                @endphp

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-partials.badges.status-badge 
                                        :color="$isIn ? 'green' : 'red'"
                                        :text="ucwords(str_replace('_', ' ', $tx->transaction_type))" />
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="{{ $isIn ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $isIn ? '+' : '-' }}{{ number_format($tx->quantity, 2) }}
                                        <span class="text-xs text-gray-500">{{ $tx->item->unit_of_measurement }}</span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.inventory.stock.show', $tx->id) }}" 
                                           class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No transactions found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($transactions->hasPages())
                <div class="p-4 border-t">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection