@extends('layouts.admin')

@section('content')
<div class="p-4 rounded-lg">
    <!-- Header with breadcrumb -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
    
        </div>
        <a href="{{ route('admin.grn.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Back to GRNs
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('admin.grn.store') }}" id="grnForm">
            @csrf

            <!-- GRN Details Section -->
            <div class="p-6 border-b">
                <h3 class="text-lg font-medium text-gray-900 mb-4">GRN Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Supplier Selection -->
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1 required">Supplier</label>
                        <select name="supplier_id" id="supplier_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('supplier_id') border-red-500 @enderror">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Branch Selection -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1 required">Branch</label>
                        <select name="branch_id" id="branch_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('branch_id') border-red-500 @enderror">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Received Date -->
                    <div>
                        <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1 required">Received Date</label>
                        <input type="date" name="received_date" id="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('received_date') border-red-500 @enderror">
                        @error('received_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Delivery Note Number -->
                    <div class="md:col-span-1">
                        <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note No.</label>
                        <input type="text" name="delivery_note_number" id="delivery_note_number" value="{{ old('delivery_note_number') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('delivery_note_number') border-red-500 @enderror">
                        @error('delivery_note_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Invoice Number -->
                    <div class="md:col-span-1">
                        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-1">Invoice No.</label>
                        <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('invoice_number') border-red-500 @enderror">
                        @error('invoice_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-3">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="notes" rows="2"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="p-6 border-b">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">GRN Items</h3>
                    <button type="button" id="addItemBtn" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                        <i class="fas fa-plus mr-1"></i> Add Item
                    </button>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic items will be added here -->
                            <tr id="noItemsRow">
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No items added yet
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Total</td>
                                <td id="grandTotal" class="px-6 py-3 font-medium">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="p-6 flex justify-end space-x-3">
                <button type="reset" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg">
                    <i class="fas fa-redo mr-2"></i> Reset
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i> Save GRN
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Item Template (Hidden) -->
<template id="itemTemplate">
    <tr class="item-row">
        <td class="px-6 py-4 whitespace-nowrap">
            <select name="items[][item_id]" class="item-select w-full px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
                <option value="">Select Item</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" data-price="{{ $item->buying_price }}">
                        {{ $item->name }} ({{ $item->code }})
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <input type="text" name="items[][batch_no]" class="batch-no w-full px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <input type="number" name="items[][ordered_quantity]" min="0" step="0.01" class="ordered-qty w-full px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <input type="number" name="items[][received_quantity]" min="0" step="0.01" class="received-qty w-full px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <input type="number" name="items[][buying_price]" min="0" step="0.0001" class="price w-full px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="line-total">0.00</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <button type="button" class="remove-item text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsContainer = document.getElementById('itemsContainer');
        const noItemsRow = document.getElementById('noItemsRow');
        const addItemBtn = document.getElementById('addItemBtn');
        const itemTemplate = document.getElementById('itemTemplate');
        const grandTotalElement = document.getElementById('grandTotal');
        
        let itemCounter = 0;
        let grandTotal = 0;

        // Add new item row
        addItemBtn.addEventListener('click', function() {
            if (noItemsRow) {
                noItemsRow.remove();
            }
            
            const newRow = itemTemplate.content.cloneNode(true);
            const newItemRow = newRow.querySelector('.item-row');
            
            // Update names with index
            const inputs = newItemRow.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name').replace('[]', `[${itemCounter}]`);
                input.setAttribute('name', name);
            });
            
            // Add event listeners for calculations
            addCalculationEvents(newItemRow);
            
            itemsContainer.appendChild(newRow);
            itemCounter++;
        });

        // Add calculation events to a row
        function addCalculationEvents(row) {
            const receivedQtyInput = row.querySelector('.received-qty');
            const orderedQtyInput = row.querySelector('.ordered-qty');
            const priceInput = row.querySelector('.price');
            const lineTotalElement = row.querySelector('.line-total');
            const itemSelect = row.querySelector('.item-select');
            const removeBtn = row.querySelector('.remove-item');
            
            // Set ordered quantity equal to received quantity by default
            receivedQtyInput.addEventListener('input', function() {
                orderedQtyInput.value = this.value;
                calculateLineTotal(row);
            });
            
            // Calculate when any value changes
            [receivedQtyInput, priceInput].forEach(input => {
                input.addEventListener('input', function() {
                    calculateLineTotal(row);
                });
            });
            
            // Set price when item is selected
            itemSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const price = selectedOption.getAttribute('data-price');
                    priceInput.value = price || '0';
                    calculateLineTotal(row);
                }
            });
            
            // Remove row
            removeBtn.addEventListener('click', function() {
                row.remove();
                calculateGrandTotal();
                
                // Show "no items" row if container is empty
                if (itemsContainer.querySelectorAll('.item-row').length === 0) {
                    itemsContainer.appendChild(noItemsRow);
                }
            });
        }
        
        // Calculate line total for a row
        function calculateLineTotal(row) {
            const receivedQty = parseFloat(row.querySelector('.received-qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const lineTotal = receivedQty * price;
            
            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            calculateGrandTotal();
        }
        
        // Calculate grand total
        function calculateGrandTotal() {
            grandTotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const lineTotal = parseFloat(row.querySelector('.line-total').textContent) || 0;
                grandTotal += lineTotal;
            });
            
            grandTotalElement.textContent = grandTotal.toFixed(2);
        }
        
        // Form validation
        document.getElementById('grnForm').addEventListener('submit', function(e) {
            const itemRows = document.querySelectorAll('.item-row');
            if (itemRows.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the GRN');
                return false;
            }
            
            let isValid = true;
            itemRows.forEach(row => {
                const itemSelect = row.querySelector('.item-select');
                const receivedQty = row.querySelector('.received-qty');
                
                if (!itemSelect.value || !receivedQty.value) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields for all items');
                return false;
            }
        });
        
        // Add first item automatically
        addItemBtn.click();
    });
</script>
@endpush

<style>
    .required:after {
        content: " *";
        color: red;
    }
    
    .item-select, .batch-no, .ordered-qty, .received-qty, .price {
        min-width: 100px;
    }
</style>
@endsection