@extends('layouts.admin')

@section('header-title', 'Goods Received Note - Create New GRN')

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create New Goods Received Note</h2>
                    <p class="text-sm text-gray-500">Record items received from suppliers</p>
                </div>
                <a href="{{ route('admin.grn.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to GRNs
                </a>
            </div>

            <!-- Form Container -->
            <form id="grnForm" action="{{ route('admin.grn.store') }}" method="POST" class="p-6">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
                        <h3 class="font-medium mb-2">Validation Errors</h3>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Supplier and Branch Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <div class="relative">
                            <select name="supplier_id" id="supplier_id"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    required>
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} ({{ $supplier->supplier_id }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <div class="relative">
                            <select name="branch_id" id="branch_id"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                            {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates and Reference Numbers Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Received Date *</label>
                        <div class="relative">
                            <input type="date" name="received_date" id="received_date"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   value="{{ old('received_date', date('Y-m-d')) }}" required>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note No.</label>
                        <input type="text" name="delivery_note_number" id="delivery_note_number"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               value="{{ old('delivery_note_number') }}">
                    </div>

                    <div>
                        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-1">Invoice No.</label>
                        <input type="text" name="invoice_number" id="invoice_number"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               value="{{ old('invoice_number') }}">
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">GRN Items</h3>
                        <button type="button" id="addItemBtn"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
                    </div>

                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Item *</th>
                                    <th class="px-4 py-3">Batch No</th>
                                    <th class="px-4 py-3">Received Qty *</th>
                                    <th class="px-4 py-3">Ordered Qty *</th>
                                    <th class="px-4 py-3">Price *</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3 w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                @php
                                    $oldItems = old('items', [
                                        ['item_id' => '', 'batch_no' => '', 'ordered_quantity' => '', 'received_quantity' => '', 'buying_price' => ''],
                                    ]);
                                @endphp

                                @foreach ($oldItems as $index => $item)
                                    <tr class="item-row border-b bg-white hover:bg-gray-50" data-index="{{ $index }}">
                                        <td class="px-4 py-3">
                                            <select name="items[{{ $index }}][item_id]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select"
                                                    required>
                                                <option value="">Select Item</option>
                                                @foreach ($items as $itemOption)
                                                    <option value="{{ $itemOption->id }}"
                                                            {{ $item['item_id'] == $itemOption->id ? 'selected' : '' }}
                                                            data-price="{{ $itemOption->buying_price }}">
                                                        {{ $itemOption->name }} ({{ $itemOption->item_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[{{ $index }}][batch_no]"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent batch-no"
                                                   value="{{ $item['batch_no'] }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][received_quantity]"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent received-qty"
                                                   min="0.01" step="0.01" value="{{ $item['received_quantity'] }}" required>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][ordered_quantity]"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent ordered-qty"
                                                   min="0.01" step="0.01" value="{{ $item['ordered_quantity'] }}" required>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][buying_price]"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-price"
                                                   min="0" step="0.01" value="{{ $item['buying_price'] }}" required>
                                        </td>
                                        <td class="px-4 py-3 font-medium item-total">
                                            @php
                                                $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                    ? (float) $item['received_quantity']
                                                    : 0;
                                                $price = is_numeric($item['buying_price'] ?? 0)
                                                    ? (float) $item['buying_price']
                                                    : 0;
                                                echo number_format($quantity * $price, 2);
                                            @endphp
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($index === 0)
                                                <button type="button" class="text-gray-500 hover:text-gray-700">
                                                    <i class=" "></i>
                                                </button>
                                            @else
                                                <button type="button" class="remove-item-btn text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold text-gray-900">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-right">Grand Total:</td>
                                    <td id="grand-total" class="px-4 py-3 font-bold">
                                        @php
                                            $grandTotal = 0;
                                            foreach ($oldItems as $item) {
                                                $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                    ? (float) $item['received_quantity']
                                                    : 0;
                                                $price = is_numeric($item['buying_price'] ?? 0)
                                                    ? (float) $item['buying_price']
                                                    : 0;
                                                $grandTotal += $quantity * $price;
                                            }
                                            echo number_format($grandTotal, 2);
                                        @endphp
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-8">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              rows="3" maxlength="500" placeholder="Add any special instructions or notes for this GRN...">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                    <button type="reset"
                            class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset Form
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i> Create GRN
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
    // Preload items data
    const itemsData = {
        @foreach($items as $item)
            "{{ $item->id }}": {
                buying_price: {{ $item->buying_price }}
            },
        @endforeach
    };
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');
        const grandTotalEl = document.getElementById('grand-total');
        let itemCount = {{ count($oldItems) }};

        // Function to handle item selection change
        function handleItemChange() {
            const row = this.closest('tr');
            const itemId = this.value;
            const priceInput = row.querySelector('.item-price');
            
            if (itemId && itemsData[itemId]) {
                priceInput.value = itemsData[itemId].buying_price;
                const event = new Event('input', { bubbles: true });
                priceInput.dispatchEvent(event);
            }
        }

        // Attach event to existing item selects
        document.querySelectorAll('.item-select').forEach(select => {
            select.addEventListener('change', handleItemChange);
        });

        // Add new item row
        addItemBtn.addEventListener('click', function() {
            const newRow = document.createElement('tr');
            newRow.className = 'item-row border-b bg-white hover:bg-gray-50';
            newRow.dataset.index = itemCount;
            newRow.innerHTML = `
                <td class="px-4 py-3">
                    <select name="items[${itemCount}][item_id]" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" 
                            required>
                        <option value="">Select Item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->buying_price }}">
                                {{ $item->name }} ({{ $item->item_code }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-3">
                    <input type="text" name="items[${itemCount}][batch_no]" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent batch-no">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][received_quantity]" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent received-qty" 
                           min="0.01" step="0.01" value="1" required>
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][ordered_quantity]" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent ordered-qty" 
                           min="0.01" step="0.01" value="1" required>
                </td>

                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][buying_price]" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-price" 
                           min="0" step="0.01" value="0.00" required>
                </td>
                <td class="px-4 py-3 font-medium item-total">0.00</td>
                <td class="px-4 py-3 text-center">
                    <button type="button" class="remove-item-btn text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            itemsContainer.appendChild(newRow);
            
            // Add event listeners to the new row
            const select = newRow.querySelector('.item-select');
            const receivedQtyInput = newRow.querySelector('.received-qty');
            const orderedQtyInput = newRow.querySelector('.ordered-qty');
            const priceInput = newRow.querySelector('.item-price');
            const removeBtn = newRow.querySelector('.remove-item-btn');

            // Add item change handler
            select.addEventListener('change', handleItemChange);
            
            // Set ordered quantity equal to received quantity
            receivedQtyInput.addEventListener('input', function() {
                orderedQtyInput.value = this.value;
                calculateRowTotal.call(this);
            });
            
            // Add calculation handlers
            receivedQtyInput.addEventListener('input', calculateRowTotal);
            priceInput.addEventListener('input', calculateRowTotal);
            
            // Add remove button handler
            removeBtn.addEventListener('click', function() {
                newRow.remove();
                updateGrandTotal();
            });

            itemCount++;
        });

        // Calculate row total
        function calculateRowTotal() {
            const row = this.closest('tr');
            const quantity = parseFloat(row.querySelector('.received-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = quantity * price;

            row.querySelector('.item-total').textContent = total.toFixed(2);
            updateGrandTotal();
        }

        // Update grand total
        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const totalText = row.querySelector('.item-total').textContent;
                const totalValue = parseFloat(totalText) || 0;
                grandTotal += totalValue;
            });

            grandTotalEl.textContent = grandTotal.toFixed(2);
        }

        // Add event listeners to existing inputs
        document.querySelectorAll('.received-qty, .item-price').forEach(input => {
            input.addEventListener('input', calculateRowTotal);
        });

        document.querySelectorAll('.received-qty').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                row.querySelector('.ordered-qty').value = this.value;
            });
        });

        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('tr').remove();
                updateGrandTotal();
            });
        });

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
                const orderedQty = row.querySelector('.ordered-qty');
                const price = row.querySelector('.item-price');

                if (!itemSelect.value || !receivedQty.value || !orderedQty.value || !price.value) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields for all items');
                return false;
            }
        });

        // Initialize calculations on page load
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.received-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            row.querySelector('.item-total').textContent = (quantity * price).toFixed(2);
        });
        updateGrandTotal();
    });
</script>
@endpush
@endsection