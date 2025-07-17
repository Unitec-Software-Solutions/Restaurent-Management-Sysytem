@extends('layouts.admin')

@section('header-title', 'Create Production Request')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create Production Request</h2>
                    <p class="text-sm text-gray-500">Request production items from HQ kitchen</p>
                </div>
                <div class="flex gap-2">
                    <button type="button"
                        onclick="try { window.history.back(); setTimeout(function(){ window.location='{{ route('admin.production.requests.index') }}'; }, 200); } catch(e) { window.location='{{ route('admin.production.requests.index') }}'; }"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Requests
                    </button>
                </div>
            </div>

            <!-- Form Container -->
            <form action="{{ route('admin.production.requests.store') }}" method="POST" class="p-6"
                id="productionRequestForm">
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

                <!-- Request Details Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="required_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Required Date*
                        </label>
                        <input
                            datepicker datepicker-buttons datepicker-autoselect-today datepicker-format="yyyy-mm-dd"
                            type="text"
                            id="required_date"
                            name="required_date"
                            value="{{ old('required_date', now()->addDay()->format('Y-m-d')) }}"
                            min="{{ now()->addDay()->format('Y-m-d') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            required>
                        <p class="text-xs text-gray-500 mt-1">When do you need these items?</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        @php
                            $user = Auth::user();
                            $userBranchId = $user->branch_id;
                            $isSuperAdmin = $user->is_super_admin;
                            $isOrgAdmin = !$isSuperAdmin && $userBranchId === null;
                        @endphp

                        @if ($isSuperAdmin)
                            <!-- Super Admin can bypass filtering and select any branch -->
                            <select name="branch_id" id="branchSelect" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Select Branch</option>
                                @php
                                    $branches = \App\Models\Branch::where('is_active', true)
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" data-org-id="{{ $branch->organization_id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                        @if ($branch->is_head_office)
                                            (Head Office)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="organization_id" id="organizationIdInput" value="{{ old('organization_id') }}">
                            <p class="text-xs text-gray-500 mt-1">Select any branch (Super Admin access)</p>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var branchSelect = document.getElementById('branchSelect');
                                    var orgInput = document.getElementById('organizationIdInput');
                                    function updateOrgId() {
                                        var selected = branchSelect.options[branchSelect.selectedIndex];
                                        var orgId = selected.getAttribute('data-org-id') || '';
                                        orgInput.value = orgId;
                                    }
                                    branchSelect.addEventListener('change', updateOrgId);
                                    // Set on page load if already selected
                                    updateOrgId();
                                });
                            </script>
                        @elseif ($isOrgAdmin)
                            <!-- Organization Admin can select branches within their organization -->
                            <select name="branch_id" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Select Branch</option>
                                @php
                                    $branches = \App\Models\Branch::where('organization_id', $user->organization_id)
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                        @if ($branch->is_head_office)
                                            (Head Office)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="organization_id" value="{{ $user->organization_id }}">
                            <p class="text-xs text-gray-500 mt-1">Select a branch within your organization</p>
                        @else
                            <!-- Branch Admin - branch is pre-selected -->
                            @php
                                $userBranch = \App\Models\Branch::find($userBranchId);
                                $branchName = $userBranch ? $userBranch->name : 'Unknown Branch';
                            @endphp
                            <input type="text" value="{{ $branchName }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100" readonly>
                            <input type="hidden" name="branch_id" value="{{ $userBranchId }}">
                            <input type="hidden" name="organization_id" value="{{ $user->organization_id }}">
                            <p class="text-xs text-gray-500 mt-1">Your branch (automatically selected)</p>
                        @endif
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea name="notes" id="notes"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        rows="3" maxlength="500" placeholder="Any special instructions or notes...">{{ old('notes') }}</textarea>
                </div>

                <!-- Production Items Selection -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Select Production Items</h3>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Only production items are available for request
                        </div>
                    </div>

                    <!-- Items Search -->
                    <div class="mb-6">
                        <div class="relative">
                            <input type="text" id="itemSearch" placeholder="Search production items..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Items Summary -->
                    <div id="selectedItemsSummary" class="hidden mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="font-medium text-blue-900 mb-2">Selected Items</h3>
                        <div id="selectedItemsList" class="space-y-2"></div>
                        <div class="mt-3 pt-3 border-t border-blue-200">
                            <div class="text-sm font-medium text-blue-900">
                                Total Items: <span id="totalItemsCount">0</span> |
                                Total Quantity: <span id="totalQuantity">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Available Production Items -->
                    <div class="grid grid-cols-1 gap-4" id="productionItemsList">
                        @foreach ($productionItems as $item)
                            <div class="production-item border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors duration-200 cursor-pointer select-none"
                                data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}"
                                onclick="toggleItemSelection('{{ $item->id }}')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <input type="checkbox" name="items[{{ $item->id }}][selected]"
                                                value="1"
                                                class="item-checkbox h-5 w-5 text-indigo-600 border-2 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 checked:bg-indigo-600 checked:border-indigo-600 pointer-events-none"
                                                data-item-id="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $item->id }}][item_id]"
                                                value="{{ $item->id }}">
                                        </div>

                                        <div class="flex-grow">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $item->name }}</h3>
                                            @if ($item->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ $item->description }}</p>
                                            @endif

                                            <!-- Current Stock Information -->
                                            <div class="mt-2 flex items-center space-x-4 text-sm">
                                                @php
                                                    // For stock display, use user's branch if they have one, otherwise show HQ stock for admins
                                                    $stockBranchId = $user->branch_id ?? null;
                                                    if ($stockBranchId) {
                                                        $currentStock = \App\Models\ItemTransaction::stockOnHand(
                                                            $item->id,
                                                            $stockBranchId,
                                                        );
                                                        $stockLocation = $userBranch
                                                            ? $userBranch->name
                                                            : 'Current Branch';
                                                    } else {
                                                        // Admin user - show message about selecting branch first
                                                        $currentStock = 0;
                                                        $stockLocation = 'Select branch to view stock';
                                                    }
                                                @endphp

                                                <span class="flex items-center stock-info"
                                                    data-stock-item="{{ $item->id }}">
                                                    <i class="fas fa-box text-gray-400 mr-1"></i>
                                                    @if ($user->branch_id)
                                                        Current Stock ({{ $stockLocation }}):
                                                        <span
                                                            class="font-medium ml-1 {{ $currentStock <= ($item->reorder_level ?? 0) ? 'text-red-600' : 'text-green-600' }}">
                                                            {{ number_format($currentStock, 2) }}
                                                            {{ $item->unit_of_measurement }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-500 italic">{{ $stockLocation }}</span>
                                                    @endif
                                                </span>

                                                @if ($item->reorder_level && $user->branch_id)
                                                    <span class="flex items-center text-gray-500">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        Reorder Level: {{ number_format($item->reorder_level, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="flex-shrink-0 ml-4" onclick="event.stopPropagation()">
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm font-medium text-gray-700">Quantity:</label>
                                            <div class="flex items-center space-x-1">
                                                <button type="button"
                                                    class="quantity-decrease bg-gray-200 hover:bg-gray-300 text-gray-700 w-8 h-8 rounded-md flex items-center justify-center"
                                                    data-item-id="{{ $item->id }}" disabled>
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <input type="number"
                                                    name="items[{{ $item->id }}][quantity_requested]"
                                                    class="quantity-input w-20 text-center rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                    min="1" step="0.01" value="1"
                                                    data-item-id="{{ $item->id }}" disabled>
                                                <button type="button"
                                                    class="quantity-increase bg-gray-200 hover:bg-gray-300 text-gray-700 w-8 h-8 rounded-md flex items-center justify-center"
                                                    data-item-id="{{ $item->id }}" disabled>
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>
                                            </div>
                                            <span class="text-sm text-gray-500">{{ $item->unit_of_measurement }}</span>
                                        </div>

                                        <!-- Item Notes -->
                                        <div class="mt-2">
                                            <input type="text" name="items[{{ $item->id }}][notes]"
                                                class="item-notes w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Notes for this item..." disabled
                                                onclick="event.stopPropagation()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($productionItems->isEmpty())
                        <div class="text-center py-12">
                            <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Production Items Available</h3>
                            <p class="text-gray-500">Contact your administrator to add production items to the system.</p>
                        </div>
                    @endif
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                    <button type="submit" name="action" value="save_draft"
                        class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Save as Draft
                    </button>
                    <button type="submit" name="action" value="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center"
                        id="submitBtn" disabled>
                        <i class="fas fa-paper-plane mr-2"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const itemCheckboxes = document.querySelectorAll('.item-checkbox');
                const submitBtn = document.getElementById('submitBtn');
                const selectedItemsSummary = document.getElementById('selectedItemsSummary');
                const selectedItemsList = document.getElementById('selectedItemsList');
                const totalItemsCount = document.getElementById('totalItemsCount');
                const totalQuantity = document.getElementById('totalQuantity');
                const itemSearch = document.getElementById('itemSearch');
                const branchSelect = document.querySelector('select[name="branch_id"]');

                // Add global function for item selection toggle
                window.toggleItemSelection = function(itemId) {
                    const checkbox = document.querySelector(`input[data-item-id="${itemId}"].item-checkbox`);
                    if (checkbox && !checkbox.disabled) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));

                        // Add visual feedback for touch devices
                        const itemContainer = checkbox.closest('.production-item');
                        if (checkbox.checked) {
                            itemContainer.classList.add('bg-indigo-50', 'border-indigo-400');
                        } else {
                            itemContainer.classList.remove('bg-indigo-50', 'border-indigo-400');
                        }
                    }
                };

                // Handle branch selection change for admins
                if (branchSelect) {
                    branchSelect.addEventListener('change', function() {
                        const selectedBranchId = this.value;
                        if (selectedBranchId) {
                            updateStockInformation(selectedBranchId);
                        } else {
                            // Clear stock information when no branch is selected
                            document.querySelectorAll('.stock-info').forEach(element => {
                                element.innerHTML =
                                    '<span class="text-gray-500 italic">Select branch to view stock</span>';
                            });
                        }
                    });
                }

                // Function to update stock information for selected branch
                function updateStockInformation(branchId) {
                    // Show loading state
                    document.querySelectorAll('.stock-info').forEach(element => {
                        element.innerHTML = '<span class="text-gray-500 italic">Loading stock...</span>';
                    });

                    // Fetch stock information for the selected branch
                    fetch(`{{ route('admin.production.requests.index') }}?action=get_stock&branch_id=${branchId}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update stock information for each item
                                Object.entries(data.stock_data).forEach(([itemId, stockInfo]) => {
                                    const stockElement = document.querySelector(
                                        `[data-stock-item="${itemId}"]`);
                                    if (stockElement) {
                                        const stockColor = stockInfo.current_stock <= stockInfo
                                            .reorder_level ? 'text-red-600' : 'text-green-600';
                                        stockElement.innerHTML = `
                                        Current Stock (${stockInfo.branch_name}):
                                        <span class="font-medium ml-1 ${stockColor}">
                                            ${parseFloat(stockInfo.current_stock).toFixed(2)} ${stockInfo.unit}
                                        </span>
                                    `;
                                    }
                                });
                            } else {
                                console.error('Failed to fetch stock information');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching stock information:', error);
                            document.querySelectorAll('.stock-info').forEach(element => {
                                element.innerHTML =
                                    '<span class="text-red-500 italic">Error loading stock</span>';
                            });
                        });
                }

                // Handle item selection
                itemCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const itemId = this.dataset.itemId;
                        const isChecked = this.checked;
                        const itemContainer = this.closest('.production-item');

                        // Update visual state
                        if (isChecked) {
                            itemContainer.classList.add('bg-indigo-50', 'border-indigo-400');
                        } else {
                            itemContainer.classList.remove('bg-indigo-50', 'border-indigo-400');
                        }

                        // Enable/disable quantity controls
                        const quantityInput = document.querySelector(
                            `input[name="items[${itemId}][quantity_requested]"]`);
                        const quantityDecrease = document.querySelector(
                            `.quantity-decrease[data-item-id="${itemId}"]`);
                        const quantityIncrease = document.querySelector(
                            `.quantity-increase[data-item-id="${itemId}"]`);
                        const itemNotes = document.querySelector(
                            `input[name="items[${itemId}][notes]"]`);

                        quantityInput.disabled = !isChecked;
                        quantityDecrease.disabled = !isChecked;
                        quantityIncrease.disabled = !isChecked;
                        itemNotes.disabled = !isChecked;

                        if (!isChecked) {
                            quantityInput.value = 1;
                            itemNotes.value = '';
                        }

                        updateSummary();
                    });
                });

                // Handle quantity controls
                document.querySelectorAll('.quantity-decrease').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const itemId = this.dataset.itemId;
                        const input = document.querySelector(
                            `input[name="items[${itemId}][quantity_requested]"]`);
                        const currentValue = parseFloat(input.value) || 1;
                        if (currentValue > 1) {
                            input.value = currentValue - 1;
                            updateSummary();
                        }
                    });
                });

                document.querySelectorAll('.quantity-increase').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const itemId = this.dataset.itemId;
                        const input = document.querySelector(
                            `input[name="items[${itemId}][quantity_requested]"]`);
                        const currentValue = parseFloat(input.value) || 1;
                        input.value = currentValue + 1;
                        updateSummary();
                    });
                });

                // Handle quantity input changes
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.addEventListener('change', updateSummary);
                });

                // Search functionality
                itemSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const items = document.querySelectorAll('.production-item');

                    items.forEach(item => {
                        const itemName = item.dataset.itemName.toLowerCase();
                        if (itemName.includes(searchTerm)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });

                // Ensure only checked items' inputs are submitted
                document.getElementById('productionRequestForm').addEventListener('submit', function(e) {
                    document.querySelectorAll('.item-checkbox').forEach(function(checkbox) {
                        const itemId = checkbox.dataset.itemId;
                        const quantityInput = document.querySelector(
                            `input[name="items[${itemId}][quantity_requested]"]`);
                        const notesInput = document.querySelector(
                            `input[name="items[${itemId}][notes]"]`);
                        if (checkbox.checked) {
                            quantityInput.disabled = false;
                            notesInput.disabled = false;
                        } else {
                            // Remove all inputs for unchecked items
                            if (quantityInput) quantityInput.remove();
                            if (notesInput) notesInput.remove();
                            // Also remove the hidden item_id input
                            const itemIdInput = document.querySelector(
                                `input[name="items[${itemId}][item_id]"]`);
                            if (itemIdInput) itemIdInput.remove();
                        }
                    });
                });

                function updateSummary() {
                    const checkedItems = document.querySelectorAll('.item-checkbox:checked');
                    const hasSelectedItems = checkedItems.length > 0;

                    // Show/hide summary
                    selectedItemsSummary.classList.toggle('hidden', !hasSelectedItems);

                    // Enable/disable submit button
                    submitBtn.disabled = !hasSelectedItems;

                    if (hasSelectedItems) {
                        // Update summary content
                        selectedItemsList.innerHTML = '';
                        let totalQty = 0;

                        checkedItems.forEach(checkbox => {
                            const itemId = checkbox.dataset.itemId;
                            const itemName = checkbox.closest('.production-item').dataset.itemName;
                            const quantity = parseFloat(document.querySelector(
                                `input[name="items[${itemId}][quantity_requested]"]`).value) || 0;

                            totalQty += quantity;

                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'flex justify-between text-sm';
                            itemDiv.innerHTML = `
                                <span>${itemName}</span>
                                <span class="font-medium">${quantity}</span>
                            `;
                            selectedItemsList.appendChild(itemDiv);
                        });

                        totalItemsCount.textContent = checkedItems.length;
                        totalQuantity.textContent = totalQty.toFixed(2);
                    }
                }
            });
        </script>
    @endpush
@endsection
