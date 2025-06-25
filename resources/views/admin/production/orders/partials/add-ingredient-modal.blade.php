<!-- Add Ingredient Modal -->
<div id="addIngredientModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Add Manual Ingredient</h3>
                    <button type="button" id="closeModalBtn" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="addIngredientForm">
                    <div class="space-y-4">
                        <div>
                            <label for="ingredientSelect" class="block text-sm font-medium text-gray-700 mb-2">
                                Ingredient *
                            </label>
                            <select id="ingredientSelect" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Ingredient</option>
                                @if (isset($availableIngredients))
                                    @foreach ($availableIngredients as $ingredient)
                                        <option value="{{ $ingredient->id }}"
                                            data-unit="{{ $ingredient->unit_of_measurement }}"
                                            data-stock="{{ $ingredient->current_stock ?? 0 }}">
                                            {{ $ingredient->name }} ({{ $ingredient->unit_of_measurement }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div>
                            <label for="ingredientQuantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Required Quantity *
                            </label>
                            <input type="number" id="ingredientQuantity" step="0.001" min="0.001" required
                                placeholder="0.000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="ingredientUnit" class="block text-sm font-medium text-gray-700 mb-2">
                                Unit of Measurement
                            </label>
                            <input type="text" id="ingredientUnit" readonly
                                placeholder="Auto-filled based on ingredient"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>

                        <div>
                            <label for="ingredientNotes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea id="ingredientNotes" rows="2" placeholder="Special preparation notes..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div id="stockWarning" class="hidden p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-400 mr-2 mt-0.5"></i>
                                <div>
                                    <p class="text-sm text-yellow-800 font-medium">Stock Warning</p>
                                    <p class="text-sm text-yellow-700" id="stockWarningText"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button" id="cancelIngredientBtn"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Add Ingredient
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modal = document.getElementById('addIngredientModal');
        const addIngredientBtn = document.getElementById('addIngredientBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelIngredientBtn = document.getElementById('cancelIngredientBtn');
        const addIngredientForm = document.getElementById('addIngredientForm');
        const ingredientSelect = document.getElementById('ingredientSelect');
        const ingredientUnit = document.getElementById('ingredientUnit');
        const ingredientQuantity = document.getElementById('ingredientQuantity');
        const stockWarning = document.getElementById('stockWarning');
        const stockWarningText = document.getElementById('stockWarningText');

        // Show modal
        addIngredientBtn?.addEventListener('click', () => {
            modal.classList.remove('hidden');
            ingredientSelect.focus();
        });

        // Hide modal
        function hideModal() {
            modal.classList.add('hidden');
            addIngredientForm.reset();
            ingredientUnit.value = '';
            stockWarning.classList.add('hidden');
        }

        closeModalBtn?.addEventListener('click', hideModal);
        cancelIngredientBtn?.addEventListener('click', hideModal);

        // Handle ingredient selection
        ingredientSelect?.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                ingredientUnit.value = option.dataset.unit || '';
                checkStock();
            } else {
                ingredientUnit.value = '';
                stockWarning.classList.add('hidden');
            }
        });

        // Handle quantity changes
        ingredientQuantity?.addEventListener('input', checkStock);

        function checkStock() {
            const selectedOption = ingredientSelect.options[ingredientSelect.selectedIndex];
            const currentStock = parseFloat(selectedOption.dataset.stock) || 0;
            const requiredQuantity = parseFloat(ingredientQuantity.value) || 0;

            if (selectedOption.value && requiredQuantity > 0) {
                if (requiredQuantity > currentStock) {
                    stockWarning.classList.remove('hidden');
                    stockWarningText.textContent =
                        `Required quantity (${requiredQuantity}) exceeds available stock (${currentStock}). You may need to purchase more ingredients.`;
                } else {
                    stockWarning.classList.add('hidden');
                }
            } else {
                stockWarning.classList.add('hidden');
            }
        }

        // Handle form submission
        addIngredientForm?.addEventListener('submit', function(e) {
            e.preventDefault();

            const ingredientId = ingredientSelect.value;
            const quantity = parseFloat(ingredientQuantity.value);
            const unit = ingredientUnit.value;
            const notes = document.getElementById('ingredientNotes').value;
            const name = ingredientSelect.options[ingredientSelect.selectedIndex].text;
            const currentStock = parseFloat(ingredientSelect.options[ingredientSelect.selectedIndex]
                .dataset.stock) || 0;

            if (!ingredientId || !quantity) {
                alert('Please select an ingredient and enter a quantity.');
                return;
            }

            // Check if ingredient already exists
            if (window.manualIngredients && window.manualIngredients[ingredientId]) {
                alert(
                    'This ingredient is already added. Please edit the existing entry or remove it first.'
                    );
                return;
            }

            if (window.calculatedIngredients && window.calculatedIngredients[ingredientId]) {
                alert(
                    'This ingredient is already calculated from recipes. You cannot add it manually.'
                    );
                return;
            }

            // Add to manual ingredients
            if (!window.manualIngredients) {
                window.manualIngredients = {};
            }

            window.manualIngredients[ingredientId] = {
                name: name.split(' (')[0], // Remove unit from name
                total_required: quantity,
                unit: unit,
                current_stock: currentStock,
                notes: notes
            };

            // Update display
            if (window.updateIngredientsDisplay) {
                window.updateIngredientsDisplay();
            }

            // Reset form and hide modal
            hideModal();
        });

        // Close modal on outside click
        modal?.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal();
            }
        });
    });
</script>
