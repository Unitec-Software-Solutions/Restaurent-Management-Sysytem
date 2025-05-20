@extends('layouts.main')

@section('content')
<div class="">
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Items</h2>
            <a href="{{ route('admin.inventory.items.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i> Back to Items
            </a>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-800 dark:border-red-600 dark:text-red-100 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <h4 class="font-bold">There were some errors with your submission:</h4>
            </div>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Form Container -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <form action="{{ route('admin.inventory.items.store') }}" method="POST" class="p-6">
                @csrf

                <div id="items-container">
                    @include('admin.inventory.items.partials.item-form', ['index' => 0])
                </div>

                <div class="mt-6">
                    <button type="button" id="add-item" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i> Add Another Item
                    </button>
                </div>

                <div class="flex justify-end space-x-4 mt-8">
                    <button type="reset" 
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-save mr-2"></i> Save Items
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let itemCount = 1;

    document.getElementById('add-item').addEventListener('click', async () => {
        const response = await fetch(`/admin/inventory/items/create-template/${itemCount}`);
        const html = await response.text();
        const container = document.getElementById('items-container');
        container.insertAdjacentHTML('beforeend', html);
        
        // Enable remove button for all items except the first one
        const removeButtons = document.querySelectorAll('.remove-item');
        if (removeButtons.length > 1) {
            removeButtons.forEach(btn => btn.classList.remove('hidden'));
        }
        
        itemCount++;
    });

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-section').remove();
            
            // Hide remove button if only one item remains
            const removeButtons = document.querySelectorAll('.remove-item');
            if (removeButtons.length <= 1) {
                removeButtons.forEach(btn => btn.classList.add('hidden'));
            }
        }
    });

    // Delegate change events for category selects
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('item-category')) {
            const index = e.target.dataset.index;
            const catId = e.target.value;
            const attrContainer = document.querySelector(`.category-attributes[data-index="${index}"]`);
            const attrField = document.querySelector(`input[name="items[${index}][attributes]"]`);
            
            attrContainer.innerHTML = getAttributeInputs(catId, index);
            
            // Set up event listeners for the new attribute fields
            const attributeFields = attrContainer.querySelectorAll('.attribute-field');
            attributeFields.forEach(field => {
                field.addEventListener('input', updateAttributesField.bind(null, index));
            });
        }
    });

    function updateAttributesField(index) {
        const attrContainer = document.querySelector(`.category-attributes[data-index="${index}"]`);
        const attrField = document.querySelector(`input[name="items[${index}][attributes]"]`);
        const inputs = attrContainer.querySelectorAll('.attribute-field');
        
        const json = {};
        inputs.forEach(input => {
            const key = input.dataset.attr;
            json[key] = input.value;
        });
        
        attrField.value = JSON.stringify(json);
    }

    function getAttributeInputs(categoryId, index) {
        if (categoryId == 1) {
            return `
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-300">Food Attributes</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ingredients</label>
                            <input type="text" placeholder="Ingredients" data-attr="ingredients" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Portion Size</label>
                            <input type="text" placeholder="Portion Size" data-attr="portion_size" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prep Time (mins)</label>
                            <input type="number" placeholder="Prep Time" data-attr="prep_time" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Available From</label>
                            <input type="time" placeholder="HH:MM" data-attr="available_from" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Available To</label>
                            <input type="time" placeholder="HH:MM" data-attr="available_to" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Available Days</label>
                            <input type="text" placeholder="Mon,Tue,Wed" data-attr="available_days" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image Filename</label>
                            <input type="text" placeholder="image.jpg" data-attr="img" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount (%)</label>
                            <input type="number" step="0.01" placeholder="0.00" data-attr="discount" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>`;
        } else if (categoryId == 4) {
            return `
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-300">Kitchen Equipment Attributes</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand</label>
                            <input type="text" placeholder="Brand" data-attr="brand" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Material</label>
                            <input type="text" placeholder="Material" data-attr="material" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dimensions</label>
                            <input type="text" placeholder="Length" data-attr="length" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Warranty</label>
                            <input type="text" placeholder="Warranty" data-attr="warranty" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image Filename</label>
                            <input type="text" placeholder="image.jpg" data-attr="img" 
                                   class="attribute-field w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>`;
        }
        return '';
    }
});
</script>
@endsection