{{-- Production Request Creation View --}}
@extends('layouts.admin')

@section('title', 'Create Production Request')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Create Production Request</h1>
                    <p class="text-gray-600 mt-1">Request production items from HQ kitchen</p>
                </div>
                <a href="{{ route('admin.production.requests.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Requests
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg border border-red-200">
                    <h4 class="font-medium mb-2">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.production.requests.store') }}" method="POST" id="productionRequestForm">
                @csrf

                <!-- Request Details -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Date *</label>
                            <input type="date" name="required_date" value="{{ old('required_date') }}"
                                min="{{ now()->addDay()->format('Y-m-d') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                            <p class="text-sm text-gray-500 mt-1">When do you need these items?</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <input type="text" value="{{ Auth::user()->branch->name }}"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm" readonly>
                            <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Any special instructions or notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Production Items Selection -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Select Production Items</h2>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Only production items are available for request
                        </div>
                    </div>

                    <!-- Items Search -->
                    <div class="mb-6">
                        <div class="relative">
                            <input type="text" id="itemSearch" placeholder="Search production items..."
                                class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                            <div class="production-item border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors duration-200"
                                data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <input type="checkbox" name="items[{{ $item->id }}][selected]"
                                                value="1"
                                                class="item-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
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
                                                    $currentStock =
                                                        $item
                                                            ->transactions()
                                                            ->where('branch_id', Auth::user()->branch_id)
                                                            ->selectRaw(
                                                                'COALESCE(SUM(CASE
                                                        WHEN transaction_type IN ("purchase", "production", "adjustment_increase") THEN quantity
                                                        WHEN transaction_type IN ("sale", "consumption", "waste", "adjustment_decrease") THEN -quantity
                                                        ELSE 0
                                                    END), 0) as current_stock',
                                                            )
                                                            ->value('current_stock') ?? 0;
                                                @endphp

                                                <span class="flex items-center">
                                                    <i class="fas fa-box text-gray-400 mr-1"></i>
                                                    Current Stock:
                                                    <span
                                                        class="font-medium ml-1 {{ $currentStock <= ($item->reorder_level ?? 0) ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ number_format($currentStock, 2) }}
                                                        {{ $item->unit_of_measurement }}
                                                    </span>
                                                </span>

                                                @if ($item->reorder_level)
                                                    <span class="flex items-center text-gray-500">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        Reorder Level: {{ number_format($item->reorder_level, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="flex-shrink-0 ml-4">
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm font-medium text-gray-700">Quantity:</label>
                                            <div class="flex items-center space-x-1">
                                                <button type="button"
                                                    class="quantity-decrease bg-gray-200 hover:bg-gray-300 text-gray-700 w-8 h-8 rounded-md flex items-center justify-center"
                                                    data-item-id="{{ $item->id }}" disabled>
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <input type="number" name="items[{{ $item->id }}][quantity_requested]"
                                                    class="quantity-input w-20 text-center rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
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
                                                class="item-notes w-full text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Notes for this item..." disabled>
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
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg transition duration-200">
                        Cancel
                    </a>

                    <div class="space-x-3">
                        <button type="submit" name="action" value="save_draft"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-save mr-2"></i>Save as Draft
                        </button>
                        <button type="submit" name="action" value="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200"
                            id="submitBtn" disabled>
                            <i class="fas fa-paper-plane mr-2"></i>Submit Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const submitBtn = document.getElementById('submitBtn');
            const selectedItemsSummary = document.getElementById('selectedItemsSummary');
            const selectedItemsList = document.getElementById('selectedItemsList');
            const totalItemsCount = document.getElementById('totalItemsCount');
            const totalQuantity = document.getElementById('totalQuantity');
            const itemSearch = document.getElementById('itemSearch');

            // Handle item selection
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const itemId = this.dataset.itemId;
                    const isChecked = this.checked;

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
@endsection
