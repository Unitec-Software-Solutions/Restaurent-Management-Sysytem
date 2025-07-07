@extends('layouts.admin')

@section('title', 'Create Standalone KOT Items')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Standalone KOT Items</h1>
            <p class="text-gray-600">Create standalone Kitchen Order Ticket (KOT) items without linking to Item Master</p>
        </div>
        <a href="{{ route('admin.menu-items.index') }}" 
           class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Menu Items
        </a>
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

    <form action="{{ route('admin.menu-items.standalone-kot.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Configuration Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">General Configuration</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="menu_category_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Menu Category <span class="text-red-500">*</span>
                    </label>
                    <select id="menu_category_id" name="menu_category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
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

                <div>
                    <label for="default_kitchen_station_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Default Kitchen Station
                    </label>
                    <select id="default_kitchen_station_id" name="default_kitchen_station_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">No Default Station</option>
                        @foreach($kitchenStations as $station)
                            <option value="{{ $station->id }}" {{ old('default_kitchen_station_id') == $station->id ? 'selected' : '' }}>
                                {{ $station->name }} ({{ $station->type }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- KOT Items Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">KOT Items</h3>
                <button type="button" id="add-item-btn" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Item
                </button>
            </div>

            <div id="items-container" class="space-y-4">
                <!-- Dynamic items will be added here -->
            </div>

            <div class="mt-4 text-sm text-gray-600">
                <i class="fas fa-info-circle mr-2"></i>
                Add multiple KOT items. Each item will be created as a standalone menu item that requires preparation.
            </div>
        </div>

        <!-- Submit Section -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('admin.menu-items.index') }}" 
               class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Create KOT Items
            </button>
        </div>
    </form>
</div>

<!-- Item Template (Hidden) -->
<template id="item-template">
    <div class="item-card border border-gray-200 rounded-lg p-4 space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="font-medium text-gray-900">KOT Item #<span class="item-number">1</span></h4>
            <button type="button" class="remove-item-btn text-red-500 hover:text-red-700">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Item Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="items[INDEX][name]" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Price (LKR) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="items[INDEX][price]" step="0.01" min="0" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="items[INDEX][description]" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Preparation Time (minutes)
                </label>
                <input type="number" name="items[INDEX][preparation_time]" min="1" max="240" value="15"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kitchen Station</label>
                <select name="items[INDEX][kitchen_station_id]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Use Default</option>
                    @foreach($kitchenStations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }} ({{ $station->type }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    const addItemBtn = document.getElementById('add-item-btn');
    const itemsContainer = document.getElementById('items-container');
    const itemTemplate = document.getElementById('item-template');

    // Add first item automatically
    addItem();

    addItemBtn.addEventListener('click', addItem);

    function addItem() {
        const template = itemTemplate.content.cloneNode(true);
        const itemCard = template.querySelector('.item-card');
        
        // Update item number
        template.querySelector('.item-number').textContent = itemIndex + 1;
        
        // Update input names with current index
        const inputs = template.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('INDEX', itemIndex);
            }
        });

        // Add remove functionality
        const removeBtn = template.querySelector('.remove-item-btn');
        removeBtn.addEventListener('click', function() {
            itemCard.remove();
            updateItemNumbers();
        });

        itemsContainer.appendChild(template);
        itemIndex++;
    }

    function updateItemNumbers() {
        const itemCards = itemsContainer.querySelectorAll('.item-card');
        itemCards.forEach((card, index) => {
            card.querySelector('.item-number').textContent = index + 1;
        });
    }
});
</script>
@endpush
