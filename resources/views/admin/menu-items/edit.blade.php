@extends('layouts.admin')

@section('title', 'Edit Menu Item')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Menu Item</h1>
            <p class="text-gray-600">Update menu item information</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.menu-items.show', $menuItem) }}" 
               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                <i class="fas fa-eye mr-2"></i>View Details
            </a>
            <a href="{{ route('admin.menu-items.index') }}" 
               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.menu-items.update', $menuItem) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

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
                           value="{{ old('name', $menuItem->name) }}" 
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
                           value="{{ old('unicode_name', $menuItem->unicode_name) }}"
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
                            <option value="{{ $category->id }}" {{ old('menu_category_id', $menuItem->menu_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('menu_category_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type (if not linked to item master) -->
                @if(!$menuItem->item_master_id)
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('type') border-red-500 @enderror">
                        @foreach(App\Models\MenuItem::getTypes() as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $menuItem->type) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-500">
                        Linked to Item Master ({{ $menuItem->itemMaster->name }})
                    </div>
                    <input type="hidden" name="type" value="{{ $menuItem->type }}">
                </div>
                @endif
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $menuItem->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Pricing Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" id="price" name="price" step="0.01" min="0"
                               value="{{ old('price', $menuItem->price) }}" 
                               required
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('price') border-red-500 @enderror">
                    </div>
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cost Price -->
                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                               value="{{ old('cost_price', $menuItem->cost_price) }}"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('cost_price') border-red-500 @enderror">
                    </div>
                    @error('cost_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Promotion Price -->
                <div>
                    <label for="promotion_price" class="block text-sm font-medium text-gray-700 mb-1">Promotion Price</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" id="promotion_price" name="promotion_price" step="0.01" min="0"
                               value="{{ old('promotion_price', $menuItem->promotion_price) }}"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('promotion_price') border-red-500 @enderror">
                    </div>
                    @error('promotion_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Promotion Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="promotion_start" class="block text-sm font-medium text-gray-700 mb-1">Promotion Start</label>
                    <input type="datetime-local" id="promotion_start" name="promotion_start"
                           value="{{ old('promotion_start', $menuItem->promotion_start?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('promotion_start') border-red-500 @enderror">
                    @error('promotion_start')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promotion_end" class="block text-sm font-medium text-gray-700 mb-1">Promotion End</label>
                    <input type="datetime-local" id="promotion_end" name="promotion_end"
                           value="{{ old('promotion_end', $menuItem->promotion_end?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('promotion_end') border-red-500 @enderror">
                    @error('promotion_end')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Image Upload -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Image</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Current Image -->
                @if($menuItem->image_path)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                    <img src="{{ asset('storage/' . $menuItem->image_path) }}" 
                         alt="{{ $menuItem->name }}" 
                         class="w-full h-48 object-cover rounded-lg border">
                </div>
                @endif

                <!-- New Image -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $menuItem->image_path ? 'Replace Image' : 'Upload Image' }}
                    </label>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('image') border-red-500 @enderror">
                    @error('image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</p>
                </div>
            </div>
        </div>

        <!-- Preparation & Kitchen Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Preparation & Kitchen Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Preparation Time -->
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">Preparation Time (minutes)</label>
                    <input type="number" id="preparation_time" name="preparation_time" min="0"
                           value="{{ old('preparation_time', $menuItem->preparation_time) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('preparation_time') border-red-500 @enderror">
                    @error('preparation_time')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Station -->
                <div>
                    <label for="station" class="block text-sm font-medium text-gray-700 mb-1">Station</label>
                    <input type="text" id="station" name="station"
                           value="{{ old('station', $menuItem->station) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('station') border-red-500 @enderror">
                    @error('station')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kitchen Station -->
                <div>
                    <label for="kitchen_station_id" class="block text-sm font-medium text-gray-700 mb-1">Kitchen Station</label>
                    <select id="kitchen_station_id" name="kitchen_station_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('kitchen_station_id') border-red-500 @enderror">
                        <option value="">Select kitchen station</option>
                        @foreach($kitchenStations as $station)
                            <option value="{{ $station->id }}" {{ old('kitchen_station_id', $menuItem->kitchen_station_id) == $station->id ? 'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('kitchen_station_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Requires Preparation -->
            <div class="mt-6">
                <div class="flex items-center">
                    <input type="checkbox" id="requires_preparation" name="requires_preparation" value="1"
                           {{ old('requires_preparation', $menuItem->requires_preparation) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="requires_preparation" class="ml-2 block text-sm text-gray-700">Requires Preparation</label>
                </div>
            </div>
        </div>

        <!-- Nutritional & Dietary Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Nutritional & Dietary Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Calories -->
                <div>
                    <label for="calories" class="block text-sm font-medium text-gray-700 mb-1">Calories</label>
                    <input type="number" id="calories" name="calories" min="0"
                           value="{{ old('calories', $menuItem->calories) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('calories') border-red-500 @enderror">
                    @error('calories')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Spice Level -->
                <div>
                    <label for="spice_level" class="block text-sm font-medium text-gray-700 mb-1">Spice Level</label>
                    <select id="spice_level" name="spice_level"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('spice_level') border-red-500 @enderror">
                        <option value="">Select spice level</option>
                        @foreach(App\Models\MenuItem::getSpiceLevels() as $value => $label)
                            <option value="{{ $value }}" {{ old('spice_level', $menuItem->spice_level) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('spice_level')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Display Order -->
                <div>
                    <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" id="display_order" name="display_order" min="0"
                           value="{{ old('display_order', $menuItem->display_order) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('display_order') border-red-500 @enderror">
                    @error('display_order')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dietary Preferences -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Dietary Preferences</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_vegetarian" name="is_vegetarian" value="1"
                               {{ old('is_vegetarian', $menuItem->is_vegetarian) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_vegetarian" class="ml-2 block text-sm text-gray-700">Vegetarian</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_vegan" name="is_vegan" value="1"
                               {{ old('is_vegan', $menuItem->is_vegan) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_vegan" class="ml-2 block text-sm text-gray-700">Vegan</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_spicy" name="is_spicy" value="1"
                               {{ old('is_spicy', $menuItem->is_spicy) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_spicy" class="ml-2 block text-sm text-gray-700">Spicy</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="contains_alcohol" name="contains_alcohol" value="1"
                               {{ old('contains_alcohol', $menuItem->contains_alcohol) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="contains_alcohol" class="ml-2 block text-sm text-gray-700">Contains Alcohol</label>
                    </div>
                </div>
            </div>

            <!-- Allergens -->
            <div class="mt-6">
                <label for="allergens_input" class="block text-sm font-medium text-gray-700 mb-1">Allergens</label>
                <input type="text" id="allergens_input" placeholder="Enter allergens separated by commas"
                       value="{{ old('allergens_input', is_array($menuItem->allergens) ? implode(', ', $menuItem->allergens) : '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Separate multiple allergens with commas (e.g., nuts, dairy, gluten)</p>
                <input type="hidden" id="allergens" name="allergens" value="{{ old('allergens', json_encode($menuItem->allergens ?? [])) }}">
            </div>

            <!-- Ingredients -->
            <div class="mt-6">
                <label for="ingredients" class="block text-sm font-medium text-gray-700 mb-1">Ingredients</label>
                <textarea id="ingredients" name="ingredients" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('ingredients') border-red-500 @enderror">{{ old('ingredients', $menuItem->ingredients) }}</textarea>
                @error('ingredients')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Special Instructions & Notes -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
            
            <!-- Special Instructions -->
            <div class="mb-6">
                <label for="special_instructions" class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                <textarea id="special_instructions" name="special_instructions" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('special_instructions') border-red-500 @enderror">{{ old('special_instructions', $menuItem->special_instructions) }}</textarea>
                @error('special_instructions')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes', $menuItem->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Status & Availability -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status & Availability</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center">
                    <input type="checkbox" id="is_available" name="is_available" value="1"
                           {{ old('is_available', $menuItem->is_available) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="is_available" class="ml-2 block text-sm text-gray-700">Available for Orders</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                           {{ old('is_featured', $menuItem->is_featured) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="is_featured" class="ml-2 block text-sm text-gray-700">Featured Item</label>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.menu-items.show', $menuItem) }}" 
               class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Update Menu Item
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle allergens input
    const allergensInput = document.getElementById('allergens_input');
    const allergensHidden = document.getElementById('allergens');
    
    if (allergensInput && allergensHidden) {
        allergensInput.addEventListener('input', function() {
            const allergens = this.value.split(',').map(item => item.trim()).filter(item => item);
            allergensHidden.value = JSON.stringify(allergens);
        });
    }
});
</script>
@endpush
@endsection
