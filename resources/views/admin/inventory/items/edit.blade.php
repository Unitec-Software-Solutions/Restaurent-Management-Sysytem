@extends('layouts.admin')

@section('header-title', 'Edit Inventory Item: ' . $item->name)

@section('content')
    <div class="p-4 rounded-lg">
        {{-- <!-- Header with KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Items Card -->
        <x-partials.cards.stats-card
            title="Total Items"
            value="{{ $totalItems }}"
            trend="+{{ $newItemsToday }} today"
            icon="fas fa-box-open"
            color="indigo" />

        <!-- Active Items Card -->
        <x-partials.cards.stats-card
            title="Active Items"
            value="{{ $activeItems }}"
            trend="{{ $activeItemsChange > 0 ? '+' : '' }}{{ $activeItemsChange }} from yesterday"
            icon="fas fa-check-circle"
            color="green" />

        <!-- Inactive Items Card -->
        <x-partials.cards.stats-card
            title="Inactive Items"
            value="{{ $inactiveItems }}"
            trend="{{ $inactiveItemsChange > 0 ? '+' : '' }}{{ $inactiveItemsChange }} from yesterday"
            icon="fas fa-times-circle"
            color="red" />
    </div> --}}

        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Edit Inventory Item: {{ $item->name }}</h2>
                    <p class="text-sm text-gray-500">Update item details in your inventory</p>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('admin.inventory.items.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Items
                    </a>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div
                    class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-800 dark:border-green-600 dark:text-green-100">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-800 dark:border-red-600 dark:text-red-100">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>There were some problems with your input.</span>
                    </div>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Section -->
            <div class="p-6">
                <form action="{{ route('admin.inventory.items.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information Column -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                                <div class="relative">
                                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Enter item name">
                                    <i class="fas fa-box absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unicode Name</label>
                                <input type="text" name="unicode_name"
                                    value="{{ old('unicode_name', $item->unicode_name) }}"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Enter unicode name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Item Code *</label>
                                <div class="relative">
                                    <input type="text" name="item_code" value="{{ old('item_code', $item->item_code) }}"
                                        required
                                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Enter item code">
                                    <i class="fas fa-barcode absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="item_category_id" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $item->item_category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measurement *</label>
                                <input type="text" name="unit_of_measurement" required
                                    value="{{ old('unit_of_measurement', $item->unit_of_measurement) }}"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="e.g., kg, pcs, liters">
                            </div>
                        </div>

                        <!-- Pricing & Status Column -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (Rs.) *</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="buying_price" required
                                        value="{{ old('buying_price', $item->buying_price) }}"
                                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="0.00">
                                    <i class="fas fa-rupee-sign absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (Rs.) *</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="selling_price" required
                                        value="{{ old('selling_price', $item->selling_price) }}"
                                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="0.00">
                                    <i class="fas fa-rupee-sign absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level *</label>
                                <input type="number" name="reorder_level" required
                                    value="{{ old('reorder_level', $item->reorder_level) }}"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Minimum stock level">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Shelf Life (days)</label>
                                <input type="number" name="shelf_life_in_days"
                                    value="{{ old('shelf_life_in_days', $item->shelf_life_in_days) }}"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Expiry period in days">
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input type="hidden" name="is_perishable" value="0">
                                    <input type="checkbox" name="is_perishable" value="1" id="is_perishable"
                                        {{ old('is_perishable', $item->is_perishable) ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="is_perishable" class="ml-2 block text-sm text-gray-700">Perishable</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="hidden" name="is_menu_item" value="0">
                                    <input type="checkbox" name="is_menu_item" value="1" id="is_menu_item"
                                        {{ old('is_menu_item', $item->is_menu_item) ? 'checked' : '' }}
                                        class="menu-item-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="is_menu_item" class="ml-2 block text-sm text-gray-700">Include in Menu</label>
                                </div>
                            </div>
                        </div>

                        <!-- Menu-Specific Attributes -->
                        <div class="md:col-span-2 menu-attributes mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200 {{ old('is_menu_item', $item->is_menu_item) ? '' : 'hidden' }}">
                            <h4 class="text-lg font-semibold mb-4 text-blue-900 flex items-center">
                                <i class="fas fa-utensils mr-2"></i>
                                Menu Item Properties
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Cuisine Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cuisine Type</label>
                                    <select name="menu_attributes[cuisine_type]" 
                                            class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Cuisine</option>
                                        <option value="sri_lankan" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'sri_lankan') ? 'selected' : '' }}>Sri Lankan</option>
                                        <option value="chinese" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'chinese') ? 'selected' : '' }}>Chinese</option>
                                        <option value="indian" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'indian') ? 'selected' : '' }}>Indian</option>
                                        <option value="italian" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'italian') ? 'selected' : '' }}>Italian</option>
                                        <option value="continental" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'continental') ? 'selected' : '' }}>Continental</option>
                                        <option value="seafood" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'seafood') ? 'selected' : '' }}>Seafood</option>
                                        <option value="vegetarian" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'vegetarian') ? 'selected' : '' }}>Vegetarian</option>
                                        <option value="vegan" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'vegan') ? 'selected' : '' }}>Vegan</option>
                                        <option value="dessert" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'dessert') ? 'selected' : '' }}>Dessert</option>
                                        <option value="beverage" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'beverage') ? 'selected' : '' }}>Beverage</option>
                                        <option value="other" {{ (old('menu_attributes.cuisine_type', $item->attributes['cuisine_type'] ?? '') == 'other') ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <!-- Spice Level -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Spice Level</label>
                                    <select name="menu_attributes[spice_level]" 
                                            class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Level</option>
                                        <option value="mild" {{ (old('menu_attributes.spice_level', $item->attributes['spice_level'] ?? '') == 'mild') ? 'selected' : '' }}>Mild</option>
                                        <option value="medium" {{ (old('menu_attributes.spice_level', $item->attributes['spice_level'] ?? '') == 'medium') ? 'selected' : '' }}>Medium</option>
                                        <option value="hot" {{ (old('menu_attributes.spice_level', $item->attributes['spice_level'] ?? '') == 'hot') ? 'selected' : '' }}>Hot</option>
                                        <option value="extra_hot" {{ (old('menu_attributes.spice_level', $item->attributes['spice_level'] ?? '') == 'extra_hot') ? 'selected' : '' }}>Extra Hot</option>
                                    </select>
                                </div>

                                <!-- Preparation Time -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Prep Time (minutes)</label>
                                    <input type="number" name="menu_attributes[prep_time_minutes]" min="1" max="120"
                                           value="{{ old('menu_attributes.prep_time_minutes', $item->attributes['prep_time_minutes'] ?? '') }}"
                                           class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="e.g., 15">
                                </div>

                                <!-- Serving Size -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Serving Size</label>
                                    <select name="menu_attributes[serving_size]" 
                                            class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Size</option>
                                        <option value="small" {{ (old('menu_attributes.serving_size', $item->attributes['serving_size'] ?? '') == 'small') ? 'selected' : '' }}>Small</option>
                                        <option value="regular" {{ (old('menu_attributes.serving_size', $item->attributes['serving_size'] ?? '') == 'regular') ? 'selected' : '' }}>Regular</option>
                                        <option value="large" {{ (old('menu_attributes.serving_size', $item->attributes['serving_size'] ?? '') == 'large') ? 'selected' : '' }}>Large</option>
                                        <option value="family" {{ (old('menu_attributes.serving_size', $item->attributes['serving_size'] ?? '') == 'family') ? 'selected' : '' }}>Family Size</option>
                                    </select>
                                </div>

                                <!-- Dietary Restrictions -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dietary</label>
                                    <select name="menu_attributes[dietary_type]" 
                                            class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Type</option>
                                        <option value="vegetarian" {{ (old('menu_attributes.dietary_type', $item->attributes['dietary_type'] ?? '') == 'vegetarian') ? 'selected' : '' }}>Vegetarian</option>
                                        <option value="vegan" {{ (old('menu_attributes.dietary_type', $item->attributes['dietary_type'] ?? '') == 'vegan') ? 'selected' : '' }}>Vegan</option>
                                        <option value="gluten_free" {{ (old('menu_attributes.dietary_type', $item->attributes['dietary_type'] ?? '') == 'gluten_free') ? 'selected' : '' }}>Gluten Free</option>
                                        <option value="dairy_free" {{ (old('menu_attributes.dietary_type', $item->attributes['dietary_type'] ?? '') == 'dairy_free') ? 'selected' : '' }}>Dairy Free</option>
                                        <option value="halal" {{ (old('menu_attributes.dietary_type', $item->attributes['dietary_type'] ?? '') == 'halal') ? 'selected' : '' }}>Halal</option>
                                        <option value="kosher" {{ (old('menu_attributes.dietary_type', $item->attributes['dietary_type'] ?? '') == 'kosher') ? 'selected' : '' }}>Kosher</option>
                                        <option value="none" {{ (old('menu_attributes.dietary_type', $item->attributes['dietary_type'] ?? '') == 'none') ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>

                                <!-- Availability -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Available During</label>
                                    <select name="menu_attributes[availability]" 
                                            class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Availability</option>
                                        <option value="all_day" {{ (old('menu_attributes.availability', $item->attributes['availability'] ?? '') == 'all_day') ? 'selected' : '' }}>All Day</option>
                                        <option value="breakfast" {{ (old('menu_attributes.availability', $item->attributes['availability'] ?? '') == 'breakfast') ? 'selected' : '' }}>Breakfast Only</option>
                                        <option value="lunch" {{ (old('menu_attributes.availability', $item->attributes['availability'] ?? '') == 'lunch') ? 'selected' : '' }}>Lunch Only</option>
                                        <option value="dinner" {{ (old('menu_attributes.availability', $item->attributes['availability'] ?? '') == 'dinner') ? 'selected' : '' }}>Dinner Only</option>
                                        <option value="lunch_dinner" {{ (old('menu_attributes.availability', $item->attributes['availability'] ?? '') == 'lunch_dinner') ? 'selected' : '' }}>Lunch & Dinner</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <!-- Main Ingredients -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Main Ingredients</label>
                                    <textarea name="menu_attributes[main_ingredients]" rows="2"
                                              class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                              placeholder="e.g., Rice, Chicken, Vegetables">{{ old('menu_attributes.main_ingredients', $item->attributes['main_ingredients'] ?? '') }}</textarea>
                                </div>

                                <!-- Allergen Information -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Allergen Info</label>
                                    <textarea name="menu_attributes[allergen_info]" rows="2"
                                              class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                              placeholder="e.g., Contains nuts, dairy">{{ old('menu_attributes.allergen_info', $item->attributes['allergen_info'] ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Chef's Recommendation & Popular Item -->
                            <div class="flex items-center space-x-6 mt-4">
                                <div class="flex items-center">
                                    <input type="hidden" name="menu_attributes[is_chefs_special]" value="0">
                                    <input type="checkbox" name="menu_attributes[is_chefs_special]" value="1" id="is_chefs_special"
                                           {{ old('menu_attributes.is_chefs_special', $item->attributes['is_chefs_special'] ?? false) ? 'checked' : '' }}
                                           class="menu-attribute-field h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_chefs_special" class="ml-2 block text-sm text-gray-700">Chef's Special</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="hidden" name="menu_attributes[is_popular]" value="0">
                                    <input type="checkbox" name="menu_attributes[is_popular]" value="1" id="is_popular"
                                           {{ old('menu_attributes.is_popular', $item->attributes['is_popular'] ?? false) ? 'checked' : '' }}
                                           class="menu-attribute-field h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_popular" class="ml-2 block text-sm text-gray-700">Popular Item</label>
                                </div>
                            </div>
                        </div>

                        <!-- Full Width Fields -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Detailed item description">{{ old('description', $item->description) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                            <textarea name="additional_notes" rows="2"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Any special notes about this item">{{ old('additional_notes', $item->additional_notes) }}</textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('admin.inventory.items.index') }}"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 border border-transparent rounded-lg text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuItemCheckbox = document.querySelector('.menu-item-checkbox');
    const menuAttributesSection = document.querySelector('.menu-attributes');

    // Initialize form state
    if (menuItemCheckbox && menuAttributesSection) {
        // Show/hide menu attributes based on current state
        if (menuItemCheckbox.checked) {
            menuAttributesSection.classList.remove('hidden');
            setMenuAttributesRequired(true);
        } else {
            menuAttributesSection.classList.add('hidden');
            setMenuAttributesRequired(false);
        }

        // Handle menu item checkbox change
        menuItemCheckbox.addEventListener('change', function() {
            if (this.checked) {
                menuAttributesSection.classList.remove('hidden');
                setMenuAttributesRequired(true);
            } else {
                menuAttributesSection.classList.add('hidden');
                setMenuAttributesRequired(false);
                // Clear menu attributes when unchecked
                clearMenuAttributes();
            }
        });
    }

    // Set menu attributes as required or optional
    function setMenuAttributesRequired(required) {
        if (!menuAttributesSection) return;

        // Core menu attributes that should be required
        const requiredMenuFields = [
            'cuisine_type', 'prep_time_minutes', 'serving_size'
        ];

        menuAttributesSection.querySelectorAll('.menu-attribute-field').forEach(field => {
            const fieldName = field.name?.match(/menu_attributes\[(\w+)\]/)?.[1];
            if (!fieldName) return;

            // Update required status for key menu attributes
            if (requiredMenuFields.includes(fieldName)) {
                if (required) {
                    field.setAttribute('required', 'required');
                    // Add visual indicator
                    const label = field.closest('div').querySelector('label');
                    if (label && !label.textContent.includes('*')) {
                        label.innerHTML = label.innerHTML + ' <span class="text-red-500">*</span>';
                    }
                } else {
                    field.removeAttribute('required');
                    // Remove visual indicator
                    const label = field.closest('div').querySelector('label');
                    if (label && label.textContent.includes('*')) {
                        label.innerHTML = label.innerHTML.replace(' <span class="text-red-500">*</span>', '');
                    }
                }
            }
        });
    }

    // Clear menu attributes
    function clearMenuAttributes() {
        if (!menuAttributesSection) return;

        // Clear all menu attribute fields
        menuAttributesSection.querySelectorAll('.menu-attribute-field').forEach(field => {
            if (field.type === 'checkbox') {
                field.checked = false;
            } else {
                field.value = '';
            }
        });
    }

    // Form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (menuItemCheckbox && menuItemCheckbox.checked) {
                const requiredFields = ['cuisine_type', 'prep_time_minutes', 'serving_size'];
                const errorMessages = [];

                requiredFields.forEach(fieldName => {
                    const field = menuAttributesSection?.querySelector(`[name="menu_attributes[${fieldName}]"]`);
                    if (!field || !field.value || field.value.trim() === '') {
                        const fieldLabel = {
                            'cuisine_type': 'Cuisine Type',
                            'prep_time_minutes': 'Preparation Time',
                            'serving_size': 'Serving Size'
                        };
                        errorMessages.push(`${fieldLabel[fieldName]} is required for menu items`);
                    }
                });

                if (errorMessages.length > 0) {
                    e.preventDefault();
                    alert('Please fix the following validation errors:\n\n' + errorMessages.join('\n'));
                    return;
                }
            }
        });
    }
});
</script>
@endpush
