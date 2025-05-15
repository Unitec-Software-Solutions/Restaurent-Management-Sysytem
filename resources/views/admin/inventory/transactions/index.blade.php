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
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Transactions Records</h2>
       
    </div>
    <!-- Delete Notification -->

    <!-- Filters -->
<div class="mb-6">
    <form method="GET" action="{{ route('admin.inventory.transactions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Date Range Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
            <select name="date_range" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                <option value="">All Time</option>
                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
        </div>

        <!-- Custom Date Range (initially hidden) -->
        <div id="custom_dates" class="grid grid-cols-2 gap-2" style="{{ request('date_range') == 'custom' ? '' : 'display: none;' }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <!-- Transaction Type Filter -->
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

        <!-- Branch Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
            <select name="branch_id" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by item name or SKU"
                   class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Buttons -->
        <div class="md:col-span-2 flex justify-end space-x-2">
            <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                Apply Filters
            </button>
            @if(request()->hasAny(['date_range', 'transaction_type', 'branch_id', 'search', 'start_date', 'end_date']))
                <a href="{{ route('admin.inventory.transactions.index') }}" 
                class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600">
                    Clear Filters
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
                        Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Item
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Quantity
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Value
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Branch
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $transaction->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $transaction->getTypeColor() }}">
                                {{ $transaction->transaction_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $transaction->item->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $transaction->item->sku }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($transaction->quantity, 3) }} {{ $transaction->item->unit_of_measurement }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${{ number_format($transaction->quantity * $transaction->unit_price, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $transaction->branch->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.inventory.transactions.show', $transaction) }}" 
                            class="px-2 py-1 text-xs text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No transactions found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
@endsection


<!-- JavaScript for the date range filter -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeSelect = document.querySelector('select[name="date_range"]');
    const customDatesDiv = document.getElementById('custom_dates');

    dateRangeSelect.addEventListener('change', function() {
        customDatesDiv.style.display = this.value === 'custom' ? 'grid' : 'none';
    });
});
</script>
@endpush