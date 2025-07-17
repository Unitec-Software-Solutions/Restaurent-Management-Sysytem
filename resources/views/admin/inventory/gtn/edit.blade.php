@extends('layouts.admin')

@section('header-title', 'Edit Goods Transfer Note' . $gtn->gtn_number)
@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Edit GTN: {{ $gtn->gtn_number }}</h2>
                    <p class="text-sm text-gray-500">Update goods transfer details</p>
                    @if (!$gtn->isDraft())
                        <p class="text-sm text-red-500 mt-1">⚠️ This GTN can only be viewed as it's no longer in draft status
                        </p>
                    @endif
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.inventory.gtn.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
                    </a>
                    <a href="{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-eye mr-2"></i> View GTN
                    </a>
                </div>
            </div>

            <!-- Form Container -->
            <form action="{{ route('admin.inventory.gtn.update', $gtn->gtn_id) }}" method="POST" class="p-6"
                id="gtnEditForm">
                @csrf
                @method('PUT')

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

                <!-- GTN Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GTN Number</label>
                        <input type="text" value="{{ $gtn->gtn_number }}" disabled
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label for="from_branch_id" class="block text-sm font-medium text-gray-700 mb-1">Origin
                            Branch</label>
                        <div class="relative">
                            <input type="text" value="{{ $gtn->fromBranch->name ?? 'N/A' }}" disabled
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100">
                            <!-- Hidden field to maintain the value for form submission -->
                            <input type="hidden" name="from_branch_id" value="{{ $gtn->from_branch_id }}">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Origin branch cannot be changed</p>
                    </div>

                    <div>
                        <label for="to_branch_id" class="block text-sm font-medium text-gray-700 mb-1">Destination Branch
                            *</label>
                        <div class="relative">
                            <select id="to_branch_id" name="to_branch_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                {{ !$gtn->isDraft() ? 'disabled' : 'required' }}>
                                <option value="">Select Destination Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ $gtn->to_branch_id == $branch->id ? 'selected' : '' }}
                                        {{ $gtn->from_branch_id == $branch->id ? 'disabled style=display:none' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Transfer Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1">Transfer Date
                            *</label>
                        <div class="relative">
                            <input type="date" id="transfer_date" name="transfer_date"
                                value="{{ $gtn->transfer_date->format('Y-m-d') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                {{ !$gtn->isDraft() ? 'disabled' : 'required' }}>
                        </div>
                    </div>

                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference
                            Number</label>
                        <input type="text" id="reference_number" name="reference_number"
                            value="{{ $gtn->reference_number }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            {{ !$gtn->isDraft() ? 'disabled' : '' }}>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Transfer Items</h3>
                        @if ($gtn->isDraft())
                            <button type="button" id="addItemBtn"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Item
                            </button>
                        @endif
                    </div>

                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3">Item</th>
                                        @if ($gtn->isDraft())
                                            <th class="px-4 py-3">Available Stock</th>
                                        @endif
                                        <th class="px-4 py-3">Transfer Quantity</th>
                                        <th class="px-4 py-3">Item Value</th>
                                        <th class="px-4 py-3">Line Total</th>
                                        <th class="px-4 py-3">Expiry Date</th>
                                        @if ($gtn->isDraft())
                                            <th class="px-4 py-3">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    @foreach ($gtn->items as $index => $item)
                                        <tr class="item-row border-b bg-white">
                                            <td class="px-4 py-3">
                                                @if ($gtn->isDraft())
                                                    <select name="items[{{ $index }}][item_id]"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select"
                                                        required>
                                                        <option value="">Select Item</option>
                                                        @foreach ($items as $itemOption)
                                                            <option value="{{ $itemOption->id }}"
                                                                data-code="{{ $itemOption->item_code }}"
                                                                data-price="{{ $itemOption->buying_price }}" data-stock="0"
                                                                data-max="0"
                                                                {{ $item->item_id == $itemOption->id ? 'selected' : '' }}>
                                                                {{ $itemOption->name }} ({{ $itemOption->item_code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <!-- Hidden transfer_price field, auto-populated -->
                                                    <input type="hidden" name="items[{{ $index }}][transfer_price]"
                                                        value="{{ $item->transfer_price }}" class="transfer-price-input">
                                                @else
                                                    <div class="font-medium">{{ $item->item_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                                @endif
                                            </td>
                                            @if ($gtn->isDraft())
                                                <td class="px-4 py-3">
                                                    <div class="text-sm font-medium stock-display text-gray-600">Loading...
                                                    </div>
                                                </td>
                                            @endif
                                            <td class="px-4 py-3">
                                                @if ($gtn->isDraft())
                                                    <input type="number" step="0.01"
                                                        name="items[{{ $index }}][transfer_quantity]"
                                                        value="{{ $item->transfer_quantity }}"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg quantity"
                                                        required>
                                                    <div class="text-xs text-gray-500 mt-1 stock-hint"></div>
                                                @else
                                                    <div class="font-medium">{{ $item->transfer_quantity }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-700">
                                                    Rs. {{ number_format($item->transfer_price, 2) }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium line-total">
                                                    Rs.
                                                    {{ number_format($item->transfer_quantity * $item->transfer_price, 2) }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($gtn->isDraft())
                                                    <input type="date" name="items[{{ $index }}][expiry_date]"
                                                        value="{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                @else
                                                    <div class="text-sm">
                                                        {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '-' }}
                                                    </div>
                                                @endif
                                            </td>
                                            @if ($gtn->isDraft())
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button"
                                                        class="remove-item text-red-500 hover:text-red-700">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="{{ $gtn->isDraft() ? '4' : '3' }}"
                                            class="px-4 py-3 text-right font-semibold">Grand Total:</td>
                                        <td class="px-4 py-3 font-bold text-lg text-green-600" id="grand-total">
                                            Rs. {{ number_format($gtn->getTotalTransferValue(), 2) }}
                                        </td>
                                        <td colspan="{{ $gtn->isDraft() ? '2' : '1' }}"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        {{ !$gtn->isDraft() ? 'disabled' : '' }}>{{ $gtn->notes }}</textarea>
                </div>

                <!-- Form Actions -->
                @if ($gtn->isDraft())
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                        <button type="reset"
                            class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                            <i class="fas fa-redo mr-2"></i> Reset Form
                        </button>
                        <button type="submit"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i> Update GTN
                        </button>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                            <span class="text-sm text-yellow-800">This GTN is no longer editable as it has been confirmed
                                or is in progress.</span>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($gtn->isDraft())
                let itemCounter = {{ count($gtn->items) }};
                let availableItems = [];
                let selectedBranchId = {{ $gtn->from_branch_id }}; // Fixed origin branch

                const toBranchSelect = document.getElementById('to_branch_id');
                const addItemBtn = document.getElementById('addItemBtn');
                const itemsContainer = document.getElementById('itemsContainer');

                // Function to calculate cumulative quantities and remaining stock for all items
                function updateAllRemainingStock() {
                    const itemQuantities = new Map(); // item_id -> total quantity used
                    const itemStocks = new Map(); // item_id -> available stock

                    // First pass: collect all item selections and their available stock
                    document.querySelectorAll('.item-row').forEach(row => {
                        const selectInput = row.querySelector('.item-select');
                        if (selectInput && selectInput.value) {
                            const selectedOption = selectInput.selectedOptions[0];
                            const itemId = selectInput.value;
                            const availableStock = parseFloat(selectedOption.dataset.stock) || 0;

                            if (!itemStocks.has(itemId)) {
                                itemStocks.set(itemId, availableStock);
                                itemQuantities.set(itemId, 0);
                            }
                        }
                    });

                    // Second pass: calculate cumulative quantities
                    document.querySelectorAll('.item-row').forEach(row => {
                        const selectInput = row.querySelector('.item-select');
                        const qtyInput = row.querySelector('.quantity');

                        if (selectInput && selectInput.value && qtyInput && qtyInput.value) {
                            const itemId = selectInput.value;
                            const quantity = parseFloat(qtyInput.value) || 0;

                            if (itemQuantities.has(itemId)) {
                                itemQuantities.set(itemId, itemQuantities.get(itemId) + quantity);
                            }
                        }
                    });

                    // Third pass: update remaining stock displays and validation
                    document.querySelectorAll('.item-row').forEach(row => {
                        const selectInput = row.querySelector('.item-select');
                        const qtyInput = row.querySelector('.quantity');
                        const stockHint = row.querySelector('.stock-hint');

                        if (selectInput && selectInput.value && stockHint) {
                            const itemId = selectInput.value;
                            const currentQty = parseFloat(qtyInput.value) || 0;
                            const totalQtyUsed = itemQuantities.get(itemId) || 0;
                            const availableStock = itemStocks.get(itemId) || 0;
                            const remainingStock = availableStock - totalQtyUsed;

                            // Clear previous error states
                            qtyInput.classList.remove('border-red-500');

                            if (totalQtyUsed > availableStock) {
                                // Error state
                                qtyInput.classList.add('border-red-500');
                                stockHint.textContent =
                                    `Error: Total quantity for this item (${totalQtyUsed.toFixed(2)}) exceeds available stock (${availableStock.toFixed(2)})`;
                                stockHint.className = 'text-xs text-red-500 mt-1 stock-hint';
                            } else {
                                // Normal state - show remaining stock after this transfer
                                stockHint.textContent =
                                    `Remaining stock after transfer: ${remainingStock.toFixed(2)}`;
                                stockHint.className = 'text-xs text-gray-500 mt-1 stock-hint';
                            }
                        }
                    });
                }

                // Enhanced quantity validation with real-time remaining stock updates
                function validateQuantity(qtyInput) {
                    const max = parseFloat(qtyInput.max);
                    const value = parseFloat(qtyInput.value);

                    // Individual quantity validation
                    if (value > max) {
                        qtyInput.setCustomValidity(`Quantity cannot exceed ${max.toFixed(2)}`);
                    } else {
                        qtyInput.setCustomValidity('');
                    }

                    // Update all remaining stock displays
                    updateAllRemainingStock();
                }

                // Function to validate cumulative quantities (for form submission)
                function validateCumulativeQuantities() {
                    const itemQuantities = new Map();
                    const itemStocks = new Map();
                    let hasErrors = false;

                    // Calculate cumulative quantities
                    document.querySelectorAll('.item-row').forEach(row => {
                        const selectInput = row.querySelector('.item-select');
                        const qtyInput = row.querySelector('.quantity');

                        if (selectInput && selectInput.value && qtyInput && qtyInput.value) {
                            const itemId = selectInput.value;
                            const quantity = parseFloat(qtyInput.value) || 0;
                            const selectedOption = selectInput.selectedOptions[0];
                            const availableStock = parseFloat(selectedOption.dataset.stock) || 0;

                            itemStocks.set(itemId, availableStock);

                            if (itemQuantities.has(itemId)) {
                                itemQuantities.set(itemId, itemQuantities.get(itemId) + quantity);
                            } else {
                                itemQuantities.set(itemId, quantity);
                            }
                        }
                    });

                    // Check for violations
                    itemQuantities.forEach((totalQty, itemId) => {
                        const availableStock = itemStocks.get(itemId) || 0;
                        if (totalQty > availableStock) {
                            hasErrors = true;
                        }
                    });

                    return !hasErrors;
                }

                // Load stock for existing items with fixed origin branch
                if (selectedBranchId) {
                    fetchItemsWithStock(selectedBranchId);
                }

                // Handle destination branch selection only
                toBranchSelect.addEventListener('change', function() {
                    if (this.value === selectedBranchId.toString()) {
                        alert('Destination branch cannot be the same as origin branch');
                        this.value = '';
                    }
                });

                function fetchItemsWithStock(branchId) {
                    fetch(`{{ route('admin.inventory.gtn.items-with-stock') }}?branch_id=${branchId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                throw new Error(data.error);
                            }

                            availableItems = data;

                            // Update existing item selects with stock data
                            document.querySelectorAll('.item-select').forEach(select => {
                                Array.from(select.options).forEach(option => {
                                    if (option.value) {
                                        const item = availableItems.find(item => item.id ==
                                            option.value);
                                        if (item) {
                                            option.dataset.stock = item.stock_on_hand;
                                            option.dataset.max = item.max_transfer;
                                            option.dataset.price = item.buying_price;
                                        }
                                    }
                                });

                                // Update stock display for selected item
                                if (select.value) {
                                    updateStockDisplay(select);
                                }
                            });

                            addItemBtn.disabled = availableItems.length === 0;
                            if (availableItems.length === 0) {
                                addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            } else {
                                addItemBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }

                // Add new item row
                addItemBtn.addEventListener('click', function() {
                    if (!selectedBranchId || availableItems.length === 0) {
                        alert('No available stock for transfer from this branch');
                        return;
                    }

                    const newRow = document.createElement('tr');
                    newRow.className = 'item-row border-b bg-white';
                    newRow.innerHTML = `
                        <td class="px-4 py-3">
                            <select name="items[${itemCounter}][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" required>
                                <option value="">Select Item</option>
                                ${availableItems.map(item =>
                                    `<option value="${item.id}"
                                                                    data-code="${item.item_code}"
                                                                    data-stock="${item.stock_on_hand}"
                                                                    data-price="${item.buying_price}"
                                                                    data-max="${item.max_transfer}">
                                                                    ${item.name} (${item.item_code})
                                                                </option>`
                                ).join('')}
                            </select>
                            <input type="hidden" name="items[${itemCounter}][transfer_price]" class="transfer-price-input" value="0">
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium stock-display text-gray-600">-</div>
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" name="items[${itemCounter}][transfer_quantity]" value=""
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg quantity" required
                                min="0.01" max="" placeholder="0.00">
                            <div class="text-xs text-gray-500 mt-1 stock-hint"></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-700 transfer-price-display">Rs. 0.00</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium line-total">Rs. 0.00</div>
                        </td>
                        <td class="px-4 py-3">
                            <input type="date" name="items[${itemCounter}][expiry_date]"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" class="remove-item text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;

                    itemsContainer.appendChild(newRow);

                    // Add event listeners to the new row
                    const select = newRow.querySelector('.item-select');
                    const qtyInput = newRow.querySelector('.quantity');
                    const removeBtn = newRow.querySelector('.remove-item');

                    select.addEventListener('change', function() {
                        updateStockDisplay(this);
                    });

                    qtyInput.addEventListener('input', function() {
                        validateQuantity(this);
                        calculateLineTotal(this.closest('.item-row'));
                        updateGrandTotal();
                    });

                    removeBtn.addEventListener('click', function() {
                        newRow.remove();
                        updateGrandTotal();
                    });

                    itemCounter++;
                });

                // Add cumulative stock validation
                function validateCumulativeQuantities() {
                    const itemQuantities = new Map(); // item_id -> total quantity
                    const itemStocks = new Map(); // item_id -> available stock
                    let hasErrors = false;

                    // Reset all error states first
                    document.querySelectorAll('.quantity').forEach(input => {
                        input.classList.remove('border-red-500');
                        const hint = input.closest('tr').querySelector('.stock-hint');
                        if (hint && hint.classList.contains('text-red-500')) {
                            hint.classList.remove('text-red-500');
                            hint.classList.add('text-gray-500');
                        }
                    });

                    // Calculate cumulative quantities for each item
                    document.querySelectorAll('.item-row').forEach(row => {
                        const selectInput = row.querySelector('.item-select');
                        const qtyInput = row.querySelector('.quantity');

                        if (selectInput && selectInput.value && qtyInput && qtyInput.value) {
                            const itemId = selectInput.value;
                            const quantity = parseFloat(qtyInput.value) || 0;
                            const selectedOption = selectInput.selectedOptions[0];
                            const availableStock = parseFloat(selectedOption.dataset.stock) || 0;

                            // Store available stock for this item
                            itemStocks.set(itemId, availableStock);

                            // Add to cumulative quantity
                            if (itemQuantities.has(itemId)) {
                                itemQuantities.set(itemId, itemQuantities.get(itemId) + quantity);
                            } else {
                                itemQuantities.set(itemId, quantity);
                            }
                        }
                    });

                    // Check for violations and mark errors
                    itemQuantities.forEach((totalQty, itemId) => {
                        const availableStock = itemStocks.get(itemId) || 0;

                        if (totalQty > availableStock) {
                            hasErrors = true;

                            // Mark all rows with this item as having errors
                            document.querySelectorAll('.item-row').forEach(row => {
                                const selectInput = row.querySelector('.item-select');
                                const qtyInput = row.querySelector('.quantity');
                                const hint = row.querySelector('.stock-hint');

                                if (selectInput && selectInput.value === itemId) {
                                    qtyInput.classList.add('border-red-500');
                                    if (hint) {
                                        hint.textContent =
                                            `Error: Total quantity for this item (${totalQty.toFixed(2)}) exceeds available stock (${availableStock.toFixed(2)})`;
                                        hint.className = 'text-xs text-red-500 mt-1 stock-hint';
                                    }
                                }
                            });
                        }
                    });

                    return !hasErrors;
                }

                function validateQuantity(qtyInput) {
                    const max = parseFloat(qtyInput.max);
                    const value = parseFloat(qtyInput.value);

                    // Reset error state
                    qtyInput.classList.remove('border-red-500');

                    if (value > max) {
                        qtyInput.setCustomValidity(`Quantity cannot exceed ${max.toFixed(2)}`);
                        qtyInput.classList.add('border-red-500');
                        const row = qtyInput.closest('.item-row');
                        const stockHint = row.querySelector('.stock-hint');
                        if (stockHint) {
                            stockHint.textContent = `Error: Maximum allowed is ${max.toFixed(2)}`;
                            stockHint.className = 'text-xs text-red-500 mt-1 stock-hint';
                        }
                    } else {
                        qtyInput.setCustomValidity('');
                        const selectedOption = qtyInput.closest('.item-row').querySelector('.item-select')
                            .selectedOptions[0];
                        if (selectedOption) {
                            const stock = parseFloat(selectedOption.dataset.stock);
                            const remainingStock = stock - value;
                            const row = qtyInput.closest('.item-row');
                            const stockHint = row.querySelector('.stock-hint');
                            stockHint.textContent = `Remaining stock after transfer: ${remainingStock.toFixed(2)}`;
                            stockHint.className = 'text-xs text-gray-500 mt-1 stock-hint';
                        }
                    }

                    // Always run cumulative validation after individual validation
                    setTimeout(() => validateCumulativeQuantities(), 100);
                }

                function updateStockDisplay(selectElement) {
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const row = selectElement.closest('.item-row');
                    const stockDisplay = row.querySelector('.stock-display');
                    const qtyInput = row.querySelector('.quantity');
                    const stockHint = row.querySelector('.stock-hint');
                    const transferPriceInput = row.querySelector('.transfer-price-input');

                    if (selectedOption && selectedOption.value) {
                        const stock = parseFloat(selectedOption.dataset.stock) || 0;
                        const maxTransfer = parseFloat(selectedOption.dataset.max) || 0;
                        const buyingPrice = parseFloat(selectedOption.dataset.price) || 0;

                        // Auto-populate transfer price from item's buying price
                        if (transferPriceInput) {
                            transferPriceInput.value = buyingPrice.toFixed(4);
                        }

                        if (stockDisplay) {
                            stockDisplay.textContent = `${stock} available`;
                            stockDisplay.className = stock > 0 ?
                                'text-sm font-medium text-green-600 stock-display' :
                                'text-sm font-medium text-red-600 stock-display';
                        }

                        if (qtyInput) {
                            qtyInput.max = maxTransfer;
                            qtyInput.placeholder = `Max: ${stock}`;

                            if (stock <= 0) {
                                qtyInput.disabled = true;
                                if (stockHint) {
                                    stockHint.textContent = 'No stock available for this item';
                                    stockHint.className = 'text-xs text-red-500 mt-1 stock-hint';
                                }
                            } else {
                                qtyInput.disabled = false;
                                if (stockHint) {
                                    stockHint.className = 'text-xs text-gray-500 mt-1 stock-hint';
                                }
                            }
                        }

                        calculateLineTotal(row);
                        updateGrandTotal();
                    }

                    // Update all remaining stock displays after stock display update
                    updateAllRemainingStock();
                }

                // Enhanced form submission with cumulative validation
                document.getElementById('gtnEditForm').addEventListener('submit', function(e) {
                    // Run cumulative validation before submission
                    if (!validateCumulativeQuantities()) {
                        e.preventDefault();
                        alert(
                            'Some items have total quantities that exceed available stock. Please check the highlighted fields.'
                            );
                        return false;
                    }
                });

                function calculateLineTotal(row) {
                    const qtyInput = row.querySelector('.quantity');
                    const transferPriceInput = row.querySelector('.transfer-price-input');
                    const lineTotalCell = row.querySelector('.line-total');
                    const transferPriceDisplay = row.querySelector('.transfer-price-display');

                    if (qtyInput && transferPriceInput && lineTotalCell) {
                        const qty = parseFloat(qtyInput.value) || 0;
                        const price = parseFloat(transferPriceInput.value) || 0;
                        const total = qty * price;

                        lineTotalCell.textContent = 'Rs. ' + total.toFixed(2);
                        if (transferPriceDisplay) {
                            transferPriceDisplay.textContent = 'Rs. ' + price.toFixed(2);
                        }
                    }
                }

                function updateGrandTotal() {
                    let grandTotal = 0;
                    document.querySelectorAll('.line-total').forEach(cell => {
                        const value = cell.textContent.replace('Rs. ', '').replace(',', '');
                        grandTotal += parseFloat(value) || 0;
                    });
                    document.getElementById('grand-total').textContent = 'Rs. ' + grandTotal.toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                }

                // Attach event listeners to existing elements
                document.querySelectorAll('.item-select').forEach(select => {
                    select.addEventListener('change', function() {
                        updateStockDisplay(this);
                    });
                });

                document.querySelectorAll('.quantity').forEach(input => {
                    input.addEventListener('input', function() {
                        validateQuantity(this);
                        calculateLineTotal(this.closest('.item-row'));
                        updateGrandTotal();
                    });
                });

                document.querySelectorAll('.remove-item').forEach(btn => {
                    btn.addEventListener('click', function() {
                        this.closest('.item-row').remove();
                        updateGrandTotal();
                        // Update remaining stock displays after row removal
                        updateAllRemainingStock();
                    });
                });

                // Load stock data and trigger initial remaining stock calculation
                setTimeout(() => {
                    updateAllRemainingStock();
                }, 500);
            @endif
        });
    </script>
@endpush
