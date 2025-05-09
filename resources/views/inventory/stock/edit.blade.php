@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Adjust Stock</h2>
        <a href="{{ route('inventory.stock.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Back</a>
    </div>

    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Current Stock Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Item:</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $stock->item->name }} ({{ $stock->item->sku }})</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Branch:</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $stock->branch->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Current Quantity:</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ number_format($stock->current_quantity, 3) }} {{ $stock->item->unit_of_measurement }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Available Quantity:</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ number_format($stock->available_quantity, 3) }} {{ $stock->item->unit_of_measurement }}
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('inventory.stock.update', $stock) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Adjustment Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Adjustment Quantity* 
                    <span class="text-xs text-gray-500">(use negative for reduction)</span>
                </label>
                <input type="number" name="adjustment_quantity" value="{{ old('adjustment_quantity') }}" 
                       required step="0.001"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Transaction Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type*</label>
                <select name="transaction_type" required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="adjustment" {{ old('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    <option value="transfer_in" {{ old('transaction_type') == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                    <option value="transfer_out" {{ old('transaction_type') == 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                </select>
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="3" 
                          class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Update Stock
            </button>
        </div>
    </form>
</div>
@endsection