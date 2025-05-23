@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mt-8">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Change Stock levels</h2>
    <p class="text-gray-500 dark:text-gray-400 mb-6">Record a new stock in/out transaction for an item at a branch.</p>

    @if ($errors->any())
        <div class="mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.inventory.stock.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Item -->
        <div>
            <label for="inventory_item_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item</label>
            <select name="inventory_item_id" id="inventory_item_id" required
                class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Item</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ old('inventory_item_id', request('item_id')) == $item->id ? 'selected' : '' }}>
                        {{ $item->name }} ({{ $item->item_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Branch -->
        <div>
            <label for="branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
            <select name="branch_id" id="branch_id" required
                class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', request('branch_id')) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Transaction Type -->
        <div>
            <label for="transaction_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction Type</label>
            <select name="transaction_type" id="transaction_type" required
                class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Type</option>
                <!-- Stock In Types -->
                <optgroup label="Stock In">
                    <option value="purchase_order" {{ old('transaction_type') == 'purchase_order' ? 'selected' : '' }}>Purchase Order</option>
                    <option value="return" {{ old('transaction_type') == 'return' ? 'selected' : '' }}>Return</option>
                    <option value="adjustment" {{ old('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    <option value="audit" {{ old('transaction_type') == 'audit' ? 'selected' : '' }}>Audit</option>
                    <option value="transfer_in" {{ old('transaction_type') == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                </optgroup>
                <!-- Stock Out Types -->
                <optgroup label="Stock Out">
                    <option value="sales_order" {{ old('transaction_type') == 'sales_order' ? 'selected' : '' }}>Sales Order</option>
                    <option value="write_off" {{ old('transaction_type') == 'write_off' ? 'selected' : '' }}>Write Off</option>
                    <option value="transfer" {{ old('transaction_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="usage" {{ old('transaction_type') == 'usage' ? 'selected' : '' }}>Usage</option>
                    <option value="transfer_out" {{ old('transaction_type') == 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                </optgroup>
            </select>
        </div>

        <!-- Quantity -->
        <div>
            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
            <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" required
                value="{{ old('quantity') }}"
                class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Notes -->
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
            <textarea name="notes" id="notes" rows="2"
                class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="{{ route('admin.inventory.stock.index') }}" class="text-gray-600 dark:text-gray-300 hover:underline">
                &larr; Back to Stock List
            </a>
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
                Add Transaction
            </button>
        </div>
    </form>
</div>
@endsection