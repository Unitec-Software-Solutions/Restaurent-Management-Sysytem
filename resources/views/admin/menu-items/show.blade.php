@extends('layouts.admin')

@section('title', 'Menu Item Details')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $menuItem->name }}</h1>
            <p class="text-gray-600">Menu Item Details</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.menu-items.edit', $menuItem) }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="{{ route('admin.menu-items.index') }}" 
               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Details -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->name }}</p>
                    </div>
                    
                    @if($menuItem->unicode_name)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Unicode Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->unicode_name }}</p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Category</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->menuCategory->name ?? 'Uncategorized' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Type</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $menuItem->type == 1 ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ $menuItem->type == 1 ? 'Buy/Sell' : 'KOT' }}
                        </span>
                    </div>
                    
                    @if($menuItem->item_code)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Item Code</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">{{ $menuItem->item_code }}</p>
                    </div>
                    @endif
                    
                    @if($menuItem->itemMaster)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Linked to Item Master</label>
                        <p class="mt-1 text-sm text-indigo-600">{{ $menuItem->itemMaster->name }}</p>
                    </div>
                    @endif
                </div>

                @if($menuItem->description)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-500">Description</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $menuItem->description }}</p>
                </div>
                @endif
            </div>

            <!-- Pricing Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Regular Price</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($menuItem->price, 2) }}</p>
                    </div>
                    
                    @if($menuItem->cost_price)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Cost Price</label>
                        <p class="mt-1 text-sm text-gray-900">${{ number_format($menuItem->cost_price, 2) }}</p>
                    </div>
                    @endif
                    
                    @if($menuItem->promotion_price)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Promotion Price</label>
                        <p class="mt-1 text-lg font-semibold text-green-600">${{ number_format($menuItem->promotion_price, 2) }}</p>
                        @if($menuItem->promotion_start && $menuItem->promotion_end)
                        <p class="text-xs text-gray-500">
                            {{ $menuItem->promotion_start->format('M j') }} - {{ $menuItem->promotion_end->format('M j, Y') }}
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Nutritional & Dietary Information -->
            @if($menuItem->calories || $menuItem->allergens || $menuItem->ingredients || $menuItem->is_vegetarian || $menuItem->is_vegan || $menuItem->spice_level)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nutritional & Dietary Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($menuItem->calories)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Calories</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->calories }} cal</p>
                    </div>
                    @endif
                    
                    @if($menuItem->spice_level)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Spice Level</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $menuItem->spice_level == 'mild' ? 'bg-green-100 text-green-800' : 
                               ($menuItem->spice_level == 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                               ($menuItem->spice_level == 'hot' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                            {{ ucfirst($menuItem->spice_level) }}
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Dietary Preferences -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Dietary Preferences</label>
                    <div class="flex flex-wrap gap-2">
                        @if($menuItem->is_vegetarian)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-leaf mr-1"></i>Vegetarian
                            </span>
                        @endif
                        @if($menuItem->is_vegan)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-seedling mr-1"></i>Vegan
                            </span>
                        @endif
                        @if($menuItem->is_spicy)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-pepper-hot mr-1"></i>Spicy
                            </span>
                        @endif
                        @if($menuItem->contains_alcohol)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-wine-glass mr-1"></i>Contains Alcohol
                            </span>
                        @endif
                    </div>
                </div>

                @if($menuItem->allergens && count($menuItem->allergens) > 0)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Allergens</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($menuItem->allergens as $allergen)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ $allergen }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($menuItem->ingredients)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-500">Ingredients</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $menuItem->ingredients }}</p>
                </div>
                @endif
            </div>
            @endif

            <!-- Preparation Information -->
            @if($menuItem->preparation_time || $menuItem->station || $menuItem->kitchenStation || $menuItem->special_instructions)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preparation Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($menuItem->preparation_time)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Preparation Time</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->preparation_time }} minutes</p>
                    </div>
                    @endif
                    
                    @if($menuItem->station)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Station</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->station }}</p>
                    </div>
                    @endif
                    
                    @if($menuItem->kitchenStation)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Kitchen Station</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->kitchenStation->name }}</p>
                    </div>
                    @endif
                </div>

                <div class="mt-4">
                    <div class="flex items-center">
                        <input type="checkbox" disabled {{ $menuItem->requires_preparation ? 'checked' : '' }} 
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        <label class="ml-2 block text-sm text-gray-700">Requires Preparation</label>
                    </div>
                </div>

                @if($menuItem->special_instructions)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-500">Special Instructions</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $menuItem->special_instructions }}</p>
                </div>
                @endif
            </div>
            @endif

            <!-- Recipe Information -->
            @if($menuItem->recipes && $menuItem->recipes->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recipe</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingredient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste %</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($menuItem->recipes as $recipe)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $recipe->ingredientItem->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $recipe->quantity_needed }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $recipe->unit }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $recipe->waste_percentage }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Image -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Image</h3>
                @if($menuItem->image_path)
                    <img src="{{ asset('storage/' . $menuItem->image_path) }}" 
                         alt="{{ $menuItem->name }}" 
                         class="w-full h-48 object-cover rounded-lg">
                @else
                    <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-image text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500 text-center mt-2">No image uploaded</p>
                @endif
            </div>

            <!-- Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Active</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $menuItem->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $menuItem->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Available</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $menuItem->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $menuItem->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Featured</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $menuItem->is_featured ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $menuItem->is_featured ? 'Featured' : 'Regular' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Organization & Branch -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization</h3>
                
                <div class="space-y-3">
                    @if($menuItem->organization)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Organization</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->organization->name }}</p>
                    </div>
                    @endif
                    
                    @if($menuItem->branch)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Branch</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $menuItem->branch->name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Display Order -->
            @if($menuItem->display_order)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Display Order</h3>
                <p class="text-2xl font-bold text-indigo-600">{{ $menuItem->display_order }}</p>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                
                <div class="space-y-3">
                    <button onclick="if(confirm('Are you sure you want to delete this menu item?')) { document.getElementById('delete-form').submit(); }" 
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete Menu Item
                    </button>
                </div>
                
                <form id="delete-form" action="{{ route('admin.menu-items.destroy', $menuItem) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
