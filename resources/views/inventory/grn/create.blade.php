@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Goods Received Note</h2>
        <a href="{{ route('inventory.grn.index') }}" 
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
                <div>
                    <h1 class="text-2xl font-bold">GOODS RECEIVED NOTE</h1>
                    <p class="mt-2">GRN No: <span class="font-semibold">Auto-generated</span></p>
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
        <form action="{{ route('inventory.grn.store') }}" method="POST" class="p-6">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
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
                                    <input type="text" name="items[0][code]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" name="items[0][description]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" step="0.01" name="items[0][unit_cost]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="items[0][quantity]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="items[0][free_quantity]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" step="0.01" name="items[0][discount]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
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
    // Add new item row
    let itemCount = 1;
    document.getElementById('add-item').addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="text" name="items[${itemCount}][code]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="text" name="items[${itemCount}][description]" class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" step="0.01" name="items[${itemCount}][unit_cost]" class="unit-cost w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" name="items[${itemCount}][quantity]" class="quantity w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" name="items[${itemCount}][free_quantity]" class="free-quantity w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" step="0.01" name="items[${itemCount}][discount]" class="discount w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="item-total">0.00</span>
            </td>
        `;
        document.getElementById('items-container').appendChild(newRow);
        itemCount++;
    });

    // Calculate totals when inputs change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('unit-cost') || 
            e.target.classList.contains('quantity') || 
            e.target.classList.contains('discount')) {
            calculateRowTotal(e.target.closest('tr'));
            calculateGrandTotal();
        }
    });

    // Calculate row total
    function calculateRowTotal(row) {
        const unitCost = parseFloat(row.querySelector('.unit-cost').value) || 0;
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const discount = parseFloat(row.querySelector('.discount').value) || 0;
        
        const subtotal = unitCost * quantity;
        const discountedAmount = subtotal * (discount / 100);
        const rowTotal = subtotal - discountedAmount;
        
        row.querySelector('.item-total').textContent = rowTotal.toFixed(2);
    }

    // Calculate grand total
    function calculateGrandTotal() {
        let subtotal = 0;
        let totalDiscount = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const unitCost = parseFloat(row.querySelector('.unit-cost').value) || 0;
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const discount = parseFloat(row.querySelector('.discount').value) || 0;
            
            const rowSubtotal = unitCost * quantity;
            const rowDiscount = rowSubtotal * (discount / 100);
            
            subtotal += rowSubtotal;
            totalDiscount += rowDiscount;
        });
        
        // Assuming tax rate of 15% - you can make this configurable
        const taxRate = 0.15;
        const tax = (subtotal - totalDiscount) * taxRate;
        const totalAmount = subtotal - totalDiscount + tax;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total-discount').textContent = totalDiscount.toFixed(2);
        document.getElementById('tax').textContent = tax.toFixed(2);
        document.getElementById('total-amount').textContent = totalAmount.toFixed(2);
        
        // Update balance due based on amount paid
        updateBalanceDue();
    }

    // Update balance due when amount paid changes
    document.querySelector('input[name="amount_paid"]').addEventListener('input', updateBalanceDue);
    
    function updateBalanceDue() {
        const totalAmount = parseFloat(document.getElementById('total-amount').textContent) || 0;
        const amountPaid = parseFloat(document.querySelector('input[name="amount_paid"]').value) || 0;
        const balanceDue = totalAmount - amountPaid;
        
        document.querySelector('input[name="balance_due"]').value = balanceDue.toFixed(2);
    }
});
</script>
@endsection