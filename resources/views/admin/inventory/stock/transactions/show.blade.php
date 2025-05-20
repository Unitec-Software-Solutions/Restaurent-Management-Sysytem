@extends('layouts.main')
@section('content')
<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Transaction Details</h2>

    <div class="space-y-4">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Item</p>
            <p class="text-gray-900 dark:text-white">{{ $transaction->item->name }} ({{ $transaction->item->item_code }})</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Branch</p>
            <p class="text-gray-900 dark:text-white">{{ optional($transaction->branch)->name ?? 'N/A' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
            <p class="text-gray-900 dark:text-white">{{ ucfirst($transaction->transaction_type) }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Quantity</p>
            <p class="text-gray-900 dark:text-white">
                {{ number_format($transaction->quantity, 2) }} {{ $transaction->item->unit_of_measurement }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Net Quantity</p>
            <p class="text-gray-900 dark:text-white">
                {{ number_format($transaction->net_quantity, 2) }} {{ $transaction->item->unit_of_measurement }}
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Date</p>
            <p class="text-gray-900 dark:text-white">{{ $transaction->created_at->format('Y-m-d H:i') }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Notes</p>
            <p class="text-gray-900 dark:text-white">{{ $transaction->notes ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-6 flex justify-between">
        {{-- <a href="{{ route('admin.inventory.stock.edit', $transaction) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Edit</a> --}}
        <a href="{{ route('admin.inventory.stock.transactions.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back to List</a>
    </div>
</div>
@endsection