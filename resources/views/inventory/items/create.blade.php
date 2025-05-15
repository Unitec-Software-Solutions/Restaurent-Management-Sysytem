@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Add New Inventory Items</h2>
        <a href="{{ route('inventory.items.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Back to Items List</a>
    </div>

    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <form action="{{ route('inventory.items.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Add Item Section -->
            <div id="items-container">
                <div class="item-section mb-8 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Item #1</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name*</label>
                            <input type="text" name="items[0][name]" required
                                   class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU*</label>
                            <input type="text" name="items[0][sku]" required
                                   class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category*</label>
                            <select name="items[0][inventory_category_id]" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit of Measurement*</label>
                            <select name="items[0][unit_of_measurement]" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Select Unit</option>
                                <option value="piece">Piece</option>
                                <option value="kg">Kilogram</option>
                                <option value="g">Gram</option>
                                <option value="L">Liter</option>
                                <option value="ml">Milliliter</option>
                                <option value="box">Box</option>
                                <option value="pack">Pack</option>
                                <option value="bottle">Bottle</option>
                                <option value="can">Can</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reorder Level*</label>
                            <input type="number" name="items[0][reorder_level]" required step="0.001" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        @if(isset($branches) && $branches->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                            <select name="items[0][branch_id]" 
                                    class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Purchase Price*</label>
                            <input type="number" name="items[0][purchase_price]" required step="0.01" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Selling Price*</label>
                            <input type="number" name="items[0][selling_price]" required step="0.01" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        
                    </div>
                    
                    <!-- Additional Options -->
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                        <div class="flex flex-wrap gap-6">
                            <div class="flex items-center">
                                <input type="checkbox" id="show_in_menu_0" name="items[0][show_in_menu]" value="1" 
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="show_in_menu_0" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Show in Menu
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="is_perishable_0" name="items[0][is_perishable]" value="1" 
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="is_perishable_0" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    This is a perishable item
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="is_active_0" name="items[0][is_active]" value="1" checked
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="is_active_0" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-right">
                        <button type="button" class="text-red-600 hover:text-red-900 delete-row hidden">
                            Remove Item
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="button" id="add-item" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Add Another Item
                </button>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="reset" 
                        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 dark:text-white bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Reset
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Save Items
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;
    
    // Toggle perishable details
    const togglePerishableDetails = function(index) {
        const checkbox = document.getElementById(`is_perishable_${index}`);
        const detailsDiv = document.getElementById(`perishable_details_${index}`);
        
        if (checkbox && detailsDiv) {
            if (checkbox.checked) {
                detailsDiv.classList.remove('hidden');
            } else {
                detailsDiv.classList.add('hidden');
            }
        }
    };
    
    // Initial setup for first item
    document.getElementById('is_perishable_0').addEventListener('change', function() {
        togglePerishableDetails(0);
    });
    
    // Add new item section
    document.getElementById('add-item').addEventListener('click', function() {
        const template = document.querySelector('.item-section').cloneNode(true);
        
        // Update headings
        template.querySelector('h3').textContent = `Item #${itemCount + 1}`;
        
        // Update input names and ids
        template.querySelectorAll('input, select, textarea').forEach(input => {
            const newName = input.name.replace(/\[0\]/g, `[${itemCount}]`);
            input.name = newName;
            
            if (input.id) {
                const newId = input.id.replace(/_0$/g, `_${itemCount}`);
                input.id = newId;
                
                // Update associated labels
                const associatedLabel = template.querySelector(`label[for="${input.id.replace(`_${itemCount}`, '_0')}"]`);
                if (associatedLabel) {
                    associatedLabel.setAttribute('for', newId);
                }
            }
            
            // Clear values
            if (input.type === 'checkbox') {
                if (input.id === `is_active_${itemCount}`) {
                    input.checked = true; // Keep 'active' checked by default
                } else {
                    input.checked = false;
                }
            } else {
                input.value = '';
            }
        });
        
        // Update perishable details div id
        const perishableDetails = template.querySelector(`div[id="perishable_details_0"]`);
        if (perishableDetails) {
            perishableDetails.id = `perishable_details_${itemCount}`;
            perishableDetails.classList.add('hidden'); // Hide by default
        }
        
        // Add event listener for perishable checkbox
        const perishableCheckbox = template.querySelector(`input[id="is_perishable_${itemCount}"]`);
        if (perishableCheckbox) {
            perishableCheckbox.addEventListener('change', function() {
                togglePerishableDetails(itemCount);
            });
        }
        
        // Show delete button
        template.querySelector('.delete-row').classList.remove('hidden');
        
        document.getElementById('items-container').appendChild(template);
        itemCount++;
    });
    
    // Delete item section
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-row')) {
            if (document.querySelectorAll('.item-section').length > 1) {
                e.target.closest('.item-section').remove();
            }
        }
    });
});
</script>
@endsection