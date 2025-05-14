
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
            
            <!-- Items Table -->
            <div class="mb-8">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reorder Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Purchase Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Selling Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="items-container">
                            <tr class="item-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" name="items[0][name]" required
                                           class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" name="items[0][sku]" required
                                           class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="items[0][inventory_category_id]" required 
                                            class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="items[0][reorder_level]" required step="0.001"
                                           class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="items[0][purchase_price]" required step="0.01"
                                           class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="items[0][selling_price]" required step="0.01"
                                           class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button type="button" class="text-red-600 hover:text-red-900 delete-row hidden">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button type="button" id="add-item" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Add Another Item
                    </button>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
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
    
    // Add new item row
    document.getElementById('add-item').addEventListener('click', function() {
        const template = document.querySelector('.item-row').cloneNode(true);
        
        // Update input names
        template.querySelectorAll('input, select').forEach(input => {
            input.name = input.name.replace('[0]', `[${itemCount}]`);
            input.value = '';
        });
        
        // Show delete button
        template.querySelector('.delete-row').classList.remove('hidden');
        
        document.getElementById('items-container').appendChild(template);
        itemCount++;
    });
    
    // Delete row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-row')) {
            if (document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });
});
</script>
@endsection