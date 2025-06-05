@extends('layouts.admin')

@section('title', 'Edit Stock ')

@section('content')
    <div class="p-4 rounded-lg">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm p-6">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Edit Stock </h2>
                <p class="text-sm text-gray-500">Update details for inventory tracking</p>
            </div>

<form action="{{ route('admin.inventory.stock.update', ['item_id' => $transaction->inventory_item_id, 'branch_id' => $transaction->branch_id]) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Item Selectio (Disabled) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                    <div class="relative">
                        <select name="inventory_item_id" disabled
                            class="w-full pl-4 pr-8 py-2.5 border rounded-lg bg-gray-100 cursor-not-allowed">
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-unit="{{ $item->unit_of_measurement }}" 
                                    {{ $transaction->inventory_item_id == $item->id ? 'selected' : '' }}>
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
                        <select name="branch_id" disabled
                            class="w-full pl-4 pr-8 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $transaction->branch_id == $branch->id ? 'selected' : '' }}>
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
                                <option value="purchase_order" {{ $transaction->transaction_type == 'purchase_order' ? 'selected' : '' }}>Purchase Order</option>
                                <option value="return" {{ $transaction->transaction_type == 'return' ? 'selected' : '' }}>Return</option>
                                <option value="adjustment" {{ $transaction->transaction_type == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                <option value="audit" {{ $transaction->transaction_type == 'audit' ? 'selected' : '' }}>Audit</option>
                                <option value="transfer_in" {{ $transaction->transaction_type == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                            </optgroup>
                            <optgroup label="Stock Out">
                                <option value="sales_order" {{ $transaction->transaction_type == 'sales_order' ? 'selected' : '' }}>Sales Order</option>
                                <option value="write_off" {{ $transaction->transaction_type == 'write_off' ? 'selected' : '' }}>Write Off</option>
                                <option value="transfer" {{ $transaction->transaction_type == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="usage" {{ $transaction->transaction_type == 'usage' ? 'selected' : '' }}>Usage</option>
                                <option value="transfer_out" {{ $transaction->transaction_type == 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
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
                            value="{{ old('quantity', $transaction->quantity) }}"
                            class="w-full pl-4 pr-8 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <span class="text-gray-500 text-sm">
                                {{ $transaction->item->unit_of_measurement }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $transaction->notes) }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center pt-6 border-t">
                    <a href="{{ route('admin.inventory.stock.index') }}" 
                       class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Stock
                    </a>
                    <div class="flex space-x-3">
                        <button type="reset" 
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg">
                            Reset Changes
                        </button>
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg flex items-center">
                            <i class="fas fa-save mr-2"></i> Update stock
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemSelect = document.getElementById('inventory_item_id');
            const unitDisplay = document.querySelector('#unit-label');
            
            if(itemSelect && unitDisplay) {
                itemSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    unitDisplay.textContent = selectedOption.getAttribute('data-unit');
                });
            }
        });
    </script>
@endsection