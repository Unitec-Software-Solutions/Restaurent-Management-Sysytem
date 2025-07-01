@extends('layouts.admin')

@section('title', 'Create Stock Transaction')

@section('content')
    <div class="p-4 rounded-lg">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm p-6">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Record Stock Transaction</h2>
                <p class="text-sm text-gray-500"> ~ Dev Use Only~</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <h3 class="font-medium mb-1">Validation Errors:</h3>
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.inventory.stock.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Item Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                    <div class="relative">
                        <select name="inventory_item_id" required
                            class="w-full pl-4 pr-8 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('inventory_item_id', request('item_id')) == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} ({{ $item->item_code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-box text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Branch Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <div class="relative">
                        <select name="branch_id" required
                            class="w-full pl-4 pr-8 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', request('branch_id')) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-store text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Transaction Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
                    <div class="relative">
                        <select name="transaction_type" required
                            class="w-full pl-4 pr-8 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <optgroup label="Stock In">
                                <option value="purchase_order" {{ old('transaction_type') == 'purchase_order' ? 'selected' : '' }}>Purchase Order</option>
                                <option value="return" {{ old('transaction_type') == 'return' ? 'selected' : '' }}>Return</option>
                                <option value="adjustment" {{ old('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                <option value="audit" {{ old('transaction_type') == 'audit' ? 'selected' : '' }}>Audit</option>
                                <option value="transfer_in" {{ old('transaction_type') == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                                <option value="grn_stock_added" {{ old('transaction_type') == 'grn_stock_added' ? 'selected' : '' }}>GRN Stock Added</option>
                            </optgroup>
                            <optgroup label="Stock Out">
                                <option value="sales_order" {{ old('transaction_type') == 'sales_order' ? 'selected' : '' }}>Sales Order</option>
                                <option value="write_off" {{ old('transaction_type') == 'write_off' ? 'selected' : '' }}>Write Off</option>
                                <option value="transfer" {{ old('transaction_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="usage" {{ old('transaction_type') == 'usage' ? 'selected' : '' }}>Usage</option>
                                <option value="transfer_out" {{ old('transaction_type') == 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                                <option value="gtn_outgoing" {{ old('transaction_type') == 'gtn_outgoing' ? 'selected' : '' }}>GTN Outgoing</option>
                                <option value="production_issue" {{ old('transaction_type') == 'production_issue' ? 'selected' : '' }}>Production Issue</option>
                            </optgroup>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-exchange-alt text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" name="quantity" required
                            value="{{ old('quantity') }}"
                            class="w-full pl-4 pr-8 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-hashtag text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center pt-6 border-t">
                    <a href="{{ route('admin.inventory.stock.index') }}"
                       class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Stock
                    </a>
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i> Create Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
