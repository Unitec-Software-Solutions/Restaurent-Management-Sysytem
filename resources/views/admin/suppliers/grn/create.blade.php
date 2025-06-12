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

                        </div>
                    </div>
                </div>

                <!-- Dates and Reference Numbers Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Received Date
                            *</label>
                        <div class="relative">
                            <input type="date" name="received_date" id="received_date"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                value="{{ old('received_date', date('Y-m-d')) }}" required style="appearance: none;">
                        </div>
                    </div>

                    <div>
                        <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note
                            No.</label>
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
                                    {{-- <th class="px-4 py-3">Batch No</th> --}}
                                    <th class="px-4 py-3">Received Qty *</th>
                                    <th class="px-4 py-3">Free Qty</th>
                                    <th class="px-4 py-3">Price *</th>
                                    <th class="px-4 py-3">Discount (%)</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3 w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                @php
                                    // Always preserve old input if validation fails or page is refreshed
                                    $oldItems = collect(
                                        old('items', [
                                            [
                                                'item_id' => '',
                                                // 'batch_no' => '',
                                                // 'ordered_quantity' => '',
                                                'received_quantity' => '',
                                                'buying_price' => '',
                                                'discount_received' => 0,
                                                'free_received_quantity' => 0,
                                            ],
                                        ]),
                                    )
                                        ->map(function ($item) use ($items) {
                                            // If item_id is set, fetch the latest buying_price from $items (ItemMaster) only if not already filled
                                            if (
                                                !empty($item['item_id']) &&
                                                (!isset($item['buying_price']) || $item['buying_price'] === '')
                                            ) {
                                                $itemMaster = collect($items)->firstWhere('id', $item['item_id']);
                                                if ($itemMaster) {
                                                    $item['buying_price'] = $itemMaster->buying_price;
                                                }
                                            }
                                            $item['discount_received'] = $item['discount_received'] ?? 0;
                                            $item['free_received_quantity'] = $item['free_received_quantity'] ?? 0;
                                            return $item;
                                        })
                                        ->toArray();
                                @endphp

                                @foreach ($oldItems as $index => $item)
                                    <tr class="item-row border-b bg-white hover:bg-gray-50"
                                        data-index="{{ $index }}">
                                        <td class="px-4 py-3">
                                            <select name="items[{{ $index }}][item_id]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select"
                                                required>
                                                <option value="">Select Item</option>
                                                @foreach ($items as $itemOption)
                                                    <option value="{{ $itemOption->id }}"
                                                        {{ $item['item_id'] == $itemOption->id ? 'selected' : '' }}
                                                        data-price="{{ $itemOption->buying_price }}">
                                                        {{ $itemOption->item_code }} - {{ $itemOption->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_code]"
                                                value="{{ $item['item_code'] ?? '' }}">
                                        </td>
                                        {{-- <td class="px-4 py-3">
                                    <input type="text" name="items[{{ $index }}][batch_no]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent batch-no"
                                        value="{{ $item['batch_no'] }}">
                                </td> --}}
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][received_quantity]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent received-qty"
                                                min="0.01" step="0.01" value="{{ $item['received_quantity'] }}"
                                                required>
                                            <input type="hidden" name="items[{{ $index }}][ordered_quantity]"
                                                class="ordered-qty" value="{{ $item['received_quantity'] }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][free_received_quantity]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent free-qty"
                                                min="0" step="0.01"
                                                value="{{ $item['free_received_quantity'] ?? 0 }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][buying_price]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-price"
                                                min="0" step="0.01" value="{{ $item['buying_price'] }}"
                                                required>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][discount_received]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent discount-received"
                                                min="0" max="100" step="0.01"
                                                value="{{ $item['discount_received'] ?? 0 }}">
                                        </td>
                                        <td class="px-4 py-3 font-medium item-total">
                                            @php
                                                $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                    ? (float) $item['received_quantity']
                                                    : 0;
                                                $price = is_numeric($item['buying_price'] ?? 0)
                                                    ? (float) $item['buying_price']
                                                    : 0;
                                                $discountPercent = is_numeric($item['discount_received'] ?? 0)
                                                    ? (float) $item['discount_received']
                                                    : 0;
                                                $discountAmount = $quantity * $price * ($discountPercent / 100);
                                                echo number_format($quantity * $price - $discountAmount, 2);
                                            @endphp
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($index === 0)
                                                <button type="button" class="text-gray-500 hover:text-gray-700">
                                                    <i class=" "></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="remove-item-btn text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold text-gray-900">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Total Items:</td>
                                    <td id="total-items" class="px-4 py-3 font-bold">
                                        {{ count($oldItems) }}
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Total Before Discount:</td>
                                    <td id="total-before-discount" class="px-4 py-3 font-bold">
                                        @php
                                            $totalBeforeDiscount = 0;
                                            foreach ($oldItems as $item) {
                                                $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                    ? (float) $item['received_quantity']
                                                    : 0;
                                                $price = is_numeric($item['buying_price'] ?? 0)
                                                    ? (float) $item['buying_price']
                                                    : 0;
                                                $totalBeforeDiscount += $quantity * $price;
                                            }
                                            echo number_format($totalBeforeDiscount, 2);
                                        @endphp
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Total Discount (Items):</td>
                                    <td id="total-discount-items" class="px-4 py-3 font-bold">
                                        @php
                                            $totalDiscountItems = 0;
                                            foreach ($oldItems as $item) {
                                                $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                    ? (float) $item['received_quantity']
                                                    : 0;
                                                $price = is_numeric($item['buying_price'] ?? 0)
                                                    ? (float) $item['buying_price']
                                                    : 0;
                                                $discountPercent = is_numeric($item['discount_received'] ?? 0)
                                                    ? (float) $item['discount_received']
                                                    : 0;
                                                $totalDiscountItems += $quantity * $price * ($discountPercent / 100);
                                            }
                                            echo number_format($totalDiscountItems, 2);
                                        @endphp
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">
                                        Grand Discount (Total Bill)
                                        <input type="number" name="grand_discount" id="grand-discount-input"
                                            class="ml-2 w-24 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500"
                                            min="0" max="100" step="0.01"
                                            value="{{ old('grand_discount', 0) }}" placeholder="%">
                                        <span class="text-xs text-gray-500 ml-1">%</span>
                                    </td>
                                    <td id="grand-discount-amount" class="px-4 py-3 font-bold">0.00</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Grand Total:</td>
                                    <td id="grand-total" class="px-4 py-3 font-bold">
                                        @php
                                            $grandTotal = $totalBeforeDiscount - $totalDiscountItems;
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
                @foreach ($items as $item)
                    "{{ $item->id }}": {
                        buying_price: {{ $item->buying_price }}
                    },
                @endforeach
            };

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
                        const event = new Event('input', {
                            bubbles: true
                        });
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
                    <input type="hidden" name="items[${itemCount}][item_code]" value="">
                </td>

                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][received_quantity]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent received-qty"
                           min="0.01" step="0.01" value="1" required>
                    <input type="hidden" name="items[${itemCount}][ordered_quantity]" class="ordered-qty" value="1">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][free_received_quantity]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent free-qty"
                           min="0" step="0.01" value="0">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][buying_price]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-price"
                           min="0" step="0.01" value="0.00" required>
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][discount_received]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent discount-received"
                           min="0" max="100" step="0.01" value="0">
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
                    const discountInput = newRow.querySelector('.discount-received');
                    const freeQtyInput = newRow.querySelector('.free-qty');
                    const removeBtn = newRow.querySelector('.remove-item-btn');

                    // Set price automatically when item is selected
                    select.addEventListener('change', function() {
                        handleItemChange.call(this);
                    });

                    // Set ordered quantity equal to received quantity
                    receivedQtyInput.addEventListener('input', function() {
                        orderedQtyInput.value = this.value;
                        calculateRowTotal.call(this);
                    });

                    // Prevent discount > 100%
                    discountInput.addEventListener('input', function() {
                        if (parseFloat(this.value) > 100) {
                            this.value = 100;
                            alert('Discount (%) cannot be more than 100%');
                        }
                        calculateRowTotal.call(this);
                    });

                    // Add calculation handlers
                    receivedQtyInput.addEventListener('input', calculateRowTotal);
                    priceInput.addEventListener('input', calculateRowTotal);
                    discountInput.addEventListener('input', calculateRowTotal);
                    freeQtyInput.addEventListener('input', calculateRowTotal);

                    // Add remove button handler
                    removeBtn.addEventListener('click', function() {
                        newRow.remove();
                        updateGrandTotal();
                    });

                    itemCount++;
                });

                function updateSummaryFooter() {
                    let totalItems = 0;
                    let totalBeforeDiscount = 0;
                    let totalDiscountItems = 0;

                    document.querySelectorAll('.item-row').forEach(row => {
                        totalItems++;
                        const quantity = parseFloat(row.querySelector('.received-qty').value) || 0;
                        const price = parseFloat(row.querySelector('.item-price').value) || 0;
                        const discountPercent = parseFloat(row.querySelector('.discount-received')?.value) || 0;
                        totalBeforeDiscount += quantity * price;
                        totalDiscountItems += (quantity * price) * (discountPercent / 100);
                    });

                    document.getElementById('total-items').textContent = totalItems;
                    document.getElementById('total-before-discount').textContent = totalBeforeDiscount.toFixed(2);
                    document.getElementById('total-discount-items').textContent = totalDiscountItems.toFixed(2);

                    // Grand discount
                    const grandDiscountPercent = parseFloat(document.getElementById('grand-discount-input').value) || 0;
                    const grandDiscountAmount = (totalBeforeDiscount - totalDiscountItems) * (grandDiscountPercent /
                        100);
                    document.getElementById('grand-discount-amount').textContent = grandDiscountAmount.toFixed(2);

                    // Grand total
                    const grandTotal = (totalBeforeDiscount - totalDiscountItems) - grandDiscountAmount;
                    document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
                }

                // Calculate row total using discount as percentage
                function calculateRowTotal() {
                    const row = this.closest('tr');
                    const quantity = parseFloat(row.querySelector('.received-qty').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const discountPercent = parseFloat(row.querySelector('.discount-received')?.value) || 0;
                    const discountAmount = (quantity * price) * (discountPercent / 100);
                    const total = (quantity * price) - discountAmount;
                    row.querySelector('.item-total').textContent = total.toFixed(2);
                    updateSummaryFooter();
                }

                // Update grand total
                function updateGrandTotal() {
                    updateSummaryFooter();
                }

                // Add event listeners to existing inputs
                document.querySelectorAll('.received-qty, .item-price, .discount-received, .free-qty').forEach(
                    input => {
                        if (input.classList.contains('discount-received')) {
                            input.addEventListener('input', function() {
                                if (parseFloat(this.value) > 100) {
                                    this.value = 100;
                                    alert('Discount (%) cannot be more than 100%');
                                }
                                calculateRowTotal.call(this);
                            });
                        } else {
                            input.addEventListener('input', calculateRowTotal);
                        }
                    });

                document.getElementById('grand-discount-input').addEventListener('input', updateSummaryFooter);

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

                    // Ensure ordered_quantity is set to received_quantity for all items before submit
                    itemRows.forEach(row => {
                        const receivedQty = row.querySelector('.received-qty');
                        const orderedQty = row.querySelector('.ordered-qty');
                        if (receivedQty && orderedQty) {
                            orderedQty.value = receivedQty.value;
                        }
                    });

                    let isValid = true;
                    itemRows.forEach(row => {
                        const itemSelect = row.querySelector('.item-select');
                        const receivedQty = row.querySelector('.received-qty');
                        const price = row.querySelector('.item-price');

                        // Only check visible/required fields
                        if (!itemSelect.value || !receivedQty.value || !price.value) {
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
                    const discountPercent = parseFloat(row.querySelector('.discount-received')?.value) || 0;
                    const discountAmount = (quantity * price) * (discountPercent / 100);
                    row.querySelector('.item-total').textContent = ((quantity * price) - discountAmount)
                        .toFixed(2);
                });
                updateSummaryFooter();
            });
        </script>
    @endpush
@endsection
