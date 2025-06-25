@extends('layouts.admin')

@section('title', 'Aggregate Production Requests')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Aggregate Production Requests
                    </h1>
                    <p class="text-gray-600 mt-1">Create production orders from approved requests with ingredient
                        calculations</p>
                </div>
                <a href="{{ route('admin.production.orders.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg border border-red-200 shadow">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <form method="GET" action="{{ route('admin.production.requests.aggregate') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Date From</label>
                            <input type="date" name="required_date_from" value="{{ request('required_date_from') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Date To</label>
                            <input type="date" name="required_date_to" value="{{ request('required_date_to') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <form action="{{ route('admin.production.orders.store_aggregated') }}" method="POST" id="aggregateForm">
                @csrf

                <!-- Production Requests Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Approved Production Requests ({{ $requests->count() }})
                            </h3>
                            <div class="flex items-center gap-4">
                                <button type="button" id="selectAllBtn"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Select All
                                </button>
                                <button type="button" id="clearAllBtn"
                                    class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                    Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    @if ($requests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-12 px-6 py-3"></th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Required Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Qty</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($requests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" name="selected_requests[]"
                                                    value="{{ $request->id }}"
                                                    class="request-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">Request #{{ $request->id }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->request_date->format('M d, Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $request->branch->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $request->items->count() }} items
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    @foreach ($request->items->take(3) as $item)
                                                        {{ $item->item->name }}@if (!$loop->last)
                                                            ,
                                                        @endif
                                                    @endforeach
                                                    @if ($request->items->count() > 3)
                                                        <span class="text-gray-400">... +{{ $request->items->count() - 3 }}
                                                            more</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $request->required_date->format('M d, Y') }}</div>
                                                @if ($request->required_date->isPast())
                                                    <div class="text-xs text-red-500">Overdue</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ number_format($request->getTotalQuantityApproved(), 2) }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <i class="fas fa-box-open text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900">No Approved Requests</h3>
                            <p class="text-gray-500">No approved production requests found for the selected criteria.</p>
                        </div>
                    @endif
                </div>

                <!-- Selected Requests Summary -->
                <div id="selectedSummary" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-blue-900">Selected Requests Summary</h3>
                        <button type="button" id="previewIngredientsBtn"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-eye mr-2"></i>Preview Ingredients
                        </button>
                    </div>
                    <div id="selectedRequestsList" class="text-sm text-blue-700"></div>
                </div>

                <!-- Aggregated Items Preview -->
                <div id="aggregatedItemsPreview" class="hidden bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Aggregated Production Items</h3>
                    </div>
                    <div id="aggregatedItemsContent"></div>
                </div>

                <!-- Ingredient Requirements Preview -->
                <div id="ingredientRequirements" class="hidden bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Required Ingredients</h3>
                            <button type="button" id="addManualIngredientBtn"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-plus mr-2"></i>Add Manual Ingredient
                            </button>
                        </div>
                    </div>
                    <div id="ingredientRequirementsContent"></div>

                    <!-- Manual Ingredients Section -->
                    <div id="manualIngredientsSection" class="p-6 border-t border-gray-200 bg-gray-50 hidden">
                        <h4 class="text-md font-semibold text-gray-900 mb-4">Manual Ingredients</h4>
                        <div id="manualIngredientsList"></div>
                    </div>
                </div>

                <!-- Production Order Notes -->
                <div id="productionNotesSection" class="hidden bg-white rounded-xl shadow-sm p-6 mb-6">
                    <label for="production_notes" class="block text-sm font-medium text-gray-700 mb-2">Production
                        Notes</label>
                    <textarea name="production_notes" id="production_notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter any special instructions or notes for this production order..."></textarea>
                </div>

                <!-- Submit Button -->
                <div id="submitSection" class="hidden flex justify-end">
                    <button type="submit" id="createProductionOrderBtn"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Create Production Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manual Ingredient Modal -->
    <div id="manualIngredientModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Add Manual Ingredient</h3>
                        <button type="button" id="closeManualIngredientModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ingredient</label>
                        <select id="manualIngredientSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select an ingredient...</option>
                            @foreach ($availableIngredients as $ingredient)
                                <option value="{{ $ingredient->id }}" data-name="{{ $ingredient->name }}"
                                    data-unit="{{ $ingredient->unit_of_measurement }}">
                                    {{ $ingredient->name }} ({{ $ingredient->unit_of_measurement }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input type="number" id="manualIngredientQuantity" step="0.001" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0.000">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea id="manualIngredientNotes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="Optional notes..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" id="cancelManualIngredient"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="button" id="addManualIngredient"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Add Ingredient
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectAllBtn = document.getElementById('selectAllBtn');
                const clearAllBtn = document.getElementById('clearAllBtn');
                const requestCheckboxes = document.querySelectorAll('.request-checkbox');
                const selectedSummary = document.getElementById('selectedSummary');
                const previewIngredientsBtn = document.getElementById('previewIngredientsBtn');
                const aggregatedItemsPreview = document.getElementById('aggregatedItemsPreview');
                const ingredientRequirements = document.getElementById('ingredientRequirements');
                const productionNotesSection = document.getElementById('productionNotesSection');
                const submitSection = document.getElementById('submitSection');
                const manualIngredientModal = document.getElementById('manualIngredientModal');
                const addManualIngredientBtn = document.getElementById('addManualIngredientBtn');

                let selectedRequests = [];
                let manualIngredients = [];

                // Select All functionality - prevent duplicates
                selectAllBtn.addEventListener('click', function() {
                    requestCheckboxes.forEach(checkbox => {
                        if (!checkbox.checked) {
                            checkbox.checked = true;
                            const requestId = checkbox.value;
                            if (!selectedRequests.includes(requestId)) {
                                selectedRequests.push(requestId);
                            }
                        }
                    });
                    updateSummary();
                });

                // Clear All functionality
                clearAllBtn.addEventListener('click', function() {
                    requestCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    selectedRequests = [];
                    manualIngredients = [];
                    hideSections();
                });

                // Individual checkbox change
                requestCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const requestId = this.value;
                        if (this.checked) {
                            if (!selectedRequests.includes(requestId)) {
                                selectedRequests.push(requestId);
                            }
                        } else {
                            selectedRequests = selectedRequests.filter(id => id !== requestId);
                        }
                        updateSummary();
                    });
                });

                // Preview ingredients
                previewIngredientsBtn.addEventListener('click', function() {
                    if (selectedRequests.length === 0) {
                        alert('Please select at least one request first.');
                        return;
                    }
                    fetchIngredientRequirements();
                });

                // Manual ingredient modal
                addManualIngredientBtn.addEventListener('click', function() {
                    manualIngredientModal.classList.remove('hidden');
                });

                document.getElementById('closeManualIngredientModal').addEventListener('click', function() {
                    manualIngredientModal.classList.add('hidden');
                    clearManualIngredientForm();
                });

                document.getElementById('cancelManualIngredient').addEventListener('click', function() {
                    manualIngredientModal.classList.add('hidden');
                    clearManualIngredientForm();
                });

                document.getElementById('addManualIngredient').addEventListener('click', function() {
                    addManualIngredient();
                });

                function updateSummary() {
                    if (selectedRequests.length === 0) {
                        hideSections();
                        return;
                    }

                    selectedSummary.classList.remove('hidden');
                    const selectedRequestsList = document.getElementById('selectedRequestsList');
                    selectedRequestsList.innerHTML =
                        `Selected ${selectedRequests.length} request(s): ${selectedRequests.map(id => `#${id}`).join(', ')}`;
                }

                function hideSections() {
                    selectedSummary.classList.add('hidden');
                    aggregatedItemsPreview.classList.add('hidden');
                    ingredientRequirements.classList.add('hidden');
                    productionNotesSection.classList.add('hidden');
                    submitSection.classList.add('hidden');
                }

                function fetchIngredientRequirements() {
                    const requestIds = selectedRequests.join(',');

                    // Show loading
                    aggregatedItemsPreview.classList.remove('hidden');
                    document.getElementById('aggregatedItemsContent').innerHTML =
                        '<div class="p-6 text-center"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i><p class="text-gray-500 mt-2">Loading aggregated items...</p></div>';

                    ingredientRequirements.classList.remove('hidden');
                    document.getElementById('ingredientRequirementsContent').innerHTML =
                        '<div class="p-6 text-center"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i><p class="text-gray-500 mt-2">Calculating ingredient requirements...</p></div>';

                    // Fetch ingredient calculations
                    fetch(`{{ route('admin.production.requests.calculate-ingredients') }}?request_ids=${requestIds}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                displayAggregatedItems(data.aggregatedItems);
                                displayIngredientRequirements(data.ingredients);
                                showProductionSections();
                            } else {
                                alert('Error calculating ingredients: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error fetching ingredient requirements');
                        });
                }

                function displayAggregatedItems(items) {
                    let html = '<div class="overflow-x-auto"><table class="w-full"><thead class="bg-gray-50"><tr>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Requests</th>';
                    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

                    Object.values(items).forEach(item => {
                        html += '<tr>';
                        html +=
                            `<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">${item.item.name}</div></td>`;
                        html +=
                            `<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">${parseFloat(item.total_quantity).toFixed(2)} ${item.item.unit_of_measurement}</div></td>`;
                        html += '<td class="px-6 py-4"><div class="text-xs text-gray-500">';
                        item.requests.forEach(req => {
                            html +=
                                `Request #${req.request_id} (${req.branch}): ${parseFloat(req.quantity).toFixed(2)}<br>`;
                        });
                        html += '</div></td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    document.getElementById('aggregatedItemsContent').innerHTML = html;
                }

                function displayIngredientRequirements(ingredients) {
                    let html = '<div class="overflow-x-auto"><table class="w-full"><thead class="bg-gray-50"><tr>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingredient</th>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required Quantity</th>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Stock (HQ)</th>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>';
                    html +=
                        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Items</th>';
                    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

                    Object.values(ingredients).forEach(ingredient => {
                        const availableStock = parseFloat(ingredient.available_stock || 0);
                        const requiredQty = parseFloat(ingredient.total_required);
                        const isShort = availableStock < requiredQty;

                        html += '<tr>';
                        html +=
                            `<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">${ingredient.item.name}</div><div class="text-sm text-gray-500">${ingredient.unit}</div></td>`;
                        html +=
                            `<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">${requiredQty.toFixed(3)} ${ingredient.unit}</div></td>`;
                        html +=
                            `<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm ${isShort ? 'text-red-600' : 'text-green-600'}">${availableStock.toFixed(3)} ${ingredient.unit}</div></td>`;
                        html += '<td class="px-6 py-4 whitespace-nowrap">';
                        if (isShort) {
                            html +=
                                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Short by ' +
                                (requiredQty - availableStock).toFixed(3) + '</span>';
                        } else {
                            html +=
                                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Sufficient</span>';
                        }
                        html += '</td>';
                        html += '<td class="px-6 py-4"><div class="text-xs text-gray-500">';
                        ingredient.from_items.forEach(item => {
                            html +=
                                `${item.production_item}: ${parseFloat(item.quantity_needed).toFixed(3)}<br>`;
                        });
                        html += '</div></td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    document.getElementById('ingredientRequirementsContent').innerHTML = html;

                    // Update manual ingredients display
                    updateManualIngredientsDisplay();
                }

                function addManualIngredient() {
                    const select = document.getElementById('manualIngredientSelect');
                    const quantity = document.getElementById('manualIngredientQuantity');
                    const notes = document.getElementById('manualIngredientNotes');

                    if (!select.value || !quantity.value || parseFloat(quantity.value) <= 0) {
                        alert('Please select an ingredient and enter a valid quantity.');
                        return;
                    }

                    const selectedOption = select.selectedOptions[0];
                    const ingredient = {
                        id: select.value,
                        name: selectedOption.dataset.name,
                        unit: selectedOption.dataset.unit,
                        quantity: parseFloat(quantity.value),
                        notes: notes.value || ''
                    };

                    // Check if ingredient already exists
                    const existingIndex = manualIngredients.findIndex(ing => ing.id === ingredient.id);
                    if (existingIndex !== -1) {
                        manualIngredients[existingIndex].quantity += ingredient.quantity;
                    } else {
                        manualIngredients.push(ingredient);
                    }

                    updateManualIngredientsDisplay();
                    manualIngredientModal.classList.add('hidden');
                    clearManualIngredientForm();
                }

                function updateManualIngredientsDisplay() {
                    const section = document.getElementById('manualIngredientsSection');
                    const list = document.getElementById('manualIngredientsList');

                    if (manualIngredients.length === 0) {
                        section.classList.add('hidden');
                        return;
                    }

                    section.classList.remove('hidden');

                    let html = '';
                    manualIngredients.forEach((ingredient, index) => {
                        html +=
                            `<div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 mb-2">`;
                        html += `<div>`;
                        html += `<div class="text-sm font-medium text-gray-900">${ingredient.name}</div>`;
                        html +=
                            `<div class="text-sm text-gray-500">${ingredient.quantity.toFixed(3)} ${ingredient.unit}</div>`;
                        if (ingredient.notes) {
                            html += `<div class="text-xs text-gray-400">${ingredient.notes}</div>`;
                        }
                        html += `</div>`;
                        html +=
                            `<button type="button" onclick="removeManualIngredient(${index})" class="text-red-600 hover:text-red-800">`;
                        html += `<i class="fas fa-trash"></i>`;
                        html += `</button>`;
                        html += `</div>`;

                        // Add hidden inputs for form submission
                        html +=
                            `<input type="hidden" name="manual_ingredients[${index}][ingredient_id]" value="${ingredient.id}">`;
                        html +=
                            `<input type="hidden" name="manual_ingredients[${index}][quantity]" value="${ingredient.quantity}">`;
                        html +=
                            `<input type="hidden" name="manual_ingredients[${index}][notes]" value="${ingredient.notes}">`;
                    });

                    list.innerHTML = html;
                }

                window.removeManualIngredient = function(index) {
                    manualIngredients.splice(index, 1);
                    updateManualIngredientsDisplay();
                };

                function clearManualIngredientForm() {
                    document.getElementById('manualIngredientSelect').value = '';
                    document.getElementById('manualIngredientQuantity').value = '';
                    document.getElementById('manualIngredientNotes').value = '';
                }

                function showProductionSections() {
                    productionNotesSection.classList.remove('hidden');
                    submitSection.classList.remove('hidden');

                    // Add validation before allowing submission
                    const form = document.getElementById('aggregateForm');
                    form.addEventListener('submit', function(e) {
                        // Check for ingredient shortages
                        const ingredientRows = document.querySelectorAll(
                            '#ingredientRequirementsContent tbody tr');
                        let hasShortages = false;
                        let shortageItems = [];

                        ingredientRows.forEach(row => {
                            const statusCell = row.querySelector('td:nth-child(4) span');
                            if (statusCell && statusCell.textContent.includes('Short by')) {
                                hasShortages = true;
                                const ingredientName = row.querySelector(
                                    'td:first-child .text-gray-900').textContent;
                                shortageItems.push(ingredientName);
                            }
                        });

                        if (hasShortages) {
                            e.preventDefault();
                            const proceed = confirm(
                                `WARNING: The following ingredients have insufficient stock in HQ branch:\n\n${shortageItems.join('\n')}\n\nDo you want to proceed anyway? You will need to purchase or transfer these ingredients before production can begin.`
                                );
                            if (proceed) {
                                // Remove the event listener and resubmit
                                form.removeEventListener('submit', arguments.callee);
                                form.submit();
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
