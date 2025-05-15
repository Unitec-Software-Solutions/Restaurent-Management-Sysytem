@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <!-- Notifications -->
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
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Item Details</h2>
        <div class="flex space-x-4">
            <a href="{{ route('admin.inventory.items.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                Back to Items
            </a>
            <a href="{{ route('admin.inventory.items.edit', $item) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Edit Item
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Item Details -->
        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
            
            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-600 dark:text-gray-300">Name:</span>
                    <span class="col-span-2 font-medium text-gray-900 dark:text-white">{{ $item->name }}</span>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-600 dark:text-gray-300">SKU:</span>
                    <span class="col-span-2 font-medium text-gray-900 dark:text-white">{{ $item->sku }}</span>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-600 dark:text-gray-300">Category:</span>
                    <span class="col-span-2 font-medium text-gray-900 dark:text-white">{{ $item->category->name }}</span>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-600 dark:text-gray-300">Unit of Measurement:</span>
                    <span class="col-span-2 font-medium text-gray-900 dark:text-white">{{ $item->unit_of_measurement }}</span>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-600 dark:text-gray-300">Reorder Level:</span>
                    <span class="col-span-2 font-medium text-gray-900 dark:text-white">{{ $item->reorder_level }} {{ $item->unit_of_measurement }}</span>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-600 dark:text-gray-300">Status:</span>
                    <span class="col-span-2">
                        @if($item->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </span>
                </div>
                
                @if($item->is_perishable)
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-600 dark:text-gray-300">Perishable:</span>
                    <span class="col-span-2 font-medium text-gray-900 dark:text-white">
                        Yes (Shelf Life: {{ $item->shelf_life_days }} days)
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Stock Information -->
        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Stock Information</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-600 dark:bg-gray-700">
                        @forelse($item->stocks as $stock)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $stock->branch->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $stock->current_quantity }} {{ $item->unit_of_measurement }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($stock->current_quantity <= 0)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    @elseif($stock->current_quantity <= $item->reorder_level)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            In Stock
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No stock information available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="mt-8 bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Transactions</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Branch</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-600 dark:bg-gray-700">
                    @forelse($item->transactions()->latest()->take(5)->get() as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $transaction->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ ucfirst($transaction->transaction_type) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $transaction->quantity }} {{ $item->unit_of_measurement }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($transaction->unit_price)
                                    ${{ number_format($transaction->unit_price, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $transaction->branch->name ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No transaction history available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($item->transactions()->count() > 5)
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.inventory.transactions.index', ['item_id' => $item->id]) }}" 
                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                        View All Transactions
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection