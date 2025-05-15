@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Goods Received Note</h2>
        <a href="{{ route('admin.inventory.grn.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Back to GRN List</a>
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

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <!-- GRN Header -->
        <div class="bg-blue-600 dark:bg-blue-800 text-white p-6">
            <div class="flex justify-between items-start">
                <!-- GRN number section -->
                <div>
                    <h1 class="text-2xl font-bold">GOODS RECEIVED NOTE</h1>
                    <p class="mt-2">GRN No: <span class="font-semibold">{{ $grnNumber }}</span></p>
                    <input type="hidden" name="grn_number" value="{{ $grnNumber }}">
                </div>

                <!-- Add this after supplier selection -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Purchase Order*</label>
                    <select name="purchase_order_id" id="purchase-order" required class="w-full rounded-md">
                        <option value="">Select Purchase Order</option>
                        @foreach($pendingPOs as $po)
                            <option value="{{ $po->id }}" data-items='@json($po->items)'>
                                {{ $po->po_number }} - {{ $po->supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="text-right">
                    <p class="text-lg">Date: <span class="font-semibold">{{ date('d M, Y') }}</span></p>
                    <p class="mt-2">Branch: 
                        <span class="font-semibold">
                            {{ Auth::user()->branch->name ?? 'Head Office' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- GRN Form -->
        <form action="{{ route('admin.inventory.grn.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Supplier Information -->
            <div class="mb-8 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Supplier Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier*</label>
                        <select name="supplier_id" required 
                                class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier Invoice No*</label>
                        <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Date*</label>
                        <input type="date" name="invoice_date" value="{{ old('invoice_date') }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delivery Note No</label>
                        <input type="text" name="delivery_note" value="{{ old('delivery_note') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                </div>
            </div>

            <!-- GRN Items Table -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Items Received</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Free Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Discount %</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="items-container">
                            <!-- Dynamic rows will be added here -->
                            <tr class="item-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="items[0][inventory_item_id]" class="item-select w-full rounded-md" required>
                                        <option value="">Select Item</option>
                                    </select>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" step="0.01" name="items[0][unit_price]" class="unit-cost w-full rounded-md" required>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="items[0][quantity]" class="quantity w-full rounded-md" required>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="items[0][free_quantity]" class="free-quantity w-full rounded-md" value="0">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" step="0.01" name="items[0][discount_percentage]" class="discount w-full rounded-md" value="0">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="item-total">0.00</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button type="button" id="add-item" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Item</button>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="mb-8 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">GRN Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GRN Description</label>
                        <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ old('notes') }}</textarea>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="font-medium">Subtotal:</span>
                            <span id="subtotal">0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Discount:</span>
                            <span id="total-discount">0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Tax:</span>
                            <span id="tax">0.00</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-bold">Total Amount:</span>
                            <span id="total-amount" class="font-bold">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="mb-8 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Payment Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount Paid</label>
                        <input type="number" step="0.01" name="amount_paid" value="{{ old('amount_paid', 0) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Balance Due</label>
                        <input type="number" step="0.01" name="balance_due" readonly
                               class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <button type="reset" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 dark:text-white bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Reset
                </button>
                <button type="submit" name="action" value="save" class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Save Draft
                </button>
                <button type="submit" name="action" value="save_and_complete" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Complete GRN
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poSelect = document.getElementById('purchase-order');
    const taxRate = {{ config('app.tax_rate', 0) }}; // Get from config or system settings
    
    poSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const items = JSON.parse(selectedOption.dataset.items);
            populateItemDropdowns(items);
        }
    });

    function populateItemDropdowns(items) {
        const itemSelects = document.querySelectorAll('.item-select');
        itemSelects.forEach(select => {
            select.innerHTML = '<option value="">Select Item</option>';
            items.forEach(item => {
                const option = new Option(
                    `${item.inventory_item.sku} - ${item.inventory_item.name}`,
                    item.inventory_item_id
                );
                option.dataset.item = JSON.stringify(item);
                select.add(option);
            });
        });
    }

    function calculateRowTotal(row) {
        const unitCost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const freeQty = parseFloat(row.querySelector('.free-quantity').value) || 0;
        const discount = parseFloat(row.querySelector('.discount').value) || 0;
        
        const subtotal = unitCost * quantity;
        const discountAmount = subtotal * (discount / 100);
        const rowTotal = subtotal - discountAmount;
        
        row.querySelector('.item-total').textContent = rowTotal.toFixed(2);
        return {
            subtotal,
            discountAmount,
            rowTotal
        };
    }

    function calculateGrandTotal() {
        let subtotal = 0;
        let totalDiscount = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const totals = calculateRowTotal(row);
            subtotal += totals.subtotal;
            totalDiscount += totals.discountAmount;
        });
        
        const taxableAmount = subtotal - totalDiscount;
        const tax = taxableAmount * taxRate;
        const grandTotal = taxableAmount + tax;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total-discount').textContent = totalDiscount.toFixed(2);
        document.getElementById('tax').textContent = tax.toFixed(2);
        document.getElementById('total-amount').textContent = grandTotal.toFixed(2);
        
        // Update hidden fields for form submission
        document.getElementById('input-subtotal').value = subtotal;
        document.getElementById('input-discount').value = totalDiscount;
        document.getElementById('input-tax').value = tax;
        document.getElementById('input-total').value = grandTotal;
        
        updateBalanceDue();
    }

    // Add event listeners for calculation triggers
    document.addEventListener('input', function(e) {
        if (e.target.matches('.unit-cost, .quantity, .free-quantity, .discount')) {
            calculateGrandTotal();
        }
    });

    // Add item selection handler
    document.addEventListener('change', function(e) {
        if (e.target.matches('.item-select')) {
            const row = e.target.closest('tr');
            const option = e.target.options[e.target.selectedIndex];
            if (option.dataset.item) {
                const item = JSON.parse(option.dataset.item);
                row.querySelector('.item-description').textContent = item.inventory_item.name;
                row.querySelector('.unit-cost').value = item.unit_price;
                calculateGrandTotal();
            }
        }
    });
});
</script>
@endsection