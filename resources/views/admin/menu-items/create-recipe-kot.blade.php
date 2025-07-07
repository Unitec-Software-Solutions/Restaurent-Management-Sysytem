@extends('admin.layout.main')

@section('title', 'Create Recipe-Based Menu Item (KOT)')

@section('custom-styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .recipe-ingredient-row {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
    }
    
    .ingredient-card {
        transition: all 0.3s ease;
    }
    
    .ingredient-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .dietary-badges .badge {
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .prep-time-input {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .spice-level-buttons .btn {
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .recipe-preview {
        background: #f8f9fa;
        border-left: 4px solid #28a745;
        padding: 15px;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-gray-800">
                                <i class="fas fa-fire text-warning me-2"></i>
                                Create Recipe-Based Menu Item
                            </h1>
                            <p class="text-muted mb-0">
                                Create a new dish/recipe that requires kitchen preparation (KOT item)
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('admin.menu-items.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Menu Items
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <form action="{{ route('admin.menu-items.store-kot-recipe') }}" method="POST" enctype="multipart/form-data" id="kotRecipeForm">
                @csrf
                
                <div class="row">
                    <!-- Left Column: Basic Information -->
                    <div class="col-lg-8">
                        <!-- Basic Information Card -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Basic Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                <i class="fas fa-utensils text-primary me-1"></i>
                                                Dish Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   placeholder="Enter dish name (e.g., Chicken Biriyani)"
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="item_code" class="form-label">
                                                <i class="fas fa-barcode text-secondary me-1"></i>
                                                Item Code
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('item_code') is-invalid @enderror" 
                                                   id="item_code" 
                                                   name="item_code" 
                                                   value="{{ old('item_code') }}" 
                                                   placeholder="Auto-generated if empty">
                                            @error('item_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left text-info me-1"></i>
                                        Description
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              placeholder="Describe the dish, cooking method, taste profile, etc.">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="menu_category_id" class="form-label">
                                                <i class="fas fa-tags text-warning me-1"></i>
                                                Menu Category <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('menu_category_id') is-invalid @enderror" 
                                                    id="menu_category_id" 
                                                    name="menu_category_id" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('menu_category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('menu_category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="kitchen_station_id" class="form-label">
                                                <i class="fas fa-fire text-danger me-1"></i>
                                                Kitchen Station <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('kitchen_station_id') is-invalid @enderror" 
                                                    id="kitchen_station_id" 
                                                    name="kitchen_station_id" required>
                                                <option value="">Select Station</option>
                                                @foreach($kitchenStations as $station)
                                                    <option value="{{ $station->id }}" {{ old('kitchen_station_id') == $station->id ? 'selected' : '' }}>
                                                        {{ $station->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('kitchen_station_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recipe Ingredients Card -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-list-ul me-2"></i>
                                    Recipe Ingredients
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p class="text-muted mb-0">Define the ingredients required for this dish</p>
                                        <button type="button" class="btn btn-sm btn-success" id="addIngredientBtn">
                                            <i class="fas fa-plus me-1"></i> Add Ingredient
                                        </button>
                                    </div>
                                </div>

                                <div id="ingredientsContainer">
                                    <!-- Ingredient rows will be added here -->
                                </div>

                                <!-- Template for ingredient row (hidden) -->
                                <div id="ingredientTemplate" class="recipe-ingredient-row" style="display: none;">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">Ingredient</label>
                                            <select class="form-select ingredient-select" name="ingredients[0][item_id]">
                                                <option value="">Select Ingredient</option>
                                                @foreach($ingredients as $ingredient)
                                                    <option value="{{ $ingredient->id }}" data-unit="{{ $ingredient->unit_of_measurement }}">
                                                        {{ $ingredient->name }} ({{ $ingredient->unit_of_measurement }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" class="form-control" name="ingredients[0][quantity]" step="0.001" min="0" placeholder="0.000">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unit</label>
                                            <input type="text" class="form-control unit-display" readonly placeholder="Unit">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Waste %</label>
                                            <input type="number" class="form-control" name="ingredients[0][waste_percentage]" step="0.01" min="0" max="100" value="0" placeholder="0.00">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm d-block remove-ingredient">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <label class="form-label">Preparation Notes (Optional)</label>
                                            <input type="text" class="form-control" name="ingredients[0][notes]" placeholder="e.g., 'chopped finely', 'marinated for 30 mins'">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Pricing & Attributes -->
                    <div class="col-lg-4">
                        <!-- Pricing Card -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Pricing & Availability
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="price" class="form-label">
                                        <i class="fas fa-tag text-success me-1"></i>
                                        Selling Price <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">LKR</span>
                                        <input type="number" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               id="price" 
                                               name="price" 
                                               value="{{ old('price') }}" 
                                               step="0.01" 
                                               min="0" 
                                               placeholder="0.00" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cost_price" class="form-label">
                                        <i class="fas fa-calculator text-warning me-1"></i>
                                        Estimated Cost Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">LKR</span>
                                        <input type="number" 
                                               class="form-control @error('cost_price') is-invalid @enderror" 
                                               id="cost_price" 
                                               name="cost_price" 
                                               value="{{ old('cost_price') }}" 
                                               step="0.01" 
                                               min="0" 
                                               placeholder="Auto-calculated">
                                        @error('cost_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Leave empty to auto-calculate from ingredients</small>
                                </div>

                                <div class="mb-3">
                                    <label for="preparation_time" class="form-label">
                                        <i class="fas fa-clock text-info me-1"></i>
                                        Preparation Time (minutes) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control prep-time-input @error('preparation_time') is-invalid @enderror" 
                                           id="preparation_time" 
                                           name="preparation_time" 
                                           value="{{ old('preparation_time', 15) }}" 
                                           min="1" 
                                           max="480" 
                                           required>
                                    @error('preparation_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_available">
                                                Available
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">
                                                Featured
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dietary Information Card -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-leaf me-2"></i>
                                    Dietary Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Spice Level</label>
                                    <div class="spice-level-buttons">
                                        <input type="radio" class="btn-check" name="spice_level" id="spice_mild" value="mild" {{ old('spice_level', 'mild') === 'mild' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success btn-sm" for="spice_mild">
                                            <i class="fas fa-leaf me-1"></i> Mild
                                        </label>

                                        <input type="radio" class="btn-check" name="spice_level" id="spice_medium" value="medium" {{ old('spice_level') === 'medium' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning btn-sm" for="spice_medium">
                                            <i class="fas fa-fire me-1"></i> Medium
                                        </label>

                                        <input type="radio" class="btn-check" name="spice_level" id="spice_hot" value="hot" {{ old('spice_level') === 'hot' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger btn-sm" for="spice_hot">
                                            <i class="fas fa-fire me-1"></i> Hot
                                        </label>

                                        <input type="radio" class="btn-check" name="spice_level" id="spice_very_hot" value="very_hot" {{ old('spice_level') === 'very_hot' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-dark btn-sm" for="spice_very_hot">
                                            <i class="fas fa-fire me-1"></i> Very Hot
                                        </label>
                                    </div>
                                </div>

                                <div class="dietary-badges mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="is_vegetarian" name="is_vegetarian" value="1" {{ old('is_vegetarian') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_vegetarian">
                                            <span class="badge bg-success">Vegetarian</span>
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="is_vegan" name="is_vegan" value="1" {{ old('is_vegan') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_vegan">
                                            <span class="badge bg-success">Vegan</span>
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="contains_alcohol" name="contains_alcohol" value="1" {{ old('contains_alcohol') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="contains_alcohol">
                                            <span class="badge bg-warning text-dark">Contains Alcohol</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="allergens" class="form-label">Allergens</label>
                                    <input type="text" 
                                           class="form-control @error('allergens') is-invalid @enderror" 
                                           id="allergens" 
                                           name="allergens" 
                                           value="{{ old('allergens') }}" 
                                           placeholder="e.g., nuts, dairy, gluten">
                                    @error('allergens')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="calories" class="form-label">Calories (per serving)</label>
                                    <input type="number" 
                                           class="form-control @error('calories') is-invalid @enderror" 
                                           id="calories" 
                                           name="calories" 
                                           value="{{ old('calories') }}" 
                                           min="0" 
                                           placeholder="e.g., 350">
                                    @error('calories')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Options Card -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-gradient-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs me-2"></i>
                                    Additional Options
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="special_instructions" class="form-label">Special Instructions</label>
                                    <textarea class="form-control @error('special_instructions') is-invalid @enderror" 
                                              id="special_instructions" 
                                              name="special_instructions" 
                                              rows="3" 
                                              placeholder="Any special cooking instructions or notes for kitchen staff">{{ old('special_instructions') }}</textarea>
                                    @error('special_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="customization_options" class="form-label">Customization Options</label>
                                    <textarea class="form-control @error('customization_options') is-invalid @enderror" 
                                              id="customization_options" 
                                              name="customization_options" 
                                              rows="2" 
                                              placeholder="Available customizations (e.g., spice level, sides, extras)">{{ old('customization_options') }}</textarea>
                                    @error('customization_options')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="image" class="form-label">Dish Image</label>
                                    <input type="file" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max 2MB, JPG/PNG format</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('admin.menu-items.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                            <i class="fas fa-save me-2"></i> Create Recipe Menu Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom-scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let ingredientCounter = 0;

    // Initialize Select2
    $('.ingredient-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select an ingredient',
        allowClear: true
    });

    // Add ingredient row
    document.getElementById('addIngredientBtn').addEventListener('click', function() {
        const template = document.getElementById('ingredientTemplate');
        const container = document.getElementById('ingredientsContainer');
        
        const newRow = template.cloneNode(true);
        newRow.style.display = 'block';
        newRow.id = 'ingredient_' + ingredientCounter;
        
        // Update name attributes
        const inputs = newRow.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('[0]', '[' + ingredientCounter + ']');
            }
        });
        
        container.appendChild(newRow);
        
        // Initialize Select2 for new row
        $(newRow).find('.ingredient-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select an ingredient',
            allowClear: true
        });
        
        ingredientCounter++;
    });

    // Remove ingredient row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-ingredient') || e.target.closest('.remove-ingredient')) {
            const row = e.target.closest('.recipe-ingredient-row');
            if (row) {
                row.remove();
            }
        }
    });

    // Update unit display when ingredient is selected
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('ingredient-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const unit = selectedOption.getAttribute('data-unit') || '';
            const unitDisplay = e.target.closest('.recipe-ingredient-row').querySelector('.unit-display');
            if (unitDisplay) {
                unitDisplay.value = unit;
            }
        }
    });

    // Add initial ingredient row
    document.getElementById('addIngredientBtn').click();

    // Form validation
    document.getElementById('kotRecipeForm').addEventListener('submit', function(e) {
        const ingredientRows = document.querySelectorAll('.recipe-ingredient-row[style="display: block;"]');
        
        if (ingredientRows.length === 0) {
            e.preventDefault();
            alert('Please add at least one ingredient to the recipe.');
            return false;
        }

        let hasValidIngredient = false;
        ingredientRows.forEach(row => {
            const select = row.querySelector('.ingredient-select');
            const quantity = row.querySelector('input[name*="[quantity]"]');
            
            if (select.value && quantity.value && parseFloat(quantity.value) > 0) {
                hasValidIngredient = true;
            }
        });

        if (!hasValidIngredient) {
            e.preventDefault();
            alert('Please ensure at least one ingredient has both an item selected and a valid quantity.');
            return false;
        }

        // Disable submit button to prevent double submission
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating...';
    });
});
</script>
@endsection
