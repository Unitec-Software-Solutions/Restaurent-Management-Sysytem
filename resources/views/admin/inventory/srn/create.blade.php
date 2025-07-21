@extends('layouts.admin')

@section('header-title', 'Create Stock Release Note')
@section('content')
    <div class="mx-auto px-4 py-8">
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

                <!-- Organization Selection for Super Admin -->
                @if (Auth::guard('admin')->user()->is_super_admin)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-building text-blue-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-blue-900">Organization Selection</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Target Organization <span class="text-red-500">*</span>
                                </label>
                                <select name="organization_id" id="organization_id" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Organization</option>
                                    @foreach ($organizations as $org)
                                        <option value="{{ $org->id }}"
                                            {{ old('organization_id', request('organization_id')) == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-sm text-blue-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    SRN will be created for the selected organization
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Display current organization for non-super admins -->
                    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-building text-gray-600 mr-2"></i>
                            <div>
                                <h3 class="text-sm font-medium text-gray-700">Organization</h3>
                                <p class="text-gray-900 font-semibold">
                                    {{ Auth::guard('admin')->user()->organization->name }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

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
                                        {{ old('branch_id', $branchId ?? '') == $branch->id ? 'selected' : '' }}>
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
                                {{-- <option value="sale" @selected(old('release_type')=='sale')>Sale</option>
                                <option value="transfer" @selected(old('release_type')=='transfer')>Transfer</option> --}}
                                <option value="usage" @selected(old('release_type')=='usage')>Usage</option>
                                {{-- <option value="kit" @selected(old('release_type')=='kit')>Kit</option>
                                <option value="staff_usage" @selected(old('release_type')=='staff_usage')>Staff Usage</option> --}}
                                <option value="internal_usage" @selected(old('release_type')=='internal_usage')>Internal Usage</option>
                                <option value="other" @selected(old('release_type')=='other')>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

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
                                    <tr class="item-row border-b bg-white placeholder-row" id="defaultPlaceholderRow">
                                        <td class="px-4 py-3">
                                            <select name="items[0][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" required>
                                                <option value="">Select Item</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium stock-display text-gray-600">-</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" name="items[0][release_quantity]" value=""
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg quantity" required
                                                min="0.01" max="" placeholder="0.00">
                                            <div class="text-xs text-gray-500 mt-1 stock-hint"></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[0][notes]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                                placeholder="Notes (optional)">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" class="remove-item text-red-500 hover:text-red-700" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        rows="3" maxlength="500" placeholder="Add any special instructions or notes for this SRN...">{{ old('notes') }}</textarea>
                </div>

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
            let itemCounter = 1;
            let availableItems = [];
            let selectedBranchId = null;

            const orgSelect = document.getElementById('organization_id');
            const branchSelect = document.getElementById('branch_id');
            const addItemBtn = document.getElementById('addItemBtn');
            const itemsContainer = document.getElementById('itemsContainer');
            const defaultPlaceholderRow = document.getElementById('defaultPlaceholderRow');

            addItemBtn.disabled = true;
            addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');

            function updateItemSelectOptions() {
                document.querySelectorAll('.item-select').forEach(select => {
                    const currentValue = select.value;
                    select.innerHTML = '<option value="">Select Item</option>' +
                        availableItems.map(item =>
                            `<option value="${item.id}"
                                data-code="${item.item_code}"
                                data-stock="${item.current_stock}"
                                data-max="${item.current_stock}">
                                ${item.item_code} - ${item.name}
                            </option>`
                        ).join('');
                    select.value = currentValue;
                    // Re-attach change event for stock update
                    select.onchange = function() { handleItemChange(this); };
                    // If an item is already selected, update stock display immediately
                    handleItemChange(select);
                });
            }

            function handleItemChange(selectElement) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const row = selectElement.closest('.item-row');
                const stockDisplay = row.querySelector('.stock-display');
                const qtyInput = row.querySelector('.quantity');
                const stockHint = row.querySelector('.stock-hint');

                if (selectedOption && selectedOption.value) {
                    const stock = parseFloat(selectedOption.dataset.stock) || 0;
                    const maxRelease = parseFloat(selectedOption.dataset.max) || 0;

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
            }

            function createItemRow() {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row border-b bg-white';
                newRow.innerHTML = `
                    <td class="px-4 py-3">
                        <select name="items[${itemCounter}][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" required>
                            <option value="">Select Item</option>
                            ${availableItems.map(item =>
                                `<option value="${item.id}"
                                    data-code="${item.item_code}"
                                    data-stock="${item.current_stock}"
                                    data-max="${item.current_stock}">
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
                    // Optionally add validation logic here
                });

                const removeBtn = newRow.querySelector('.remove-item');
                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                });

                itemCounter++;
            }

            function fetchBranchesForOrganization(orgId) {
            branchSelect.innerHTML = '<option value="">Loading branches...</option>';
            fetch(`/admin/api/organizations/${orgId}/branches`)
                .then(response => response.json())
                .then(data => {
                    branchSelect.innerHTML = '<option value="">Select Branch</option>';
                    // FIX: Use data.branches instead of data
                    (data.branches || []).forEach(branch => {
                        branchSelect.innerHTML += `<option value="${branch.id}">${branch.name}</option>`;
                    });
                    branchSelect.value = '';
                    itemsContainer.innerHTML = '';
                    itemsContainer.appendChild(defaultPlaceholderRow);
                    addItemBtn.disabled = true;
                    addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    availableItems = [];
                    updateItemSelectOptions();
                });
            }

            function fetchItemsWithStock(branchId, orgId = null) {
                itemsContainer.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading available items...
                        </td>
                    </tr>
                `;
                let url = `{{ route('admin.inventory.srn.items-with-stock') }}?branch_id=${branchId}`;
                if (orgId) url += `&organization_id=${orgId}`;
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        availableItems = data;
                        itemsContainer.innerHTML = '';
                        itemsContainer.appendChild(defaultPlaceholderRow);
                        updateItemSelectOptions();
                        if (availableItems.length > 0) {
                            addItemBtn.disabled = false;
                            addItemBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        } else {
                            itemsContainer.innerHTML = `
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-yellow-600">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No items with available stock found in this branch
                                    </td>
                                </tr>
                            `;
                            addItemBtn.disabled = true;
                            addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    })
                    .catch(error => {
                        itemsContainer.innerHTML = `
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-red-600">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    Error loading items: ${error.message}
                                </td>
                            </tr>
                        `;
                        addItemBtn.disabled = true;
                        addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    });
            }

            if (orgSelect) {
                orgSelect.addEventListener('change', function() {
                    const orgId = orgSelect.value;
                    if (orgId) {
                        fetchBranchesForOrganization(orgId);
                    } else {
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';
                        itemsContainer.innerHTML = '';
                        itemsContainer.appendChild(defaultPlaceholderRow);
                        addItemBtn.disabled = true;
                        addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        availableItems = [];
                        updateItemSelectOptions();
                    }
                });
            }

            branchSelect.addEventListener('change', function() {
                selectedBranchId = branchSelect.value;
                let orgId = orgSelect ? orgSelect.value : null;
                itemsContainer.innerHTML = '';
                itemsContainer.appendChild(defaultPlaceholderRow);
                if (selectedBranchId) {
                    addItemBtn.disabled = true;
                    addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    fetchItemsWithStock(selectedBranchId, orgId);
                } else {
                    addItemBtn.disabled = true;
                    addItemBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    availableItems = [];
                    updateItemSelectOptions();
                }
            });

            addItemBtn.addEventListener('click', function() {
                if (availableItems.length > 0) {
                    createItemRow();
                }
            });

            updateItemSelectOptions();
        });
    </script>
@endpush
