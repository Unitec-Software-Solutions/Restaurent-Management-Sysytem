@extends('layouts.admin')

@section('title', 'Create Recipe')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Create New Recipe</h1>
                    <p class="text-gray-600 mt-1">Define ingredients and instructions for production items</p>
                </div>
                <a href="{{ route('admin.production.recipes.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Recipes
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg border border-red-200">
                    <h4 class="font-medium">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.production.recipes.store') }}" method="POST">
                @csrf

                <!-- Recipe Basic Information -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Recipe Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="production_item_id" class="block text-sm font-medium text-gray-700 mb-2">Production
                                Item
                                *</label>
                            <select name="production_item_id" id="production_item_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                                <option value="">Select Production Item</option>
                                @foreach ($productionItems as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('production_item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->unit_of_measurement }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="recipe_name" class="block text-sm font-medium text-gray-700 mb-2">Recipe Name
                                *</label>
                            <input type="text" name="recipe_name" id="recipe_name" value="{{ old('recipe_name') }}"
                                placeholder="e.g., Classic Margherita Pizza"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                        </div>

                        <div>
                            <label for="yield_quantity" class="block text-sm font-medium text-gray-700 mb-2">Yield Quantity
                                *</label>
                            <input type="number" name="yield_quantity" id="yield_quantity"
                                value="{{ old('yield_quantity') }}" step="0.01" min="0.01" placeholder="1.00"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                            <p class="text-xs text-gray-500 mt-1">How many units this recipe produces</p>
                        </div>

                        <div>
                            <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-2">Difficulty
                                Level</label>
                            <select name="difficulty_level" id="difficulty_level"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Difficulty</option>
                                <option value="Easy" {{ old('difficulty_level') == 'Easy' ? 'selected' : '' }}>Easy
                                </option>
                                <option value="Medium" {{ old('difficulty_level') == 'Medium' ? 'selected' : '' }}>
                                    Medium
                                </option>
                                <option value="Hard" {{ old('difficulty_level') == 'Hard' ? 'selected' : '' }}>Hard
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-2">Preparation
                                Time (minutes)</label>
                            <input type="number" name="preparation_time" id="preparation_time"
                                value="{{ old('preparation_time') }}" min="0" placeholder="30"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="cooking_time" class="block text-sm font-medium text-gray-700 mb-2">Cooking Time
                                (minutes)</label>
                            <input type="number" name="cooking_time" id="cooking_time" value="{{ old('cooking_time') }}"
                                min="0" placeholder="45"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" rows="3" placeholder="Brief description of the recipe..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Ingredients/Raw Materials -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Ingredients & Raw Materials</h2>
                        <button type="button" id="addIngredient"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Ingredient
                        </button>
                    </div>

                    <div id="ingredientsList" class="space-y-4">
                        @if (old('raw_materials'))
                            @foreach (old('raw_materials') as $index => $material)
                                <div class="ingredient-item border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-gray-900">Ingredient #{{ $index + 1 }}</h4>
                                        <button type="button" class="remove-ingredient text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Raw Material
                                                *</label>
                                            <select name="raw_materials[{{ $index }}][item_id]"
                                                class="w-full rounded border-gray-300" required>
                                                <option value="">Select Material</option>
                                                @foreach ($rawMaterials as $material)
                                                    <option value="{{ $material->id }}"
                                                        {{ old("raw_materials.{$index}.item_id") == $material->id ? 'selected' : '' }}>
                                                        {{ $material->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity
                                                *</label>
                                            <input type="number"
                                                name="raw_materials[{{ $index }}][quantity_required]"
                                                value="{{ old("raw_materials.{$index}.quantity_required") }}"
                                                step="0.001" min="0.001" class="w-full rounded border-gray-300"
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                            <input type="text"
                                                name="raw_materials[{{ $index }}][unit_of_measurement]"
                                                value="{{ old("raw_materials.{$index}.unit_of_measurement") }}"
                                                placeholder="kg, liters, etc." class="w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Preparation
                                                Notes</label>
                                            <input type="text"
                                                name="raw_materials[{{ $index }}][preparation_notes]"
                                                value="{{ old("raw_materials.{$index}.preparation_notes") }}"
                                                placeholder="diced, chopped, etc." class="w-full rounded border-gray-300">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Default empty ingredient row -->
                            <div class="ingredient-item border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-gray-900">Ingredient #1</h4>
                                    <button type="button" class="remove-ingredient text-red-600 hover:text-red-800"
                                        style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Raw Material
                                            *</label>
                                        <select name="raw_materials[0][item_id]" class="w-full rounded border-gray-300"
                                            required>
                                            <option value="">Select Material</option>
                                            @foreach ($rawMaterials as $material)
                                                <option value="{{ $material->id }}">{{ $material->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity
                                            *</label>
                                        <input type="number" name="raw_materials[0][quantity_required]" step="0.001"
                                            min="0.001" class="w-full rounded border-gray-300" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                        <input type="text" name="raw_materials[0][unit_of_measurement]"
                                            placeholder="kg, liters, etc." class="w-full rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Preparation
                                            Notes</label>
                                        <input type="text" name="raw_materials[0][preparation_notes]"
                                            placeholder="diced, chopped, etc." class="w-full rounded border-gray-300">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if (count($rawMaterials) == 0)
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>No ingredients or raw materials available.</p>
                            <p class="text-sm">Please add items with "Ingredients" or "Raw Materials" category first.</p>
                        </div>
                    @endif
                </div>

                <!-- Instructions -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Instructions</h2>
                    <textarea name="instructions" id="instructions" rows="6" placeholder="Step-by-step cooking instructions..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('instructions') }}</textarea>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Notes</h2>
                    <textarea name="notes" id="notes" rows="3" placeholder="Any additional notes or tips..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span class="text-red-500">*</span> Required fields
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.production.recipes.index') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg transition duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-save mr-2"></i>Create Recipe
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let ingredientIndex = {{ old('raw_materials') ? count(old('raw_materials')) : 1 }};
            const rawMaterials = @json($rawMaterials);

            // Add ingredient functionality
            document.getElementById('addIngredient').addEventListener('click', function() {
                const ingredientsList = document.getElementById('ingredientsList');
                const newIngredient = createIngredientItem(ingredientIndex);
                ingredientsList.appendChild(newIngredient);
                ingredientIndex++;
                updateRemoveButtons();
            });

            // Remove ingredient functionality
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-ingredient')) {
                    e.target.closest('.ingredient-item').remove();
                    updateIngredientNumbers();
                    updateRemoveButtons();
                }
            });

            function createIngredientItem(index) {
                const div = document.createElement('div');
                div.className = 'ingredient-item border border-gray-200 rounded-lg p-4';

                let materialsOptions = '<option value="">Select Material</option>';
                rawMaterials.forEach(material => {
                    materialsOptions += `<option value="${material.id}">${material.name}</option>`;
                });

                div.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-900">Ingredient #${index + 1}</h4>
                <button type="button" class="remove-ingredient text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raw Material *</label>
                    <select name="raw_materials[${index}][item_id]" class="w-full rounded border-gray-300" required>
                        ${materialsOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                    <input type="number" name="raw_materials[${index}][quantity_required]"
                        step="0.001" min="0.001" class="w-full rounded border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                    <input type="text" name="raw_materials[${index}][unit_of_measurement]"
                        placeholder="kg, liters, etc." class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preparation Notes</label>
                    <input type="text" name="raw_materials[${index}][preparation_notes]"
                        placeholder="diced, chopped, etc." class="w-full rounded border-gray-300">
                </div>
            </div>
        `;

                return div;
            }

            function updateIngredientNumbers() {
                document.querySelectorAll('.ingredient-item').forEach((item, index) => {
                    const header = item.querySelector('h4');
                    header.textContent = `Ingredient #${index + 1}`;

                    // Update input names
                    const inputs = item.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.name;
                        if (name && name.includes('raw_materials[')) {
                            const newName = name.replace(/raw_materials\[\d+\]/,
                                `raw_materials[${index}]`);
                            input.name = newName;
                        }
                    });
                });
            }

            function updateRemoveButtons() {
                const ingredientItems = document.querySelectorAll('.ingredient-item');
                ingredientItems.forEach((item, index) => {
                    const removeBtn = item.querySelector('.remove-ingredient');
                    if (ingredientItems.length === 1) {
                        removeBtn.style.display = 'none';
                    } else {
                        removeBtn.style.display = 'inline-block';
                    }
                });
            }

            // Initialize remove buttons
            updateRemoveButtons();
        });
    </script>
@endpush
