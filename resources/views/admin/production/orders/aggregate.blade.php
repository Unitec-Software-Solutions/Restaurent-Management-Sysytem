@extends('layouts.admin')

@section('title', 'Aggregate Production Requests')

@section('header-title', 'Aggregate Production Requests')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Production', 'link' => route('admin.production.index')],
                    ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                    ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                    ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
                    ['name' => 'Production Recipes', 'link' => route('admin.production.recipes.index')],
                    // ['name' => 'Ingredient Management', 'link' => '#', 'disabled' => true],
                ]" active="Production Orders" />
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2 mt-0.5"></i>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create Production Order</h2>
                    <p class="text-sm text-gray-500 mt-1">Aggregate approved production requests into a single efficient
                        order</p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if (Auth::guard('admin')->user()->is_super_admin)
                            Organization: All Organizations (Super Admin)
                        @elseif(Auth::guard('admin')->user()->organization)
                            Organization: {{ Auth::guard('admin')->user()->organization->name }}
                        @else
                            Organization: Not Assigned
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.production.orders.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                    </a>
                </div>
            </div>

            <!-- Form Container -->
            <form action="{{ route('admin.production.orders.store_aggregated') }}" method="POST" class="p-6"
                id="createProductionOrderForm">
                @csrf

                <!-- Production Details -->
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Production Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="production_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Production Date <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                datepicker datepicker-buttons datepicker-autoselect-today datepicker-format="yyyy-mm-dd"
                                type="text"
                                name="production_date"
                                id="production_date"
                                required
                                value="{{ old('production_date', now()->addDay()->toDateString()) }}"
                                min="{{ now()->format('Y-m-d') }}"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="production_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Production Notes
                            </label>
                            <textarea name="production_notes" id="production_notes" rows="1"
                                placeholder="Special instructions for production team..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                style="max-height: 10rem; overflow-y: auto;"
                                oninput="this.style.height = ''; this.style.height = Math.min(this.scrollHeight, 160) + 'px';">{{ old('production_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Production Requests Selection -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold  text-gray-900">Select Production Requests</h3>
                        <div class="flex gap-2">
                            <button type="button" id="selectAllBtn"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Select All
                            </button>
                            <span class="text-gray-300">|</span>
                            <button type="button" id="clearAllBtn"
                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                                Clear All
                            </button>
                        </div>
                    </div>

                    @if ($requests->isEmpty())
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium text-gray-900">No approved production requests found</p>
                            <p class="text-sm text-gray-500">Approved requests will appear here for aggregation</p>
                            <a href="{{ route('admin.production.requests.index') }}"
                                class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-arrow-left mr-2"></i>View all requests
                            </a>
                        </div>
                    @else
                        <div class="space-y-3 grid-cols-2 " id="requestsList">
                            @foreach ($requests as $request)
                                <div
                                    class="border border-gray-200 rounded-lg p-4 request-item hover:bg-gray-50 transition duration-150">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <input type="checkbox" name="selected_requests[]" value="{{ $request->id }}"
                                                class="mt-1 request-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <h4 class="font-medium text-gray-900">
                                                        Request #{{ $request->id }}
                                                    </h4>
                                                    <span class="text-sm text-gray-500">•</span>
                                                    <span
                                                        class="text-sm font-medium text-gray-700">{{ $request->branch->name }}</span>
                                                </div>
                                                <p class="text-sm text-gray-600 mb-3">
                                                    Required by: {{ $request->required_date->format('M d, Y') }}
                                                    @if ($request->required_date->isPast())
                                                        <span
                                                            class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                            Overdue
                                                        </span>
                                                    @endif
                                                </p>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($request->items as $item)
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                            {{ $item->item->name }}:
                                                            {{ number_format($item->quantity_approved) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Aggregated Items Preview -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6" id="aggregatedItemsSection"
                    style="display: none;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-layer-group text-blue-600 mr-2"></i>
                        Aggregated Production Items
                    </h3>
                    <div id="aggregatedItemsList" class="space-y-3"></div>
                </div>

                <!-- Ingredients Requirements -->
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6" id="ingredientsSection"
                    style="display: none;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-leaf text-green-600 mr-2"></i>
                            Ingredient Requirements
                        </h3>
                        <button type="button" id="addIngredientBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i>Add Manual Ingredient
                        </button>
                    </div>

                    <!-- Recipe-based Ingredients -->
                    <div id="recipeIngredientsSection">
                        <h4 class="text-md font-medium text-gray-800 mb-3">From Recipes</h4>
                        <div id="ingredientsList" class="space-y-3"></div>
                    </div>

                    <!-- Manual Ingredients -->
                    <div id="manualIngredientsSection" style="display: none;">
                        <h4 class="text-md font-medium text-gray-800 mb-3 mt-6">Manual Ingredients</h4>
                        <div id="manualIngredientsList" class="space-y-3"></div>
                    </div>
                </div>

                <!-- Hidden Form Fields -->
                <div id="recipeIngredientsContainer"></div>
                <div id="manualIngredientsContainer"></div>

                <!-- Actions -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('admin.production.orders.index') }}"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" id="createOrderBtn" disabled
                            class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg flex items-center">
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
        document.addEventListener('DOMContentLoaded', function() { // Global variables
            window.selectedRequests = [];
            window.aggregatedItems = {};
            window.calculatedIngredients = {};
            window.editedIngredients = {};
            window.manualIngredients = {};

            // DOM elements
            const requestCheckboxes = document.querySelectorAll('.request-checkbox');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const clearAllBtn = document.getElementById('clearAllBtn');
            const createOrderBtn = document.getElementById('createOrderBtn');
            const aggregatedItemsSection = document.getElementById('aggregatedItemsSection');
            const ingredientsSection = document.getElementById('ingredientsSection');

            // Event listeners
            requestCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', handleRequestSelection);
            });

            selectAllBtn?.addEventListener('click', selectAllRequests);
            clearAllBtn?.addEventListener('click', clearAllRequests);

            function handleRequestSelection() {
                const selectedIds = Array.from(requestCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                window.selectedRequests = selectedIds;

                if (selectedIds.length > 0) {
                    calculateAggregatedItems();
                    createOrderBtn.disabled = false;
                } else {
                    aggregatedItemsSection.style.display = 'none';
                    ingredientsSection.style.display = 'none';
                    createOrderBtn.disabled = true;
                }
            }

            function selectAllRequests() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                handleRequestSelection();
            }

            function clearAllRequests() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                handleRequestSelection();
            }

            function calculateAggregatedItems() {
                if (window.selectedRequests.length === 0) return;

                fetch(
                        `{{ route('admin.production.requests.calculate-ingredients') }}?request_ids=${window.selectedRequests.join(',')}`
                    )
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.aggregatedItems = data.aggregatedItems;
                            window.calculatedIngredients = data.ingredients;

                            displayAggregatedItems();
                            displayIngredients();

                            aggregatedItemsSection.style.display = 'block';
                            ingredientsSection.style.display = 'block';
                        } else {
                            alert('Error calculating ingredients: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error calculating ingredients. Please try again.');
                    });
            }

            function displayAggregatedItems() {
                const container = document.getElementById('aggregatedItemsList');
                container.innerHTML = '';

                Object.values(window.aggregatedItems).forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'flex items-center justify-between p-3 bg-blue-50 rounded-lg';
                    itemDiv.innerHTML = `
                    <div>
                        <h4 class="font-medium text-gray-900">${item.item.name}</h4>
                        <p class="text-sm text-gray-600">Total quantity: ${item.total_quantity}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">${item.requests.length} request(s)</p>
                    </div>
                `;
                    container.appendChild(itemDiv);
                });
            }

            function displayIngredients() {
                updateIngredientsDisplay();
            }

            window.updateIngredientsDisplay = function() {
                const container = document.getElementById('ingredientsList');
                const manualContainer = document.getElementById('manualIngredientsList');
                const hiddenContainer = document.getElementById('manualIngredientsContainer');

                // Clear containers
                container.innerHTML = '';
                manualContainer.innerHTML = '';
                hiddenContainer.innerHTML = '';

                // Display calculated ingredients
                Object.values(window.calculatedIngredients).forEach(ingredient => {
                    const ingredientDiv = document.createElement('div');
                    ingredientDiv.className =
                        'flex items-center justify-between p-3 bg-gray-50 rounded-lg';

                    const stockStatus = ingredient.available_stock >= ingredient.total_required ?
                        'sufficient' : 'insufficient';
                    const stockClass = stockStatus === 'sufficient' ? 'text-green-600' : 'text-red-600';

                    ingredientDiv.innerHTML = `
                    <div>
                        <h4 class="font-medium text-gray-900">${ingredient.item.name}</h4>
                        <p class="text-sm text-gray-600">Required: ${ingredient.total_required} ${ingredient.unit}</p>
                        <p class="text-sm ${stockClass}">Available: ${ingredient.available_stock} ${ingredient.unit}</p>
                    </div>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">From Recipe</span>
                `;
                    container.appendChild(ingredientDiv);
                });

                // Display manual ingredients
                Object.entries(window.manualIngredients).forEach(([ingredientId, ingredient], index) => {
                    const ingredientDiv = document.createElement('div');
                    ingredientDiv.className =
                        'flex items-center justify-between p-3 bg-yellow-50 rounded-lg';

                    const stockStatus = ingredient.current_stock >= ingredient.total_required ?
                        'sufficient' : 'insufficient';
                    const stockClass = stockStatus === 'sufficient' ? 'text-green-600' : 'text-red-600';

                    ingredientDiv.innerHTML = `
                    <div>
                        <h4 class="font-medium text-gray-900">${ingredient.name}</h4>
                        <p class="text-sm text-gray-600">Required: ${ingredient.total_required} ${ingredient.unit}</p>
                        <p class="text-sm ${stockClass}">Available: ${ingredient.current_stock} ${ingredient.unit}</p>
                        ${ingredient.notes ? `<p class="text-sm text-gray-500 italic">${ingredient.notes}</p>` : ''}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Manual</span>
                        <button type="button" onclick="removeManualIngredient('${ingredientId}')"
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                    manualContainer.appendChild(ingredientDiv);

                    // Add hidden form fields
                    const hiddenFields = document.createElement('div');
                    hiddenFields.innerHTML = `
                    <input type="hidden" name="manual_ingredients[${index}][ingredient_id]" value="${ingredientId}">
                    <input type="hidden" name="manual_ingredients[${index}][quantity]" value="${ingredient.total_required}">
                    <input type="hidden" name="manual_ingredients[${index}][notes]" value="${ingredient.notes || ''}">
                `;
                    hiddenContainer.appendChild(hiddenFields);
                });
            };
            window.removeManualIngredient = function(ingredientId) {
                delete window.manualIngredients[ingredientId];
                window.updateIngredientsDisplay();
            };

            window.resetIngredientQuantity = function(ingredientId) {
                delete window.editedIngredients[ingredientId];
                window.updateIngredientsDisplay();
            };

            // Enhanced updateIngredientsDisplay function with editable recipe ingredients
            window.updateIngredientsDisplay = function() {
                const container = document.getElementById('ingredientsList');
                const manualContainer = document.getElementById('manualIngredientsList');
                const manualSection = document.getElementById('manualIngredientsSection');
                const recipeHiddenContainer = document.getElementById('recipeIngredientsContainer');
                const manualHiddenContainer = document.getElementById('manualIngredientsContainer');

                // Clear containers
                container.innerHTML = '';
                manualContainer.innerHTML = '';
                recipeHiddenContainer.innerHTML = '';
                manualHiddenContainer.innerHTML = '';

                // Display recipe-based ingredients (editable)
                let recipeIndex = 0;
                Object.entries(window.calculatedIngredients).forEach(([ingredientId, ingredient]) => {
                    const editedIngredient = window.editedIngredients[ingredientId];
                    const currentQuantity = editedIngredient ? editedIngredient.total_required :
                        ingredient.total_required;
                    const isEdited = editedIngredient ? true : false;

                    const stockStatus = ingredient.available_stock >= currentQuantity ? 'sufficient' :
                        'insufficient';
                    const stockClass = stockStatus === 'sufficient' ? 'text-green-600' : 'text-red-600';

                    const ingredientDiv = document.createElement('div');
                    ingredientDiv.className = 'border border-gray-200 rounded-lg p-4';

                    // Build recipe source information
                    let recipeSource = '';
                    if (ingredient.from_items && ingredient.from_items.length > 0) {
                        recipeSource = ingredient.from_items.map(item => item.production_item).join(
                            ', ');
                    }

                    ingredientDiv.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h4 class="font-medium text-gray-900">${ingredient.item.name}</h4>
                                <span class="text-xs ${isEdited ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'} px-2 py-1 rounded-full">
                                    ${isEdited ? 'Manually Adjusted' : 'From Recipe'}
                                </span>
                            </div>

                            ${recipeSource ? `<p class="text-xs text-gray-500 mb-2">Used in: ${recipeSource}</p>` : ''}

                            <div class="grid grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Required Quantity</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               step="0.001"
                                               min="0.001"
                                               value="${currentQuantity}"
                                               data-ingredient-id="${ingredientId}"
                                               data-original-quantity="${ingredient.total_required}"
                                               class="ingredient-quantity-input flex-1 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <span class="text-sm text-gray-600">${ingredient.unit}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Available Stock</label>
                                    <p class="text-sm ${stockClass} py-1">${ingredient.available_stock} ${ingredient.unit}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                                <textarea rows="2"
                                          placeholder="Additional preparation notes..."
                                          data-ingredient-id="${ingredientId}"
                                          class="ingredient-notes-input w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">${editedIngredient ? editedIngredient.notes : ''}</textarea>
                            </div>
                        </div>

                        <div class="ml-4 flex flex-col gap-2">
                            ${isEdited ?
                                `<button type="button" onclick="resetIngredientQuantity('${ingredientId}')"
                                                                                         class="text-blue-600 hover:text-blue-800 text-xs">
                                                                                    <i class="fas fa-undo mr-1"></i>Reset
                                                                                 </button>` : ''}
                        </div>
                    </div>
                `;
                    container.appendChild(ingredientDiv);

                    // Add hidden form fields for recipe ingredients
                    const hiddenFields = document.createElement('div');
                    hiddenFields.innerHTML = `
                    <input type="hidden" name="recipe_ingredients[${recipeIndex}][ingredient_id]" value="${ingredientId}">
                    <input type="hidden" name="recipe_ingredients[${recipeIndex}][quantity]" value="${currentQuantity}" id="hidden_recipe_${ingredientId}_quantity">
                    <input type="hidden" name="recipe_ingredients[${recipeIndex}][notes]" value="${editedIngredient ? editedIngredient.notes : ''}" id="hidden_recipe_${ingredientId}_notes">
                    <input type="hidden" name="recipe_ingredients[${recipeIndex}][is_edited]" value="${isEdited ? '1' : '0'}" id="hidden_recipe_${ingredientId}_edited">
                `;
                    recipeHiddenContainer.appendChild(hiddenFields);
                    recipeIndex++;
                });

                // Display manual ingredients
                let manualIndex = 0;
                const hasManualIngredients = Object.keys(window.manualIngredients).length > 0;
                if (hasManualIngredients) {
                    manualSection.style.display = 'block';

                    Object.entries(window.manualIngredients).forEach(([ingredientId, ingredient]) => {
                        const stockStatus = ingredient.current_stock >= ingredient.total_required ?
                            'sufficient' : 'insufficient';
                        const stockClass = stockStatus === 'sufficient' ? 'text-green-600' :
                            'text-red-600';

                        const ingredientDiv = document.createElement('div');
                        ingredientDiv.className =
                            'flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200';

                        ingredientDiv.innerHTML = `
                        <div>
                            <h4 class="font-medium text-gray-900">${ingredient.name}</h4>
                            <p class="text-sm text-gray-600">Required: ${ingredient.total_required} ${ingredient.unit}</p>
                            <p class="text-sm ${stockClass}">Available: ${ingredient.current_stock} ${ingredient.unit}</p>
                            ${ingredient.notes ? `<p class="text-sm text-gray-500 italic">${ingredient.notes}</p>` : ''}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Manual</span>
                            <button type="button" onclick="removeManualIngredient('${ingredientId}')"
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                        manualContainer.appendChild(ingredientDiv);

                        // Add hidden form fields for manual ingredients
                        const hiddenFields = document.createElement('div');
                        hiddenFields.innerHTML = `
                        <input type="hidden" name="manual_ingredients[${manualIndex}][ingredient_id]" value="${ingredientId}">
                        <input type="hidden" name="manual_ingredients[${manualIndex}][quantity]" value="${ingredient.total_required}">
                        <input type="hidden" name="manual_ingredients[${manualIndex}][notes]" value="${ingredient.notes || ''}">
                    `;
                        manualHiddenContainer.appendChild(hiddenFields);
                        manualIndex++;
                    });
                } else {
                    manualSection.style.display = 'none';
                }

                // Add event listeners for quantity changes
                setTimeout(() => {
                    document.querySelectorAll('.ingredient-quantity-input').forEach(input => {
                        input.addEventListener('input', function() {
                            const ingredientId = this.dataset.ingredientId;
                            const originalQuantity = parseFloat(this.dataset
                                .originalQuantity);
                            const newQuantity = parseFloat(this.value);

                            if (newQuantity !== originalQuantity) {
                                // Mark as edited
                                if (!window.editedIngredients[ingredientId]) {
                                    window.editedIngredients[ingredientId] = {
                                        ...window.calculatedIngredients[
                                            ingredientId]
                                    };
                                }
                                window.editedIngredients[ingredientId].total_required =
                                    newQuantity;

                                // Update hidden field
                                const hiddenQuantity = document.getElementById(
                                    `hidden_recipe_${ingredientId}_quantity`);
                                const hiddenEdited = document.getElementById(
                                    `hidden_recipe_${ingredientId}_edited`);
                                if (hiddenQuantity) hiddenQuantity.value = newQuantity;
                                if (hiddenEdited) hiddenEdited.value = '1';

                                // Refresh display to show "Manually Adjusted" badge
                                setTimeout(() => window.updateIngredientsDisplay(),
                                    100);
                            }
                        });
                    });

                    // Add event listeners for notes changes
                    document.querySelectorAll('.ingredient-notes-input').forEach(textarea => {
                        textarea.addEventListener('input', function() {
                            const ingredientId = this.dataset.ingredientId;
                            const notes = this.value;

                            if (!window.editedIngredients[ingredientId]) {
                                window.editedIngredients[ingredientId] = {
                                    ...window.calculatedIngredients[ingredientId]
                                };
                            }
                            window.editedIngredients[ingredientId].notes = notes;

                            // Update hidden field
                            const hiddenNotes = document.getElementById(
                                `hidden_recipe_${ingredientId}_notes`);
                            if (hiddenNotes) hiddenNotes.value = notes;
                        });
                    });
                }, 50);
            };
        });
    </script>
@endpush
