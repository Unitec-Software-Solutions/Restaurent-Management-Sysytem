@extends('layouts.admin')

@section('header-title', 'Create Stock Release Note')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create New Stock Release Note</h2>
                    <p class="text-sm text-gray-500">Release stock from branch for various purposes</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.inventory.srn.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to SRNs
                    </a>
                </div>
            </div>

            <!-- Form Container -->
            <form action="{{ route('admin.inventory.srn.store') }}" method="POST" class="p-6" id="srnForm">
                @csrf

                @if (isset($isSuperAdmin) && $isSuperAdmin)
                    <!-- Pass organization_id for super admin -->
                    <input type="hidden" name="organization_id" value="{{ $targetOrgId ?? request('organization_id') }}">
                @endif

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

                <!-- Organization Info for Super Admin -->
                @if (Auth::guard('admin')->user()->is_super_admin && isset($organization))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-building text-blue-600 mr-3"></i>
                            <div>
                                <h3 class="text-sm font-medium text-blue-800">Creating SRN for Organization</h3>
                                <p class="text-sm text-blue-700 mt-1">{{ $organization->name }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- SRN Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SRN Number</label>
                        <input type="text" value="{{ $nextSrnNumber ?? '' }}" disabled
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100">
                        <input type="hidden" name="srn_number" value="{{ $nextSrnNumber ?? '' }}">
                    </div>

                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <div class="relative">
                            <select id="branch_id" name="branch_id"
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
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Select branch to see available items with stock</p>
                    </div>

                    <div>
                        <label for="release_type" class="block text-sm font-medium text-gray-700 mb-1">Release Type *</label>
                        <div class="relative">
                            <select id="release_type" name="release_type"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                                <option value="">Select Release Type</option>
                                <option value="wastage" @selected(old('release_type')=='wastage')>Wastage</option>
                                <option value="sale" @selected(old('release_type')=='sale')>Sale</option>
                                <option value="transfer" @selected(old('release_type')=='transfer')>Transfer</option>
                                <option value="usage" @selected(old('release_type')=='usage')>Usage</option>
                                <option value="kit" @selected(old('release_type')=='kit')>Kit</option>
                                <option value="staff_usage" @selected(old('release_type')=='staff_usage')>Staff Usage</option>
                                <option value="internal_usage" @selected(old('release_type')=='internal_usage')>Internal Usage</option>
                                <option value="other" @selected(old('release_type')=='other')>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Release Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="relative">
                        <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Release Date *</label>
                        <div class="relative">
                            <input
                                type="date"
                                id="release_date"
                                name="release_date"
                                value="{{ old('release_date', now()->format('Y-m-d')) }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-10"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number"
                            value="{{ old('reference_number') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Release Items</h3>
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
                                        <th class="px-4 py-3">Release Quantity*</th>
                                        <th class="px-4 py-3">Notes</th>
                                        <th class="px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <tr id="noItemsRow">
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Please select a branch first to see available items with stock
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
                        rows="3" maxlength="500" placeholder="Add any special instructions or notes for this SRN...">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                    <button type="reset"
                        class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset Form
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Create SRN
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

            const branchSelect = document.getElementById('branch_id');
            const addItemBtn = document.getElementById('addItemBtn');
            const itemsContainer = document.getElementById('itemsContainer');

            addItemBtn.disabled = true;
            addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');

            function updateAllRemainingStock() {
                const itemQuantities = new Map();
                const itemStocks = new Map();

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

                        qtyInput.classList.remove('border-red-500');

                        if (totalQtyUsed > availableStock) {
                            qtyInput.classList.add('border-red-500');
                            stockHint.textContent =
                                `Error: Total quantity for this item (${totalQtyUsed.toFixed(2)}) exceeds available stock (${availableStock.toFixed(2)})`;
                            stockHint.className = 'text-xs text-red-500 mt-1 stock-hint';
                        } else {
                            stockHint.textContent =
                                `Remaining stock after release: ${remainingStock.toFixed(2)}`;
                            stockHint.className = 'text-xs text-gray-500 mt-1 stock-hint';
                        }
                    }
                });
            }

            function validateQuantity(qtyInput) {
                const max = parseFloat(qtyInput.max);
                const value = parseFloat(qtyInput.value);

                if (value > max) {
                    qtyInput.setCustomValidity(`Quantity cannot exceed ${max.toFixed(2)}`);
                } else {
                    qtyInput.setCustomValidity('');
                }

                updateAllRemainingStock();
            }

            function handleItemChange(selectElement) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const row = selectElement.closest('.item-row');
                const stockDisplay = row.querySelector('.stock-display');
                const qtyInput = row.querySelector('.quantity');
                const stockHint = row.querySelector('.stock-hint');

                if (selectedOption && selectedOption.value) {
                    const stock = parseFloat(selectedOption.dataset.stock);
                    const maxRelease = parseFloat(selectedOption.dataset.max);

                    stockDisplay.textContent = `${stock} available`;
                    stockDisplay.className = stock > 0 ? 'text-sm font-medium text-green-600 stock-display' :
                        'text-sm font-medium text-red-600 stock-display';

                    qtyInput.max = maxRelease;
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
                    qtyInput.max = '';
                    qtyInput.placeholder = '0.00';
                    qtyInput.disabled = false;
                    stockHint.textContent = '';
                }

                updateAllRemainingStock();
            }

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
                                    data-max="${item.max_release}">
                                    ${item.item_code} - ${item.name}
                                </option>`
                            ).join('')}
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium stock-display text-gray-600">-</div>
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" step="0.01" name="items[${itemCounter}][release_quantity]" value=""
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

                itemsContainer.appendChild(newRow);

                const selectElement = newRow.querySelector('.item-select');
                selectElement.addEventListener('change', function() {
                    handleItemChange(selectElement);
                });

                const qtyInput = newRow.querySelector('.quantity');
                qtyInput.addEventListener('input', function() {
                    validateQuantity(qtyInput);
                });

                itemCounter++;
            }

            branchSelect.addEventListener('change', function() {
                selectedBranchId = branchSelect.value;

                if (selectedBranchId) {
                    addItemBtn.disabled = false;
                    addItemBtn.classList.remove('opacity-50', 'cursor-not-allowed');

                    // Fetch available items for the selected branch
                    fetch(`/admin/api/branches/${selectedBranchId}/available-items`)
                        .then(response => response.json())
                        .then(data => {
                            availableItems = data.items;

                            // Clear existing rows except the placeholder
                            itemsContainer.innerHTML = '';
                            itemCounter = 0;

                            if (availableItems.length > 0) {
                                // Remove the placeholder row if it exists
                                const placeholderRow = document.getElementById('noItemsRow');
                                if (placeholderRow) {
                                    placeholderRow.remove();
                                }

                                // Enable the Add Item button
                                addItemBtn.disabled = false;
                                addItemBtn.classList.remove('opacity-50', 'cursor-not-allowed');

                                // Create a new row for each available item
                                availableItems.forEach(item => {
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
                                                        data-max="${item.max_release}">
                                                        ${item.item_code} - ${item.name}
                                                    </option>`
                                                ).join('')}
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium stock-display text-gray-600">-</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" name="items[${itemCounter}][release_quantity]" value=""
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

                                    itemsContainer.appendChild(newRow);

                                    const selectElement = newRow.querySelector('.item-select');
                                    selectElement.addEventListener('change', function() {
                                        handleItemChange(selectElement);
                                    });

                                    const qtyInput = newRow.querySelector('.quantity');
                                    qtyInput.addEventListener('input', function() {
                                        validateQuantity(qtyInput);
                                    });

                                    itemCounter++;
                                });
                            } else {
                                // Show the placeholder row if no items are available
                                const placeholderRow = document.createElement('tr');
                                placeholderRow.id = 'noItemsRow';
                                placeholderRow.innerHTML = `
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No items available in stock for the selected branch
                                    </td>
                                `;
                                itemsContainer.appendChild(placeholderRow);

                                // Disable the Add Item button
                                addItemBtn.disabled = true;
                                addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            }
                        });
                } else {
                    // Reset the form if no branch is selected
                    itemsContainer.innerHTML = '';
                    addItemBtn.disabled = true;
                    addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });

            addItemBtn.addEventListener('click', function() {
                createPlaceholderRow();
            });

            // Initial setup: hide items section and disable submit button
            itemsContainer.innerHTML = '';
            addItemBtn.disabled = true;
            addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
        });
    </script>
@endpush
