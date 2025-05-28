@extends('layouts.admin')
@section('header-title', 'Items Management - Add Items')
@section('content')
<div class="p-4 rounded-lg">
    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Add New Items</h2>
                <p class="text-sm text-gray-500">Create multiple inventory items at once</p>
            </div>
            
            <a href="{{ route('admin.inventory.items.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Items
            </a>
        </div>

        <!-- Form Container -->
        <form id="items-form" action="{{ route('admin.inventory.items.store') }}" method="POST" class="p-6">
            @csrf

            <div id="items-container">
                <!-- Initial item form -->
                @include('admin.inventory.items.partials.item-form', ['index' => 0])
            </div>

            <div class="mt-6">
                <button type="button" id="add-item" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Another Item
                </button>
            </div>

            <div class="flex justify-end space-x-3 mt-8">
                <button type="reset" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <i class="fas fa-undo mr-2"></i> Reset
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-save mr-2"></i> Save All Items
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;
    const itemsContainer = document.getElementById('items-container');
    const addItemBtn = document.getElementById('add-item');

    // Add new item form
    addItemBtn.addEventListener('click', async function() {
        try {
            const response = await fetch(`/admin/inventory/items/create-template/${itemCount}`);
            
            if (!response.ok) {
                throw new Error('Failed to fetch item form template');
            }
            
            const html = await response.text();
            itemsContainer.insertAdjacentHTML('beforeend', html);
            
            // Enable remove buttons if more than one item exists
            const removeButtons = document.querySelectorAll('.remove-item');
            if (removeButtons.length > 1) {
                removeButtons.forEach(btn => btn.classList.remove('hidden'));
            }
            
            itemCount++;
        } catch (error) {
            console.error('Error adding item form:', error);
            alert('Failed to add new item form. Please try again.');
        }
    });

    // Remove item form
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.preventDefault();
            const itemSection = e.target.closest('.item-section');
            if (itemSection) {
                itemSection.remove();
                
                // Hide remove button if only one item remains
                const removeButtons = document.querySelectorAll('.remove-item');
                if (removeButtons.length <= 1) {
                    removeButtons.forEach(btn => btn.classList.add('hidden'));
                }
            }
        }
    });

    // Handle category-specific attributes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-category')) {
            const index = e.target.dataset.index;
            const categoryId = e.target.value;
            const attrContainer = document.querySelector(`.category-attributes[data-index="${index}"]`);
            const attrField = document.querySelector(`input[name="items[${index}][attributes]"]`);
            
            if (!attrContainer) return;
            
            // Clear existing attributes
            attrContainer.innerHTML = '';
            if (attrField) attrField.value = '';
            
            // Add specific attributes based on category
            if (categoryId == 1) { // Food category
                attrContainer.innerHTML = `
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-lg font-semibold mb-3 text-gray-700">Food Attributes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ingredients</label>
                                <input type="text" data-attr="ingredients" 
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter ingredients">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Portion Size</label>
                                <input type="text" data-attr="portion_size" 
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter portion size">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prep Time (mins)</label>
                                <input type="number" data-attr="prep_time" 
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter prep time">
                            </div>
                        </div>
                    </div>`;
            } else if (categoryId == 4) { // Equipment category
                attrContainer.innerHTML = `
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-lg font-semibold mb-3 text-gray-700">Equipment Attributes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <input type="text" data-attr="brand" 
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter brand">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                                <input type="text" data-attr="model" 
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter model">
                            </div>
                        </div>
                    </div>`;
            }
            
            // Set up event listeners for new attribute fields
            const attributeFields = attrContainer.querySelectorAll('.attribute-field');
            attributeFields.forEach(field => {
                field.addEventListener('input', function() {
                    updateAttributesField(index);
                });
            });
        }
    });

    // Update hidden attributes field
    function updateAttributesField(index) {
        const attrContainer = document.querySelector(`.category-attributes[data-index="${index}"]`);
        const attrField = document.querySelector(`input[name="items[${index}][attributes]"]`);
        
        if (!attrContainer || !attrField) return;
        
        const inputs = attrContainer.querySelectorAll('.attribute-field');
        const attributes = {};
        
        inputs.forEach(input => {
            const key = input.dataset.attr;
            attributes[key] = input.value;
        });
        
        attrField.value = JSON.stringify(attributes);
    }

    // Form submission handling
    document.getElementById('items-form').addEventListener('submit', function(e) {
        // Validate at least one item exists
        const itemSections = document.querySelectorAll('.item-section');
        if (itemSections.length === 0) {
            e.preventDefault();
            alert('Please add at least one item');
            return;
        }
        
        // Update all attributes fields before submission
        document.querySelectorAll('.item-category').forEach(select => {
            const index = select.dataset.index;
            updateAttributesField(index);
        });
    });
});
</script>
@endsection