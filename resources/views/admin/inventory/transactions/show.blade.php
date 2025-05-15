@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Transaction Details</h2>
        <div class="flex space-x-2">
            <a href="{{ route('admin.inventory.transactions.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Back</a>
            <button onclick="window.print()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Print
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Transaction Information -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Transaction Information</h3>
            <dl class="grid grid-cols-1 gap-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Reference No.</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">#{{ $transaction->id }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date & Time</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ $transaction->created_at->format('M d, Y H:i:s') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $transaction->getTypeColor() }}">
                            {{ $transaction->transaction_type }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Branch</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $transaction->branch->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Performed By</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $transaction->user->name }}</dd>
                </div>
            </dl>
        </div>

        <!-- Item Details -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Item Details</h3>
            <dl class="grid grid-cols-1 gap-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Item Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $transaction->item->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SKU</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $transaction->item->sku }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ $transaction->item->category->name }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Quantity</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ number_format($transaction->quantity, 3) }} {{ $transaction->item->unit_of_measurement }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unit Price</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        ${{ number_format($transaction->unit_price, 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Value</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-bold">
                        ${{ number_format($transaction->total_value, 2) }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Notes -->
        @if($transaction->notes)
        <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Notes</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $transaction->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection