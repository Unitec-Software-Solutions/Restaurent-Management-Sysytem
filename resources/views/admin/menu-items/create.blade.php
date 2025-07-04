@extends('layouts.admin')

@section('title', 'Create Menu Item')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Menu Item</h1>
            <p class="text-gray-600">Add a new item to your menu</p>
        </div>
        <a href="{{ route('admin.menu-items.index') }}" 
           class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Menu Items
        </a>
    </div>

    <form method="POST" action="{{ route('admin.menu-items.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" 
                           value="{{ old('name', $itemMaster->name ?? '') }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unicode Name -->
                <div>
                    <label for="unicode_name" class="block text-sm font-medium text-gray-700 mb-1">Unicode Name</label>
                    <input type="text" id="unicode_name" name="unicode_name" 
                           value="{{ old('unicode_name', $itemMaster->unicode_name ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('unicode_name') border-red-500 @enderror">
                    @error('unicode_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="menu_category_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Category <span class="text-gray-400">(Optional)</span>
                    </label>
                    <select id="menu_category_id" name="menu_category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('menu_category_id') border-red-500 @enderror">
                        <option value="">No Category (Uncategorized)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('menu_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('menu_category_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('type') border-red-500 @enderror">
                        @foreach(App\Models\MenuItem::getTypes() as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $itemMaster ? 1 : 2) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $itemMaster->description ?? '') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if($itemMaster)
                <input type="hidden" name="item_master_id" value="{{ $itemMaster->id }}">
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        This menu item will be linked to "{{ $itemMaster->name }}" from Item Master.
                    </p>
                </div>
            @endif
        </div>

        <!-- Pricing -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Selling Price (LKR) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="price" name="price" step="0.01" min="0"
                           value="{{ old('price', $itemMaster->selling_price ?? '') }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cost Price -->
                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">Cost Price (LKR)</label>
                    <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                           value="{{ old('cost_price', $itemMaster->buying_price ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('cost_price') border-red-500 @enderror">
                    @error('cost_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Display Order -->
                <div>
                    <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" id="display_order" name="display_order" min="0"
                           value="{{ old('display_order', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('display_order') border-red-500 @enderror">
                    @error('display_order')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Promotion Pricing -->
            <div class="mt-6">
                <h4 class="text-md font-medium text-gray-800 mb-3">Promotion Pricing (Optional)</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="promotion_price" class="block text-sm font-medium text-gray-700 mb-1">Promotion Price (LKR)</label>
                        <input type="number" id="promotion_price" name="promotion_price" step="0.01" min="0"
                               value="{{ old('promotion_price') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('promotion_price') border-red-500 @enderror">
                        @error('promotion_price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="promotion_start" class="block text-sm font-medium text-gray-700 mb-1">Promotion Start</label>
                        <input type="datetime-local" id="promotion_start" name="promotion_start"
                               value="{{ old('promotion_start') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('promotion_start') border-red-500 @enderror">
                        @error('promotion_start')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="promotion_end" class="block text-sm font-medium text-gray-700 mb-1">Promotion End</label>
                        <input type="datetime-local" id="promotion_end" name="promotion_end"
                               value="{{ old('promotion_end') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('promotion_end') border-red-500 @enderror">
                        @error('promotion_end')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Kitchen & Preparation -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Kitchen & Preparation</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Requires Preparation -->
                <div class="flex items-center">
                    <input type="checkbox" id="requires_preparation" name="requires_preparation" value="1"
                           {{ old('requires_preparation', !$itemMaster) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                    <label for="requires_preparation" class="text-sm font-medium text-gray-700">Requires Preparation</label>
                </div>

                <!-- Preparation Time -->
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">Preparation Time (minutes)</label>
                    <input type="number" id="preparation_time" name="preparation_time" min="0"
                           value="{{ old('preparation_time') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('preparation_time') border-red-500 @enderror">
                    @error('preparation_time')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Station -->
                <div>
                    <label for="station" class="block text-sm font-medium text-gray-700 mb-1">Station</label>
                    <input type="text" id="station" name="station"
                           value="{{ old('station') }}"
                           placeholder="e.g., grill, fryer, salad"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('station') border-red-500 @enderror">
                    @error('station')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Kitchen Station -->
            @if($kitchenStations->count() > 0)
                <div class="mt-6">
                    <label for="kitchen_station_id" class="block text-sm font-medium text-gray-700 mb-1">Kitchen Station</label>
                    <select id="kitchen_station_id" name="kitchen_station_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('kitchen_station_id') border-red-500 @enderror">
                        <option value="">Select a kitchen station</option>
                        @foreach($kitchenStations as $station)
                            <option value="{{ $station->id }}" {{ old('kitchen_station_id') == $station->id ? 'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('kitchen_station_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>

        <!-- Dietary & Status Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dietary & Status Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status Checkboxes -->
                <div class="space-y-3">
                    <h4 class="text-md font-medium text-gray-800">Status</h4>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_available" name="is_available" value="1"
                               {{ old('is_available', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <label for="is_available" class="text-sm font-medium text-gray-700">Available</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1"
                               {{ old('is_featured') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <label for="is_featured" class="text-sm font-medium text-gray-700">Featured Item</label>
                    </div>
                </div>

                <!-- Dietary Checkboxes -->
                <div class="space-y-3">
                    <h4 class="text-md font-medium text-gray-800">Dietary Information</h4>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_vegetarian" name="is_vegetarian" value="1"
                               {{ old('is_vegetarian') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <label for="is_vegetarian" class="text-sm font-medium text-gray-700">Vegetarian</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_vegan" name="is_vegan" value="1"
                               {{ old('is_vegan') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <label for="is_vegan" class="text-sm font-medium text-gray-700">Vegan</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_spicy" name="is_spicy" value="1"
                               {{ old('is_spicy') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <label for="is_spicy" class="text-sm font-medium text-gray-700">Spicy</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="contains_alcohol" name="contains_alcohol" value="1"
                               {{ old('contains_alcohol') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <label for="contains_alcohol" class="text-sm font-medium text-gray-700">Contains Alcohol</label>
                    </div>
                </div>
            </div>

            <!-- Spice Level -->
            <div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="spice_level" class="block text-sm font-medium text-gray-700 mb-1">Spice Level</label>
                        <select id="spice_level" name="spice_level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('spice_level') border-red-500 @enderror">
                            @foreach(App\Models\MenuItem::getSpiceLevels() as $value => $label)
                                <option value="{{ $value }}" {{ old('spice_level', 'mild') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('spice_level')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="calories" class="block text-sm font-medium text-gray-700 mb-1">Calories</label>
                        <input type="number" id="calories" name="calories" min="0"
                               value="{{ old('calories') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('calories') border-red-500 @enderror">
                        @error('calories')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
            
            <!-- Image Upload -->
            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Menu Item Image</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('image') border-red-500 @enderror">
                @error('image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-1">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</p>
            </div>

            <!-- Ingredients -->
            <div class="mb-6">
                <label for="ingredients" class="block text-sm font-medium text-gray-700 mb-1">Ingredients</label>
                <textarea id="ingredients" name="ingredients" rows="3"
                          placeholder="List the main ingredients..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('ingredients') border-red-500 @enderror">{{ old('ingredients') }}</textarea>
                @error('ingredients')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Special Instructions -->
            <div class="mb-6">
                <label for="special_instructions" class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                <textarea id="special_instructions" name="special_instructions" rows="3"
                          placeholder="Any special preparation or serving instructions..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('special_instructions') border-red-500 @enderror">{{ old('special_instructions') }}</textarea>
                @error('special_instructions')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="Internal notes about this menu item..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.menu-items.index') }}" 
               class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Create Menu Item
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Toggle spice level field based on is_spicy checkbox
document.getElementById('is_spicy').addEventListener('change', function() {
    const spiceLevelField = document.getElementById('spice_level');
    if (this.checked) {
        spiceLevelField.disabled = false;
        spiceLevelField.required = true;
    } else {
        spiceLevelField.disabled = true;
        spiceLevelField.required = false;
        spiceLevelField.value = 'mild';
    }
});

// Auto-check vegetarian when vegan is selected
document.getElementById('is_vegan').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('is_vegetarian').checked = true;
    }
});

// Prevent unchecking vegetarian when vegan is checked
document.getElementById('is_vegetarian').addEventListener('change', function() {
    if (!this.checked && document.getElementById('is_vegan').checked) {
        this.checked = true;
        alert('Vegan items must also be vegetarian.');
    }
});

// Validate promotion dates
document.getElementById('promotion_start').addEventListener('change', validatePromotionDates);
document.getElementById('promotion_end').addEventListener('change', validatePromotionDates);

function validatePromotionDates() {
    const startDate = document.getElementById('promotion_start').value;
    const endDate = document.getElementById('promotion_end').value;
    
    if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
        alert('Promotion end date must be after start date.');
        document.getElementById('promotion_end').value = '';
    }
}

// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create or update image preview
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'image-preview';
                preview.className = 'mt-2 w-32 h-32 object-cover rounded-lg border border-gray-300';
                document.getElementById('image').parentNode.appendChild(preview);
            }
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
