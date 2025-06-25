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

            <form action="{{ route('admin.production.orders.store') }}" method="POST" id="aggregateForm">
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
                                            Total Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($requests as $request)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" name="selected_requests[]"
                                                    value="{{ $request->id }}"
                                                    class="request-checkbox rounded border-gray-300 text-blue-600 shadow-sm"
                                                    data-request='@json($request)'>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">Request #{{ $request->id }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $request->branch->name }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ $request->items->count() }} items
                                                </div>
                                                <div class="text-sm text-gray-500 max-w-xs truncate">
                                                    {{ $request->items->pluck('item.name')->join(', ') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $request->required_date->format('M d, Y') }}</div>
                                                @if ($request->required_date->isPast())
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Overdue
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->items->sum('quantity_approved') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium text-gray-900">No approved requests found</p>
                            <p class="text-sm text-gray-500">Check if there are any approved production requests to
                                aggregate</p>
                        </div>
                    @endif
                </div>

                <!-- Selected Requests Summary -->
                <div id="selectedSummary" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-blue-900">Selected Requests Summary</h3>
                        <button type="button" onclick="clearSelection()" class="text-blue-600 hover:text-blue-800 text-sm">
                            Clear Selection
                        </button>
                    </div>
                    <div id="selectedRequestsList" class="text-sm text-blue-700 mb-4"></div>

                    <!-- Aggregated Items Preview -->
                    <div id="aggregatedItemsPreview" class="mt-6">
                        <h4 class="font-medium text-blue-900 mb-3">Production Items to Manufacture</h4>
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item
                                            </th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Total Quantity</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Branches</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Recipe Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="aggregatedItemsBody" class="divide-y divide-gray-200">
                                        <!-- Items will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Ingredients Summary -->
                    <div id="ingredientsSummary" class="mt-6">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-blue-900">Required Ingredients Summary</h4>
                            <div class="flex items-center gap-2">
                                <button type="button" id="calculateIngredientsBtn"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-calculator mr-2"></i>Calculate Ingredients
                                </button>
                                <button type="button" id="addIngredientBtn"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-plus mr-2"></i>Add Manual Ingredient
                                </button>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Ingredient</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Required Quantity</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Unit</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Available Stock</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Source</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Notes</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ingredientsBody" class="divide-y divide-gray-200">
                                        <!-- Ingredients will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="ingredientsEmptyState" class="p-8 text-center text-gray-500 hidden">
                                <i class="fas fa-flask text-3xl mb-2 text-gray-300"></i>
                                <p class="text-sm">No ingredients calculated yet. Select requests and click "Calculate
                                    Ingredients".</p>
                            </div>
                        </div>
                    </div>

                    <!-- Production Order Details -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="production_date" class="block text-sm font-medium text-blue-900 mb-2">Production
                                Date *</label>
                            <input type="date" name="production_date" id="production_date"
                                value="{{ now()->addDay()->toDateString() }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-blue-900 mb-2">Priority</label>
                            <select name="priority" id="priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="production_notes" class="block text-sm font-medium text-blue-900 mb-2">Production
                            Notes</label>
                        <textarea name="production_notes" id="production_notes" rows="3"
                            placeholder="Add any special instructions for production..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Create Production Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Ingredient Modal -->
    @include('admin.production.orders.partials.add-ingredient-modal')

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedRequests = [];
            let aggregatedItems = {};
            let calculatedIngredients = {};
            let manualIngredients = {};

            const requestCheckboxes = document.querySelectorAll('.request-checkbox');
            const selectedSummary = document.getElementById('selectedSummary');
            const selectedRequestsList = document.getElementById('selectedRequestsList');
            const aggregatedItemsBody = document.getElementById('aggregatedItemsBody');
            const ingredientsBody = document.getElementById('ingredientsBody');
            const ingredientsEmptyState = document.getElementById('ingredientsEmptyState');
            const calculateIngredientsBtn = document.getElementById('calculateIngredientsBtn');

            // Handle request selection
            requestCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedRequests.push(JSON.parse(this.dataset.request));
                    } else {
                        selectedRequests = selectedRequests.filter(req => req.id != this.value);
                    }
                    updateAggregatedView();
                });
            });

            // Select/Clear all buttons
            document.getElementById('selectAllBtn').addEventListener('click', function() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            document.getElementById('clearAllBtn').addEventListener('click', function() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            // Calculate ingredients button
            calculateIngredientsBtn.addEventListener('click', function() {
                if (Object.keys(aggregatedItems).length === 0) {
                    alert('Please select production requests first.');
                    return;
                }
                calculateIngredients();
            });

            function updateAggregatedView() {
                if (selectedRequests.length === 0) {
                    selectedSummary.classList.add('hidden');
                    return;
                }

                selectedSummary.classList.remove('hidden');

                // Update selected requests list
                selectedRequestsList.innerHTML = selectedRequests.map(req =>
                    `Request #${req.id} (${req.branch.name}) - ${req.items.length} items`
                ).join('<br>');

                // Calculate aggregated items
                aggregatedItems = {};
                selectedRequests.forEach(request => {
                    request.items.forEach(item => {
                        const itemId = item.item_id;
                        if (!aggregatedItems[itemId]) {
                            aggregatedItems[itemId] = {
                                item: item.item,
                                totalQuantity: 0,
                                branches: {}
                            };
                        }

                        aggregatedItems[itemId].totalQuantity += parseFloat(item.quantity_approved);

                        if (!aggregatedItems[itemId].branches[request.branch.name]) {
                            aggregatedItems[itemId].branches[request.branch.name] = 0;
                        }
                        aggregatedItems[itemId].branches[request.branch.name] += parseFloat(item
                            .quantity_approved);
                    });
                });

                updateAggregatedItemsDisplay();
                resetIngredientsDisplay();
            }

            function updateAggregatedItemsDisplay() {
                aggregatedItemsBody.innerHTML = '';
                Object.values(aggregatedItems).forEach(itemData => {
                    const row = document.createElement('tr');
                    const branchesText = Object.entries(itemData.branches)
                        .map(([branch, qty]) => `${branch}: ${qty}`)
                        .join('<br>');

                    row.innerHTML = `
                        <td class="px-4 py-2 text-sm font-medium text-gray-900">${itemData.item.name}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${itemData.totalQuantity}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">${branchesText}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Checking Recipe...
                            </span>
                        </td>
                    `;
                    aggregatedItemsBody.appendChild(row);
                });
            }

            function resetIngredientsDisplay() {
                calculatedIngredients = {};
                manualIngredients = {};
                ingredientsBody.innerHTML = '';
                ingredientsEmptyState.classList.remove('hidden');
            }

            function calculateIngredients() {
                // Show loading state
                calculateIngredientsBtn.disabled = true;
                calculateIngredientsBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Calculating...';

                // Prepare production items data
                const productionItems = [];
                Object.entries(aggregatedItems).forEach(([itemId, itemData]) => {
                    productionItems.push({
                        item_id: itemId,
                        quantity: itemData.totalQuantity
                    });
                });

                // Make AJAX call to calculate ingredients
                fetch('/admin/production/calculate-ingredients-from-recipes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            production_items: productionItems
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        calculatedIngredients = data.ingredients_summary || {};
                        updateIngredientsDisplay();
                        updateRecipeStatus(data.missing_recipes || []);
                    })
                    .catch(error => {
                        console.error('Error calculating ingredients:', error);
                        alert('Error calculating ingredients. Please try again.');
                    })
                    .finally(() => {
                        calculateIngredientsBtn.disabled = false;
                        calculateIngredientsBtn.innerHTML =
                            '<i class="fas fa-calculator mr-2"></i>Calculate Ingredients';
                    });
            }

            function updateIngredientsDisplay() {
                ingredientsBody.innerHTML = '';
                ingredientsEmptyState.classList.add('hidden');

                // Display calculated ingredients
                Object.entries(calculatedIngredients).forEach(([ingredientId, data]) => {
                    addIngredientRow(ingredientId, data, 'recipe');
                });

                // Display manual ingredients
                Object.entries(manualIngredients).forEach(([ingredientId, data]) => {
                    addIngredientRow(ingredientId, data, 'manual');
                });

                if (Object.keys(calculatedIngredients).length === 0 && Object.keys(manualIngredients).length ===
                    0) {
                    ingredientsEmptyState.classList.remove('hidden');
                }
            }

            function addIngredientRow(ingredientId, data, source) {
                const row = document.createElement('tr');
                const isManual = source === 'manual';
                const stockStatus = data.current_stock >= data.total_required ? 'text-green-600' : 'text-red-600';
                const stockIcon = data.current_stock >= data.total_required ? 'fa-check-circle' :
                    'fa-exclamation-triangle';

                row.innerHTML = `
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">${data.name}</td>
                    <td class="px-4 py-2">
                        <input type="number" step="0.001" min="0.001"
                            name="ingredients[${ingredientId}][planned_quantity]"
                            value="${data.total_required}"
                            class="w-24 px-2 py-1 border border-gray-300 rounded text-sm"
                            ${!isManual ? 'readonly' : ''}>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-600">${data.unit}</td>
                    <td class="px-4 py-2">
                        <div class="flex items-center">
                            <i class="fas ${stockIcon} ${stockStatus} mr-1"></i>
                            <span class="text-sm ${stockStatus}">${data.current_stock || 0}</span>
                        </div>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-600">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${isManual ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                            ${isManual ? 'Manual' : 'Recipe'}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="ingredients[${ingredientId}][notes]"
                            placeholder="Notes..."
                            class="w-32 px-2 py-1 border border-gray-300 rounded text-sm">
                    </td>
                    <td class="px-4 py-2">
                        ${isManual ? `<button type="button" onclick="removeIngredient('${ingredientId}')" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>` : ''}
                    </td>
                    <input type="hidden" name="ingredients[${ingredientId}][ingredient_item_id]" value="${ingredientId}">
                    <input type="hidden" name="ingredients[${ingredientId}][unit_of_measurement]" value="${data.unit}">
                    <input type="hidden" name="ingredients[${ingredientId}][is_manually_added]" value="${isManual ? '1' : '0'}">
                `;

                ingredientsBody.appendChild(row);
            }

            function updateRecipeStatus(missingRecipes) {
                // Update recipe status in aggregated items table
                const rows = aggregatedItemsBody.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    const itemData = Object.values(aggregatedItems)[index];
                    const statusCell = row.children[3];
                    const hasRecipe = !missingRecipes.some(missing => missing.item_id == itemData.item.id);

                    if (hasRecipe) {
                        statusCell.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>Recipe Available
                            </span>
                        `;
                    } else {
                        statusCell.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i>No Recipe
                            </span>
                        `;
                    }
                });
            }

            // Global function for removing ingredients
            window.removeIngredient = function(ingredientId) {
                if (confirm('Are you sure you want to remove this ingredient?')) {
                    delete manualIngredients[ingredientId];
                    updateIngredientsDisplay();
                }
            };

            window.clearSelection = function() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                selectedRequests = [];
                aggregatedItems = {};
                calculatedIngredients = {};
                manualIngredients = {};
                selectedSummary.classList.add('hidden');
            };
        });
    </script>
@endpush
