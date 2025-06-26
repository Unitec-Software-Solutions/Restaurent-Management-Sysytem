@extends('layouts.admin')

@section('title', 'Create Recipe')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Create New Recipe</h1>
                    <p class="text-gray-600 mt-1">Define production recipe with ingredient requirements</p>
                </div>
                <a href="{{ route('admin.production.recipes.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Recipes
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

            <form action="{{ route('admin.production.recipes.store') }}" method="POST" id="recipeForm">
                @csrf

                <!-- Recipe Basic Information -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Recipe Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="recipe_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Recipe Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="recipe_name" name="recipe_name" value="{{ old('recipe_name') }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter recipe name">
                        </div>

                        <div>
                            <label for="production_item_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Production Item <span class="text-red-500">*</span>
                            </label>
                            <select id="production_item_id" name="production_item_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <label for="yield_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Yield Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="yield_quantity" name="yield_quantity"
                                value="{{ old('yield_quantity', 1) }}" min="0.01" step="0.01" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="How many units this recipe produces">
                        </div>

                        <div>
                            <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-2">
                                Difficulty Level
                            </label>
                            <select id="difficulty_level" name="difficulty_level"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Difficulty</option>
                                <option value="Easy" {{ old('difficulty_level') == 'Easy' ? 'selected' : '' }}>Easy
                                </option>
                                <option value="Medium" {{ old('difficulty_level') == 'Medium' ? 'selected' : '' }}>Medium
                                </option>
                                <option value="Hard" {{ old('difficulty_level') == 'Hard' ? 'selected' : '' }}>Hard
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-2">
                                Preparation Time (minutes)
                            </label>
                            <input type="number" id="preparation_time" name="preparation_time"
                                value="{{ old('preparation_time', 0) }}" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="cooking_time" class="block text-sm font-medium text-gray-700 mb-2">
                                Cooking Time (minutes)
                            </label>
                            <input type="number" id="cooking_time" name="cooking_time"
                                value="{{ old('cooking_time', 0) }}" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="total_time_display" class="block text-sm font-medium text-gray-700 mb-2">
                                Total Time (minutes)
                            </label>
                            <input type="number" id="total_time_display" readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Brief description of the recipe">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Ingredients Section -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Ingredients & Raw Materials</h2>
                        <button type="button" id="addIngredientBtn"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Add Ingredient
                        </button>
                    </div>

                    <div id="ingredientsContainer">
                        <!-- Ingredients will be added here dynamically -->
                        <div class="text-center py-8 text-gray-500" id="noIngredientsMessage">
                            <i class="fas fa-leaf text-3xl mb-2"></i>
                            <p>No ingredients added yet. Click "Add Ingredient" to start.</p>
                        </div>
                    </div>
                </div>

                <!-- Instructions Section -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Cooking Instructions</h2>

                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
                            Step-by-step Instructions
                        </label>
                        <textarea id="instructions" name="instructions" rows="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter detailed cooking instructions...">{{ old('instructions') }}</textarea>
                    </div>

                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Any additional notes, tips, or variations...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.production.recipes.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200">
                        <i class="fas fa-save mr-2"></i>Create Recipe
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ingredient Template (Hidden) -->
    <div id="ingredientTemplate" style="display: none;">
        <div class="ingredient-row border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raw Material <span
                            class="text-red-500">*</span></label>
                    <select name="raw_materials[INDEX][item_id]" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Raw Material</option>
                        @foreach ($rawMaterials as $material)
                            <option value="{{ $material->id }}">{{ $material->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Required <span
                            class="text-red-500">*</span></label>
                    <input type="number" name="raw_materials[INDEX][quantity_required]" min="0.001" step="0.001"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                    <input type="text" name="raw_materials[INDEX][unit_of_measurement]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="kg, liters, pieces">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preparation Notes</label>
                    <input type="text" name="raw_materials[INDEX][preparation_notes]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="chopped, diced, etc.">
                </div>
                <div class="flex-shrink-0 flex items-end">
                    <button type="button"
                        class="remove-ingredient bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let ingredientIndex = 0;

            // Calculate total time
            function updateTotalTime() {
                const prepTime = parseInt(document.getElementById('preparation_time').value) || 0;
                const cookTime = parseInt(document.getElementById('cooking_time').value) || 0;
                document.getElementById('total_time_display').value = prepTime + cookTime;
            }

            document.getElementById('preparation_time').addEventListener('input', updateTotalTime);
            document.getElementById('cooking_time').addEventListener('input', updateTotalTime);

            // Add ingredient functionality
            document.getElementById('addIngredientBtn').addEventListener('click', function() {
                const container = document.getElementById('ingredientsContainer');
                const template = document.getElementById('ingredientTemplate');
                const noMessage = document.getElementById('noIngredientsMessage');

                // Hide no ingredients message
                if (noMessage) {
                    noMessage.style.display = 'none';
                }

                // Clone template
                const newIngredient = template.cloneNode(true);
                newIngredient.style.display = 'block';
                newIngredient.id = '';

                // Replace INDEX with actual index
                newIngredient.innerHTML = newIngredient.innerHTML.replace(/INDEX/g, ingredientIndex);

                container.appendChild(newIngredient);
                ingredientIndex++;

                // Add remove functionality
                newIngredient.querySelector('.remove-ingredient').addEventListener('click', function() {
                    newIngredient.remove();

                    // Show no ingredients message if no ingredients left
                    if (container.children.length === 1) { // Only noIngredientsMessage left
                        if (noMessage) {
                            noMessage.style.display = 'block';
                        }
                    }
                });
            });

            // Form validation
            document.getElementById('recipeForm').addEventListener('submit', function(e) {
                const ingredients = document.querySelectorAll('.ingredient-row');
                if (ingredients.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one ingredient to the recipe.');
                    return false;
                }

                // Update total time before submit
                updateTotalTime();
            });
        });
    </script>
@endpush
