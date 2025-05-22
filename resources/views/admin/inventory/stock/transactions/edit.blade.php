@extends('layouts.main')
@section('content')
<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Edit Stock Transaction</h2>

    <form action="{{ route('admin.inventory.stock.update', $transaction) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Item -->
        <div>
            <label for="inventory_item_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item</label>
            <select name="inventory_item_id" id="inventory_item_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white" disabled>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" data-unit="{{ $item->unit_of_measurement }}" {{ $transaction->inventory_item_id == $item->id ? 'selected' : '' }}>
                        {{ $item->name }} ({{ $item->item_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Branch -->
        <div>
            <label for="branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch</label>
            <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $transaction->branch_id == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Transaction Type -->
<div>
    <label for="transaction_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type</label>
    <select name="transaction_type" id="transaction_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white" required>
        <optgroup label="Stock In">
            <option value="purchase_order">Purchase Order</option>
            <option value="return">Return</option>
            <option value="adjustment">Adjustment</option>
            <option value="audit">Audit</option>
            <option value="transfer_in">Transfer In</option>
        </optgroup>
        <optgroup label="Stock Out">
            <option value="sales_order">Sales Order</option>
            <option value="write_off">Write Off</option>
            <option value="transfer">Transfer</option>
            <option value="usage">Usage</option>
            <option value="transfer_out">Transfer Out</option>
        </optgroup>
    </select>
</div>

        <!-- Quantity -->
        <div>
            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
            <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white"
                   value="{{ old('quantity', $transaction->quantity) }}" required>
            <small class="text-gray-500 dark:text-gray-400 mt-1" id="unit-label">{{ $transaction->item->unit_of_measurement }}</small>
        </div>

        <!-- Notes -->
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">{{ old('notes', $transaction->notes) }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.inventory.stock.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Update Transaction</button>
        </div>
    </form>
</div>

<script>
    // Show unit of measurement dynamically
    document.getElementById('inventory_item_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const unit = selected.getAttribute('data-unit');
        document.getElementById('unit-label').textContent = 'Unit: ' + unit;
    });
</script>
@endsection