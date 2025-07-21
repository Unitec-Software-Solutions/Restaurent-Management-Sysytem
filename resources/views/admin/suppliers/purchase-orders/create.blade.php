@extends('layouts.admin')
@section('header-title', 'Create Purchase Order')
@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create New Purchase Order</h2>
                    <p class="text-sm text-gray-500"> ~ sub ~ </p>
                </div>

                <a href="{{ route('admin.purchase-orders.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                </a>
            </div>

            <!-- Form Container -->
            <form id="purchase-order-form" action="{{ route('admin.purchase-orders.store') }}" method="POST" class="p-6">
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
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
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
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">Order Date *</label>
                        <div class="relative">
                            <input type="date" name="order_date" id="order_date"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                value="{{ old('order_date', date('Y-m-d')) }}" required>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected
                            Delivery Date *</label>
                        <div class="relative">
                            <input type="date" name="expected_delivery_date" id="expected_delivery_date"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Items</h3>
                        <button type="button" id="add-item-btn"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
                    </div>

                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Item *</th>
                                    <th class="px-4 py-3">Quantity *</th>
                                    <th class="px-4 py-3">Buying Price *</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3 w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                @php
                                    $oldItems = old('items', [
                                        ['item_id' => '', 'quantity' => '', 'buying_price' => ''],
                                    ]);
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
                                                        {{ $item['item_id'] == $itemOption->id ? 'selected' : '' }}>
                                                        {{ $itemOption->item_code }} - {{ $itemOption->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][quantity]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-quantity"
                                                min="0.01" step="0.01" value="{{ $item['quantity'] }}" required>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="items[{{ $index }}][buying_price]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-price"
                                                min="0" step="0.01" value="{{ $item['buying_price'] }}" required>
                                        </td>
                                        <td class="px-4 py-3 font-medium item-total">
                                            @php
                                                $quantity = is_numeric($item['quantity'] ?? 0)
                                                    ? (float) $item['quantity']
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
                                    <td colspan="3" class="px-4 py-3 text-right">Grand Total:</td>
                                    <td id="grand-total" class="px-4 py-3 font-bold">
                                        @php
                                            $grandTotal = 0;
                                            foreach ($oldItems as $item) {
                                                $quantity = is_numeric($item['quantity'] ?? 0)
                                                    ? (float) $item['quantity']
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
                        rows="3" maxlength="500" placeholder="Add any special instructions or notes for this order...">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                    <button type="reset"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset Form
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i> Create Purchase Order
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
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const itemsContainer = document.getElementById('items-container');
                const addItemBtn = document.getElementById('add-item-btn');
                const grandTotalEl = document.getElementById('grand-total');
                let itemCount = {{ count($oldItems) }};

                // Function to handle item selection change
                function handleItemChange() {
                    const row = this.closest('tr');
                    const itemId = this.value;
                    const priceInput = row.querySelector('.item-price');
                    if (itemId && itemsData[itemId]) {
                        priceInput.value = itemsData[itemId].buying_price;
                        // Trigger the input event to recalculate the row total
                        priceInput.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
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
                    <select name="items[${itemCount}][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" required>
                        <option value="">Select Item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->item_code }} - {{ $item->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][quantity]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-quantity"
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
                    const quantityInput = newRow.querySelector('.item-quantity');
                    const priceInput = newRow.querySelector('.item-price');
                    const removeBtn = newRow.querySelector('.remove-item-btn');

                    select.addEventListener('change', handleItemChange);
                    quantityInput.addEventListener('input', calculateRowTotal);
                    priceInput.addEventListener('input', calculateRowTotal);
                    removeBtn.addEventListener('click', function() {
                        newRow.remove();
                        updateGrandTotal();
                    });

                    itemCount++;
                });

                // Calculate row total
                function calculateRowTotal() {
                    const row = this.closest('tr');
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    row.querySelector('.item-total').textContent = (quantity * price).toFixed(2);
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
                document.querySelectorAll('.item-quantity, .item-price').forEach(input => {
                    input.addEventListener('input', calculateRowTotal);
                });

                // Add event listeners to remove buttons
                document.querySelectorAll('.remove-item-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        this.closest('tr').remove();
                        updateGrandTotal();
                    });
                });

                // Set min date for expected delivery
                document.getElementById('order_date').addEventListener('change', function() {
                    const expectedDate = document.getElementById('expected_delivery_date');
                    expectedDate.min = this.value;
                    if (new Date(expectedDate.value) < new Date(this.value)) {
                        expectedDate.value = this.value;
                    }
                });

                // Initialize calculations on page load
                document.querySelectorAll('.item-row').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    row.querySelector('.item-total').textContent = (quantity * price).toFixed(2);
                });
                updateGrandTotal();
            });
        </script>
    @endpush
@endsection
