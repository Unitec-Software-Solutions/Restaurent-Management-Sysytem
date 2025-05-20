@php $prefix = "items[{$index}]"; @endphp
<div class="item-section mb-6 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg shadow border border-gray-200 dark:border-gray-600">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Item #{{ $index + 1 }}</h3>
        <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 remove-item {{ $index === 0 ? 'hidden' : '' }}">
            <i class="fas fa-trash-alt mr-1"></i> Remove
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Basic Info -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name*</label>
            <input type="text" name="{{ $prefix }}[name]" required 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unicode Name</label>
            <input type="text" name="{{ $prefix }}[unicode_name]" 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Code*</label>
            <input type="text" name="{{ $prefix }}[item_code]" required 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category*</label>
            <select name="{{ $prefix }}[item_category_id]" required 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white item-category"
                    data-index="{{ $index }}">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit*</label>
            <select name="{{ $prefix }}[unit_of_measurement]" required 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Select Unit</option>
                <option value="piece">Piece</option>
                <option value="kg">Kg</option>
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
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reorder Level</label>
            <input type="number" name="{{ $prefix }}[reorder_level]" min="0" step="1" 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buying Price*</label>
            <div class="relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400">Rs.</span>
                </div>
                <input type="number" name="{{ $prefix }}[buying_price]" required step="0.01" 
                       class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Selling Price*</label>
            <div class="relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400">Rs.</span>
                </div>
                <input type="number" name="{{ $prefix }}[selling_price]" required step="0.01" 
                       class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Shelf Life (Days)</label>
            <input type="number" name="{{ $prefix }}[shelf_life_in_days]" step="1" 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea name="{{ $prefix }}[description]" rows="3" 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Notes</label>
            <textarea name="{{ $prefix }}[additional_notes]" rows="3" 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-6 mt-2">
        <div class="flex items-center">
            <input type="checkbox" id="perishable-{{ $index }}" name="{{ $prefix }}[is_perishable]" value="1" 
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
            <label for="perishable-{{ $index }}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Perishable</label>
        </div>
        <div class="flex items-center">
            <input type="checkbox" id="menuitem-{{ $index }}" name="{{ $prefix }}[is_menu_item]" value="1" 
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
            <label for="menuitem-{{ $index }}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Menu Item</label>
        </div>
    </div>

    <!-- Dynamic Category-Specific Attributes -->
    <div class="mt-6 category-attributes" data-index="{{ $index }}"></div>

    <!-- Hidden JSON attributes field -->
    <input type="hidden" name="{{ $prefix }}[attributes]" class="attributes-json" />
</div>