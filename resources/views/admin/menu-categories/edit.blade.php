@extends('layouts.admin')

@section('title', 'Edit ' . $menuCategory->name)

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Menu Category</h1>
                <p class="text-gray-600">Update category information</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.menu-categories.show', $menuCategory) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Category
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm">
        <form method="POST" action="{{ route('admin.menu-categories.update', $menuCategory) }}" class="p-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Category Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $menuCategory->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-300 @enderror"
                           placeholder="e.g., Appetizers, Main Courses, Desserts">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                        Display Order
                    </label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $menuCategory->sort_order) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-300 @enderror"
                           placeholder="1">
                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first in the menu</p>
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Assignment (Read-only) -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Current Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Organization</label>
                        <p class="text-sm text-gray-900">{{ $menuCategory->organization->name ?? 'Not Assigned' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Branch</label>
                        <p class="text-sm text-gray-900">{{ $menuCategory->branch->name ?? 'Not Assigned' }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Branch and organization cannot be changed after creation. Create a new category if needed.
                </p>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-300 @enderror"
                          placeholder="Brief description of this category...">{{ old('description', $menuCategory->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image URL -->
            <div class="mb-6">
                <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">
                    Category Image URL
                </label>
                <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $menuCategory->image_url) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('image_url') border-red-300 @enderror"
                       placeholder="https://example.com/image.jpg">
                <p class="mt-1 text-xs text-gray-500">Optional: URL to an image representing this category</p>
                @error('image_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <!-- Current Image Preview -->
                @if($menuCategory->image_url)
                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-500 mb-2">Current Image</label>
                        <div class="w-32 h-24 bg-cover bg-center rounded-lg border border-gray-200" 
                             style="background-image: url('{{ $menuCategory->image_url }}')"></div>
                    </div>
                @endif
            </div>

            <!-- Status Options -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-6">
                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $menuCategory->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Active Category
                        </label>
                    </div>

                    <!-- Is Featured -->
                    <div class="flex items-center">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                               {{ old('is_featured', $menuCategory->is_featured) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_featured" class="ml-2 text-sm text-gray-700">
                            Featured Category
                        </label>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Featured categories may be highlighted in menus</p>
            </div>

            <!-- Menu Items Information -->
            @if($menuCategory->menuItems->count() > 0)
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-400 mt-0.5 mr-3"></i>
                        <div class="text-sm">
                            <p class="text-blue-800 font-medium">This category contains {{ $menuCategory->menuItems->count() }} menu item(s)</p>
                            <p class="text-blue-700 mt-1">
                                Deactivating this category will not affect the menu items, but they may not be displayed in categorized views.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Add Menu Items Option if No Items Exist -->
            @if($menuCategory->menuItems->count() == 0)
                <div class="bg-yellow-50 rounded-lg p-6 mb-6 flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-utensils text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No menu items in this category</h3>
                    <p class="text-gray-500 mb-4">Add menu items to organize your offerings</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center mb-2">
                        <a href="{{ route('admin.menu-items.create-kot') }}?organization_id={{ $menuCategory->organization_id }}&branch_id={{ $menuCategory->branch_id }}&category_id={{ $menuCategory->id }}"
                           class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                            <i class="fas fa-fire mr-2"></i>Create KOT Items
                        </a>
                        <button onclick="openCreateFromItemMasterModal()"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-link mr-2"></i>From Item Master
                        </button>
                    </div>
                </div>
            @endif

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.menu-categories.show', $menuCategory) }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Update Category
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    @if($menuCategory->menuItems->count() == 0)
        <div class="bg-white rounded-lg shadow-sm mt-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-red-600">Danger Zone</h2>
            </div>
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Delete Category</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Permanently delete this category. This action cannot be undone.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.menu-categories.destroy', $menuCategory) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')"
                          class="ml-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-trash mr-2"></i> Delete Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
