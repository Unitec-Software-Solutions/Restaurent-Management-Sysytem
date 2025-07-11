@extends('layouts.admin')

@section('title', 'Create KOT Menu Items')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-orange-700 mb-2">
                <i class="fas fa-fire text-orange-600 mr-2"></i>Create KOT Menu Items
            </h1>
            <p class="text-gray-600">Manually define kitchen order ticket (KOT) items for kitchen preparation. These items are not imported from inventory, but created specifically for kitchen production.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.menu-items.enhanced.index') }}" 
               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Menu Items
            </a>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-orange-600 mr-3 mt-0.5"></i>
            <div>
                <h3 class="font-medium text-orange-800 mb-1">KOT Item Creation Guidelines</h3>
                <ul class="text-sm text-orange-700 space-y-1">
                    <li>• KOT items are for kitchen preparation and are not directly linked to inventory stock.</li>
                    <li>• Define each KOT item manually with required preparation details.</li>
                    <li>• Use this form to add new kitchen recipes, dishes, or production items.</li>
                </ul>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-medium">Please correct the following errors:</h3>
                    <ul class="text-red-700 text-sm mt-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.menu-items.store-kot') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @php
            $defaultMenuCategoryId = request('category_id');
            $defaultOrgId = request('organization_id');
            $defaultBranchId = request('branch_id');
        @endphp
        @if($defaultOrgId)
            <input type="hidden" name="organization_id" value="{{ $defaultOrgId }}">
        @endif
        @if($defaultBranchId)
            <input type="hidden" name="branch_id" value="{{ $defaultBranchId }}">
        @endif
        @if($defaultMenuCategoryId)
            <input type="hidden" name="menu_category_id" value="{{ $defaultMenuCategoryId }}">
            <div class="bg-gray-50 border border-gray-200 rounded px-3 py-2 text-gray-700 text-sm mb-2">
                <span class="font-semibold">Category:</span> {{ optional($menuCategories->firstWhere('id', $defaultMenuCategoryId))->name ?? 'Selected Category' }}
                @if($defaultOrgId)
                    <span class="ml-4 font-semibold">Organization:</span> {{ optional($organizations->firstWhere('id', $defaultOrgId))->name ?? $defaultOrgId }}
                @endif
                @if($defaultBranchId)
                    <span class="ml-4 font-semibold">Branch:</span> {{ optional($branches->firstWhere('id', $defaultBranchId))->name ?? $defaultBranchId }}
                @endif
            </div>
        @else
            <div>
                <label for="menu_category_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Menu Category <span class="text-red-500">*</span>
                </label>
                <select id="menu_category_id" name="menu_category_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Select Category</option>
                    @foreach($menuCategories as $category)
                        <option value="{{ $category->id }}" {{ old('menu_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('menu_category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        @if(auth('admin')->user()->is_super_admin && !$defaultOrgId)
            <div>
                <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Organization <span class="text-red-500">*</span>
                </label>
                <select id="organization_id" name="organization_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('organization_id') border-red-500 @enderror">
                    <option value="">Select Organization</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ old('organization_id', $defaultOrgId) == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                    @endforeach
                </select>
                @error('organization_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        @if(auth('admin')->user()->is_super_admin && !$defaultBranchId)
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Branch (Optional)
                </label>
                <select id="branch_id" name="branch_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('branch_id') border-red-500 @enderror">
                    <option value="">Organization-wide</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $defaultBranchId) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        <!-- KOT Item Details Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">KOT Item Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Item Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">
                        Preparation Time (minutes)
                    </label>
                    <input type="number" id="preparation_time" name="preparation_time" 
                           value="{{ old('preparation_time', 15) }}" min="1" max="240"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <p class="text-sm text-gray-500 mt-1">Time required to prepare this item</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label for="kitchen_station_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Kitchen Station
                    </label>
                    <select id="kitchen_station_id" name="kitchen_station_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Auto-assign</option>
                        @foreach($kitchenStations as $station)
                            <option value="{{ $station->id }}" {{ old('kitchen_station_id') == $station->id ? 'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <!-- Options Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Options</h3>
            <label class="flex items-center">
                <input type="checkbox" id="is_available" name="is_available" value="1" 
                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                       {{ old('is_available', true) ? 'checked' : '' }}>
                <span class="ml-2 text-sm text-gray-900">Make item available immediately</span>
            </label>
        </div>
        <!-- Pricing Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Price (LKR) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="price" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">
                        Cost Price (LKR)
                    </label>
                    <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price') }}" min="0" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('cost_price') border-red-500 @enderror">
                    @error('cost_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="promotion_price" class="block text-sm font-medium text-gray-700 mb-1">
                        Promotion Price (LKR)
                    </label>
                    <input type="number" id="promotion_price" name="promotion_price" value="{{ old('promotion_price') }}" min="0" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('promotion_price') border-red-500 @enderror">
                    @error('promotion_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="promotion_start" class="block text-sm font-medium text-gray-700 mb-1">Promotion Start</label>
                    <input type="datetime-local" id="promotion_start" name="promotion_start" value="{{ old('promotion_start') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('promotion_start') border-red-500 @enderror">
                    @error('promotion_start')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="promotion_end" class="block text-sm font-medium text-gray-700 mb-1">Promotion End</label>
                    <input type="datetime-local" id="promotion_end" name="promotion_end" value="{{ old('promotion_end') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('promotion_end') border-red-500 @enderror">
                    @error('promotion_end')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        <!-- Image Upload Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Image</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                        Upload Image
                    </label>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('image') border-red-500 @enderror">
                    @error('image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</p>
                </div>
            </div>
        </div>
        <!-- Additional Options Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Options</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="spice_level" class="block text-sm font-medium text-gray-700 mb-1">Spice Level</label>
                    <select id="spice_level" name="spice_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="mild" {{ old('spice_level', 'mild') == 'mild' ? 'selected' : '' }}>Mild</option>
                        <option value="medium" {{ old('spice_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hot" {{ old('spice_level') == 'hot' ? 'selected' : '' }}>Hot</option>
                    </select>
                </div>
                <div class="flex items-center mt-6">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500" {{ old('is_featured') ? 'checked' : '' }}>
                    <label for="is_featured" class="ml-2 text-sm text-gray-900">Featured Item</label>
                </div>
                <div class="flex flex-col gap-2 mt-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="is_vegetarian" name="is_vegetarian" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500" {{ old('is_vegetarian') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-900">Vegetarian</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="is_vegan" name="is_vegan" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500" {{ old('is_vegan') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-900">Vegan</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="is_spicy" name="is_spicy" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500" {{ old('is_spicy') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-900">Spicy</span>
                    </label>
                </div>
            </div>
        </div>
        <!-- Actions -->
        <div class="flex items-center justify-between">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-info-circle mr-2"></i>
                This item will be created as a KOT menu item with preparation required.
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                <i class="fas fa-fire mr-2"></i>Create KOT Item
            </button>
        </div>
    </form>
</div>
@endsection
