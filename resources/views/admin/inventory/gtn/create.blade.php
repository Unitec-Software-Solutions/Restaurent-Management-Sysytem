@extends('layouts.admin')

@section('header-title', 'Create Goods Transfer Note')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create New Goods Transfer Note</h2>
                    <p class="text-sm text-gray-500">Transfer goods between branches</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.inventory.gtn.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
                    </a>
                </div>
            </div>

            <!-- Form Container -->
            <form action="{{ route('admin.inventory.gtn.store') }}" method="POST" class="p-6" id="gtnForm">
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

                <!-- GTN Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GTN Number</label>
                        <input type="text" value="{{ $nextGtnNumber }}" disabled
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100">
                        <input type="hidden" name="gtn_number" value="{{ $nextGtnNumber }}">
                    </div>

                    <div>
                        <label for="from_branch_id" class="block text-sm font-medium text-gray-700 mb-1">Origin Branch
                            (Select First) *</label>
                        <div class="relative">
                            <select id="from_branch_id" name="from_branch_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                                <option value="">Select Origin Branch First</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">

                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Select this first to see available items with stock</p>
                    </div>

                    <div>
                        <label for="to_branch_id" class="block text-sm font-medium text-gray-700 mb-1">Destination Branch
                            *</label>
                        <div class="relative">
                            <select id="to_branch_id" name="to_branch_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                                <option value="">Select Destination Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">

                            </div>
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
                                value="{{ old('transfer_date', now()->format('Y-m-d')) }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>

                        </div>
                    </div>

                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference
                            Number</label>
                        <input type="text" id="reference_number" name="reference_number"
                            value="{{ old('reference_number') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                {{-- <!-- Unified Status Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <span class="text-sm text-blue-800 font-medium">Unified GTN System Status Information</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
                        <div>
                            <p class="font-medium">Origin Status Workflow:</p>
                            <p>Draft → Confirmed → In Delivery → Delivered</p>
                        </div>
                        <div>
                            <p class="font-medium">Receiver Status Workflow:</p>
                            <p>Pending → Received → Verified → Accepted/Rejected</p>
                        </div>
                    </div>
                    <p class="text-sm text-blue-700 mt-2">
                        This GTN will be created with "Draft" origin status and "Pending" receiver status.
                        After creation, you can confirm the GTN to deduct stock and begin the transfer workflow.
                    </p>
                </div> --}}

                <!-- Items Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Transfer Items</h3>
                        <button type="button" id="addItemBtn"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
                    </div>

                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3">Item</th>
                                        <th class="px-4 py-3">Available Stock</th>
                                        <th class="px-4 py-3">Transfer Quantity*</th>
                                        {{-- <th class="px-4 py-3">Batch No</th> --}}
                                        {{-- <th class="px-4 py-3">Expiry Date</th> --}}
                                        <th class="px-4 py-3">Notes</th>
                                        <th class="px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <!-- Items will be added dynamically after selecting origin branch -->
                                    <tr id="noItemsRow">
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Please select an origin branch first to see available items with stock
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        rows="3" maxlength="500" placeholder="Add any special instructions or notes for this GTN...">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                    <button type="reset"
                        class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset Form
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Create GTN
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCounter = 0;
            let availableItems = [];
            let selectedBranchId = null;

            const fromBranchSelect = document.getElementById('from_branch_id');
            const toBranchSelect = document.getElementById('to_branch_id');
            const addItemBtn = document.getElementById('addItemBtn');
            const itemsContainer = document.getElementById('itemsContainer');

            // Initially disable the add item button
            addItemBtn.disabled = true;
            addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // --- Helper to create a placeholder item row ---
            function createPlaceholderRow() {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row border-b bg-white placeholder-row';
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
                        <!-- Hidden field for transfer_price, auto-populated from item buying_price -->
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
                        <input type="text" name="items[${itemCounter}][notes]"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="Notes (optional)">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" class="remove-item text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;

                // Add event listeners to the new row
                const select = newRow.querySelector('.item-select');
                const qtyInput = newRow.querySelector('.quantity');
                const removeBtn = newRow.querySelector('.remove-item');

                select.addEventListener('change', function() {
                    handleItemChange(this);
                });

                qtyInput.addEventListener('input', function() {
                    validateQuantity(this);
                });

                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                });

                itemsContainer.appendChild(newRow);
                itemCounter++;
            }

            // --- End Helper ---

            // Handle origin branch selection
            fromBranchSelect.addEventListener('change', function() {
                const branchId = this.value;
                selectedBranchId = branchId;

                if (branchId) {
                    updateToBranchOptions(branchId);
                    fetchItemsWithStock(branchId);
                } else {
                    resetItemsContainer();
                    addItemBtn.disabled = true;
                    addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });

            // Handle destination branch selection
            toBranchSelect.addEventListener('change', function() {
                const fromBranchId = fromBranchSelect.value;
                if (this.value === fromBranchId) {
                    alert('Destination branch cannot be the same as origin branch');
                    this.value = '';
                }
            });

            function updateToBranchOptions(excludeBranchId) {
                const options = toBranchSelect.querySelectorAll('option');
                options.forEach(option => {
                    if (option.value === excludeBranchId) {
                        option.disabled = true;
                        option.style.display = 'none';
                    } else {
                        option.disabled = false;
                        option.style.display = 'block';
                    }
                });

                // Reset selection if currently selected branch is now excluded
                if (toBranchSelect.value === excludeBranchId) {
                    toBranchSelect.value = '';
                }
            }

            function fetchItemsWithStock(branchId) {
                // Show loading state
                itemsContainer.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading available items...
                        </td>
                    </tr>
                `;

                fetch(`{{ route('admin.inventory.gtn.items-with-stock') }}?branch_id=${branchId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        availableItems = data;
                        resetItemsContainer();

                        if (availableItems.length > 0) {
                            addItemBtn.disabled = false;
                            addItemBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        } else {
                            itemsContainer.innerHTML = `
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-yellow-600">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No items with available stock found in this branch
                                    </td>
                                </tr>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        itemsContainer.innerHTML = `
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-red-600">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    Error loading items: ${error.message}
                                </td>
                            </tr>
                        `;
                    });
            }

            function resetItemsContainer() {
                itemsContainer.innerHTML = '';
                itemCounter = 0;
                // Always add a placeholder row
                createPlaceholderRow();
            }

            // Add new item row
            addItemBtn.addEventListener('click', function() {
                if (!selectedBranchId || availableItems.length === 0) {
                    alert('Please select an origin branch with available stock first');
                    return;
                }
                createPlaceholderRow();
            });

            // Handle item selection change
            function handleItemChange(selectElement) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const row = selectElement.closest('.item-row');
                const stockDisplay = row.querySelector('.stock-display');
                const qtyInput = row.querySelector('.quantity');
                const stockHint = row.querySelector('.stock-hint');
                const transferPriceInput = row.querySelector('.transfer-price-input');

                if (selectedOption && selectedOption.value) {
                    const stock = parseFloat(selectedOption.dataset.stock);
                    const maxTransfer = parseFloat(selectedOption.dataset.max);
                    const buyingPrice = parseFloat(selectedOption.dataset.price) || 0;

                    // Auto-populate transfer price from item's buying price
                    transferPriceInput.value = buyingPrice.toFixed(4);

                    // Update the available stock display
                    stockDisplay.textContent = `${stock} available`;
                    stockDisplay.className = stock > 0 ? 'text-sm font-medium text-green-600 stock-display' :
                        'text-sm font-medium text-red-600 stock-display';

                    qtyInput.max = maxTransfer;
                    qtyInput.placeholder = `Max: ${stock}`;

                    if (stock <= 0) {
                        qtyInput.disabled = true;
                        qtyInput.value = '';
                        stockHint.textContent = 'No stock available for this item';
                        stockHint.className = 'text-xs text-red-500 mt-1 stock-hint';
                    } else {
                        qtyInput.disabled = false;
                        stockHint.className = 'text-xs text-gray-500 mt-1 stock-hint';
                    }
                } else {
                    stockDisplay.textContent = '-';
                    stockDisplay.className = 'text-sm font-medium text-gray-600 stock-display';
                    transferPriceInput.value = '0';
                    qtyInput.max = '';
                    qtyInput.placeholder = '0.00';
                    qtyInput.disabled = false;
                    stockHint.textContent = '';
                }
            }

            // Validate quantity input
            function validateQuantity(qtyInput) {
                const max = parseFloat(qtyInput.max);
                const value = parseFloat(qtyInput.value);
                const row = qtyInput.closest('.item-row');
                const stockHint = row.querySelector('.stock-hint');

                if (value > max) {
                    qtyInput.setCustomValidity(`Quantity cannot exceed ${max.toFixed(2)}`);
                    stockHint.textContent = `Error: Maximum allowed is ${max.toFixed(2)}`;
                    stockHint.className = 'text-xs text-red-500 mt-1 stock-hint';
                } else {
                    qtyInput.setCustomValidity('');
                    const selectedOption = row.querySelector('.item-select').selectedOptions[0];
                    if (selectedOption) {
                        const stock = parseFloat(selectedOption.dataset.stock);
                        const remainingStock = stock - value;
                        stockHint.textContent = `Remaining stock after transfer: ${remainingStock.toFixed(2)}`;
                        stockHint.className = 'text-xs text-gray-500 mt-1 stock-hint';
                    }
                }
            }

            // Form submission validation
            gtnForm.addEventListener('submit', function(e) {
                // Simplified validation - no modal confirmation
                const itemRows = document.querySelectorAll('.item-row');
                const validRows = Array.from(itemRows).filter(row => {
                    const selectInput = row.querySelector('.item-select');
                    return selectInput && selectInput.value;
                });

                if (validRows.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one item to transfer');
                    return false;
                }

                let hasErrors = false;
                validRows.forEach(row => {
                    const qtyInput = row.querySelector('.quantity');
                    const selectInput = row.querySelector('.item-select');
                    if (!selectInput.value) {
                        hasErrors = true;
                        selectInput.focus();
                        return;
                    }
                    if (!qtyInput.value || parseFloat(qtyInput.value) <= 0) {
                        hasErrors = true;
                        qtyInput.focus();
                        return;
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    alert('Please fill in all required fields correctly');
                    return false;
                }

                // Form will submit normally without modal confirmation
            });

            // On page load, always show a placeholder row
            resetItemsContainer();
        });
    </script>
@endpush
