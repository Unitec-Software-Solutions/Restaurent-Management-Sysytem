@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Add Stock</h2>
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

    <form action="{{ route('inventory.stock.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Item Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item*</label>
                <select name="inventory_item_id" required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} ({{ $item->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Branch Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch*</label>
                <select name="branch_id" required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity*</label>
                <input type="number" name="quantity" value="{{ old('quantity') }}" required step="0.001" min="0"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Transaction Type -->
            <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type*</label>
            <select name="transaction_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                <optgroup label="Incoming Transactions">
                    <option value="purchase" {{ old('transaction_type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="transfer_in" {{ old('transaction_type') == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                    <option value="return" {{ old('transaction_type') == 'return' ? 'selected' : '' }}>Return</option>
                    <option value="grn_adjustment" {{ old('transaction_type') == 'grn_adjustment' ? 'selected' : '' }}>GRN Adjustment</option>
                    <option value="stock_addition" {{ old('transaction_type') == 'stock_addition' ? 'selected' : '' }}>Stock Addition</option>
                    <option value="positive_adjustment" {{ old('transaction_type') == 'positive_adjustment' ? 'selected' : '' }}>Positive Adjustment</option>
                    <option value="stocktake_positive_variance" {{ old('transaction_type') == 'stocktake_positive_variance' ? 'selected' : '' }}>Stocktake Positive Variance</option>
                    <option value="supplier_stock_return" {{ old('transaction_type') == 'supplier_stock_return' ? 'selected' : '' }}>Supplier Stock Return</option>
                    <option value="recipe_reversal" {{ old('transaction_type') == 'recipe_reversal' ? 'selected' : '' }}>Recipe Reversal</option>
                    <option value="stock_replenishment" {{ old('transaction_type') == 'stock_replenishment' ? 'selected' : '' }}>Stock Replenishment</option>
                    <option value="initial_stock" {{ old('transaction_type') == 'initial_stock' ? 'selected' : '' }}>Initial Stock</option>
                </optgroup>
                <optgroup label="Outgoing Transactions">
                    <option value="transfer_out" {{ old('transaction_type') == 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                    <option value="usage" {{ old('transaction_type') == 'usage' ? 'selected' : '' }}>Usage</option>
                    <option value="wastage" {{ old('transaction_type') == 'wastage' ? 'selected' : '' }}>Wastage</option>
                    <option value="negative_adjustment" {{ old('transaction_type') == 'negative_adjustment' ? 'selected' : '' }}>Negative Adjustment</option>
                    <option value="stocktake_negative_variance" {{ old('transaction_type') == 'stocktake_negative_variance' ? 'selected' : '' }}>Stocktake Negative Variance</option>
                    <option value="donation" {{ old('transaction_type') == 'donation' ? 'selected' : '' }}>Donation</option>
                    <option value="theft_or_loss" {{ old('transaction_type') == 'theft_or_loss' ? 'selected' : '' }}>Theft/Loss</option>
                    <option value="supplier_return" {{ old('transaction_type') == 'supplier_return' ? 'selected' : '' }}>Supplier Return</option>
                    <option value="sample_given" {{ old('transaction_type') == 'sample_given' ? 'selected' : '' }}>Sample Given</option>
                    <option value="promotional_item" {{ old('transaction_type') == 'promotional_item' ? 'selected' : '' }}>Promotional Item</option>
                    <option value="employee_meal" {{ old('transaction_type') == 'employee_meal' ? 'selected' : '' }}>Employee Meal</option>
                </optgroup>
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
                Add Stock
            </button>
        </div>
    </form>
</div>
@endsection