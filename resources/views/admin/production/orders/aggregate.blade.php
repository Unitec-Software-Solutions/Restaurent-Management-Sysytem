@extends('layouts.admin')

@section('title', 'Create Production Order')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create Production Order</h1>
                        <p class="text-gray-600 mt-1">Aggregate approved production requests into a single order</p>
                    </div>
                    <a href="{{ route('admin.production.orders.index') }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Orders
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.production.orders.store_aggregated') }}" method="POST"
                id="createProductionOrderForm">
                @csrf

                <!-- Production Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Production Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Production Date *</label>
                            <input type="date" name="production_date" required
                                value="{{ old('production_date', now()->addDay()->toDateString()) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Production Notes</label>
                            <textarea name="production_notes" rows="3" placeholder="Special instructions for production..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('production_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Production Requests Selection -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Select Production Requests</h2>
                        <div class="flex gap-2">
                            <button type="button" id="selectAllBtn" class="text-blue-600 hover:text-blue-800 text-sm">
                                Select All
                            </button>
                            <button type="button" id="clearAllBtn" class="text-red-600 hover:text-red-800 text-sm">
                                Clear All
                            </button>
                        </div>
                    </div>

                    @if ($requests->isEmpty())
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">No approved production requests found.</p>
                            <a href="{{ route('admin.production.requests.index') }}"
                                class="text-blue-600 hover:text-blue-800">
                                View all requests
                            </a>
                        </div>
                    @else
                        <div class="space-y-4" id="requestsList">
                            @foreach ($requests as $request)
                                <div class="border border-gray-200 rounded-lg p-4 request-item">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <input type="checkbox" name="selected_requests[]" value="{{ $request->id }}"
                                                class="mt-1 request-checkbox">
                                            <div>
                                                <h3 class="font-medium text-gray-900">
                                                    Request #{{ $request->id }} - {{ $request->branch->name }}
                                                </h3>
                                                <p class="text-sm text-gray-600">
                                                    Required by: {{ $request->required_date->format('M d, Y') }}
                                                </p>
                                                <div class="mt-2">
                                                    @foreach ($request->items as $item)
                                                        <span
                                                            class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-2 mb-1">
                                                            {{ $item->item->name }}:
                                                            {{ number_format($item->quantity_approved) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Aggregated Items Preview -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6" id="aggregatedItemsSection"
                    style="display: none;">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Aggregated Production Items</h2>
                    <div id="aggregatedItemsList" class="space-y-3"></div>
                </div>

                <!-- Ingredients Requirements -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6" id="ingredientsSection"
                    style="display: none;">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Ingredient Requirements</h2>
                        <button type="button" id="addIngredientBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Manual Ingredient
                        </button>
                    </div>
                    <div id="ingredientsList" class="space-y-3"></div>
                    <div id="manualIngredientsList" class="space-y-3 mt-4"></div>
                </div>

                <!-- Manual Ingredients (Hidden Form Fields) -->
                <div id="manualIngredientsContainer"></div>

                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('admin.production.orders.index') }}"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" id="createOrderBtn" disabled
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg">
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
            // Global variables
            window.selectedRequests = [];
            window.aggregatedItems = {};
            window.calculatedIngredients = {};
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
                        `{{ route('admin.production.requests.calculate-ingredients') }}?request_ids=${window.selectedRequests.join(',')}`)
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
        });
    </script>
@endpush
