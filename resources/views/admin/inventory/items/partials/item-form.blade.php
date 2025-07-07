@php $prefix = "items[{$index}]"; @endphp
<div
    class="item-section mb-6 p-6 bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Item #{{ $index + 1 }}</h3>
            @if (Auth::guard('admin')->user()->is_super_admin)
                <p class="text-xs text-blue-600 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Categories will be loaded based on selected organization
                </p>
            @endif
        </div>
        <button type="button"
            class="remove-item {{ $index === 0 ? 'hidden' : '' }} text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
            <i class="fas fa-trash-alt mr-1"></i> Remove
        </button>
    </div>

    <!-- Organization Display for Super Admin -->
    @if (Auth::guard('admin')->user()->is_super_admin)
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center text-sm">
                <i class="fas fa-building text-blue-600 mr-2"></i>
                <span class="text-blue-800 font-medium">Organization:</span>
                <span class="text-blue-700 ml-2 organization-display">Please select organization above</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <!-- Basic Info -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name*</label>
            <input type="text" name="{{ $prefix }}[name]" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Enter item name">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unicode Name</label>
            <input type="text" name="{{ $prefix }}[unicode_name]"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Enter unicode name">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Code*</label>
            <input type="text" name="{{ $prefix }}[item_code]" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Enter item code">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Category*
                @if (Auth::guard('admin')->user()->is_super_admin)
                    <span class="text-xs text-blue-600">(Select organization first)</span>
                @endif
            </label>
            <select name="{{ $prefix }}[item_category_id]" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white item-category"
                data-index="{{ $index }}">
                <option value="">Select Category</option>
                @if (!Auth::guard('admin')->user()->is_super_admin)
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                @endif
            </select>
            @if (Auth::guard('admin')->user()->is_super_admin)
                <p class="text-xs text-gray-500 mt-1 category-help-text">Categories will load after selecting
                    organization</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit*</label>
            <select name="{{ $prefix }}[unit_of_measurement]" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Minimum stock level">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buying Price (Rs.)*</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400">Rs.</span>
                </div>
                <input type="number" name="{{ $prefix }}[buying_price]" required step="0.01" min="0"
                    class="block w-full pl-10 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="0.00">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Selling Price (Rs.)*</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400">Rs.</span>
                </div>
                <input type="number" name="{{ $prefix }}[selling_price]" required step="0.01" min="0"
                    class="block w-full pl-10 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="0.00">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Shelf Life (Days)</label>
            <input type="number" name="{{ $prefix }}[shelf_life_in_days]" step="1" min="0"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Expiry period in days">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea name="{{ $prefix }}[description]" rows="3"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Detailed item description"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Notes</label>
            <textarea name="{{ $prefix }}[additional_notes]" rows="3"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Any special notes about this item"></textarea>
        </div>
    </div>

    <!-- Item Type Selection Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Item Type <span class="text-red-500">*</span>
            </label>
            <select name="{{ $prefix }}[item_type]" required
                class="item-type-select w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                data-index="{{ $index }}">
                <option value="">Select Item Type</option>
                <option value="buy_sell">Buy & Sell Item (Inventory Item)</option>
            </select>
            <div class="text-xs text-gray-500 mt-2 p-3 bg-blue-50 border border-blue-200 rounded">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-0.5"></i>
                    <div>
                        <div class="font-medium text-blue-800 mb-1">Item Master is for inventory items only</div>
                        <div class="text-blue-700 mb-2"><strong>Buy & Sell:</strong> Items you purchase and sell directly with stock tracking</div>
                        <div class="text-amber-700"><strong>For KOT Items (Recipes):</strong> Create dishes/recipes in Menu Items → Create KOT Recipe instead</div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Current Stock Level
            </label>
            <input type="number" name="{{ $prefix }}[current_stock]" min="0" step="0.01"
                class="current-stock-input w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="0.00" data-index="{{ $index }}">
            <p class="text-xs text-gray-500 mt-1">
                <span class="stock-required-text">Required for Buy & Sell items, optional for KOT items</span>
            </p>
        </div>
    </div>

    <div class="flex items-center space-x-6 mt-4">
        <div class="flex items-center">
            <input type="hidden" name="{{ $prefix }}[is_perishable]" value="0">
            <input type="checkbox" id="perishable-{{ $index }}" name="{{ $prefix }}[is_perishable]"
                value="1"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
            <label for="perishable-{{ $index }}"
                class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Perishable</label>
        </div>
        <div class="flex items-center">
            <input type="hidden" name="{{ $prefix }}[is_menu_item]" value="0">
            <input type="checkbox" id="menuitem-{{ $index }}" name="{{ $prefix }}[is_menu_item]"
                value="1"
                class="menu-item-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                data-index="{{ $index }}">
            <label for="menuitem-{{ $index }}"
                class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Include in Menu</label>
        </div>
        <div class="flex items-center">
            <input type="hidden" name="{{ $prefix }}[requires_production]" value="0">
            <input type="checkbox" id="requires-production-{{ $index }}" name="{{ $prefix }}[requires_production]"
                value="1"
                class="production-checkbox h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                data-index="{{ $index }}">
            <label for="requires-production-{{ $index }}"
                class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Requires Production</label>
        </div>
        <div class="flex items-center">
            <input type="hidden" name="{{ $prefix }}[is_inventory_item]" value="0">
            <input type="checkbox" id="inventory-item-{{ $index }}" name="{{ $prefix }}[is_inventory_item]"
                value="1"
                class="inventory-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                data-index="{{ $index }}">
            <label for="inventory-item-{{ $index }}"
                class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Track Inventory</label>
        </div>
    </div>

    <!-- Menu-Specific Attributes (Initially Hidden) -->
    <div class="menu-attributes mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 hidden"
        data-index="{{ $index }}">
        <h4 class="text-lg font-semibold mb-4 text-blue-900 dark:text-blue-100 flex items-center">
            <i class="fas fa-utensils mr-2"></i>
            Menu Item Properties
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Cuisine Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cuisine Type</label>
                <select data-menu-attr="cuisine_type"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Cuisine</option>
                    <option value="sri_lankan">Sri Lankan</option>
                    <option value="chinese">Chinese</option>
                    <option value="indian">Indian</option>
                    <option value="italian">Italian</option>
                    <option value="continental">Continental</option>
                    <option value="seafood">Seafood</option>
                    <option value="vegetarian">Vegetarian</option>
                    <option value="vegan">Vegan</option>
                    <option value="dessert">Dessert</option>
                    <option value="beverage">Beverage</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Spice Level -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Spice Level</label>
                <select data-menu-attr="spice_level"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Level</option>
                    <option value="mild">Mild</option>
                    <option value="medium">Medium</option>
                    <option value="hot">Hot</option>
                    <option value="extra_hot">Extra Hot</option>
                </select>
            </div>

            <!-- Preparation Time -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prep Time
                    (minutes)</label>
                <input type="number" data-menu-attr="prep_time_minutes" min="1" max="120"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="e.g., 15">
            </div>

            <!-- Serving Size -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Serving Size</label>
                <select data-menu-attr="serving_size"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Size</option>
                    <option value="small">Small</option>
                    <option value="regular">Regular</option>
                    <option value="large">Large</option>
                    <option value="family">Family Size</option>
                </select>
            </div>

            <!-- Dietary Restrictions -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dietary</label>
                <select data-menu-attr="dietary_type"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Type</option>
                    <option value="vegetarian">Vegetarian</option>
                    <option value="vegan">Vegan</option>
                    <option value="gluten_free">Gluten Free</option>
                    <option value="dairy_free">Dairy Free</option>
                    <option value="halal">Halal</option>
                    <option value="kosher">Kosher</option>
                    <option value="none">None</option>
                </select>
            </div>

            <!-- Availability -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Available During</label>
                <select data-menu-attr="availability"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Availability</option>
                    <option value="all_day">All Day</option>
                    <option value="breakfast">Breakfast Only</option>
                    <option value="lunch">Lunch Only</option>
                    <option value="dinner">Dinner Only</option>
                    <option value="lunch_dinner">Lunch & Dinner</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Main Ingredients -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Main Ingredients</label>
                <textarea data-menu-attr="main_ingredients" rows="2"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="e.g., Rice, Chicken, Vegetables"></textarea>
            </div>

            <!-- Allergen Information -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Allergen Info</label>
                <textarea data-menu-attr="allergen_info" rows="2"
                    class="menu-attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="e.g., Contains nuts, dairy"></textarea>
            </div>
        </div>

        <!-- Chef's Recommendation -->
        <div class="mt-4">
            <div class="flex items-center">
                <input type="checkbox" data-menu-attr="is_chefs_special" value="1"
                    class="menu-attribute-field h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Chef's Special</label>
            </div>
        </div>

        <!-- Popular Item -->
        <div class="mt-2">
            <div class="flex items-center">
                <input type="checkbox" data-menu-attr="is_popular" value="1"
                    class="menu-attribute-field h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Popular Item</label>
            </div>
        </div>
    </div>

    <!-- Dynamic Category-Specific Attributes -->
    <div class="mt-6 category-attributes" data-index="{{ $index }}"></div>

    <!-- Hidden JSON attributes field -->
    <input type="hidden" name="{{ $prefix }}[attributes]" class="attributes-json" />
</div>

@if (Auth::guard('admin')->user()->is_super_admin)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update organization display when main organization select changes
            const orgSelect = document.getElementById('organization_id');
            if (orgSelect) {
                orgSelect.addEventListener('change', function() {
                    const orgName = this.options[this.selectedIndex]?.text ||
                        'Please select organization above';
                    document.querySelectorAll('.organization-display').forEach(span => {
                        span.textContent = orgName;
                        span.className = this.value ? 'text-green-700 ml-2 organization-display' :
                            'text-blue-700 ml-2 organization-display';
                    });
                });
            }
        });
    </script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Item type selection logic
    const itemTypeSelect = document.querySelector('.item-type-select[data-index="{{ $index }}"]');
    const currentStockInput = document.querySelector('.current-stock-input[data-index="{{ $index }}"]');
    const menuItemCheckbox = document.querySelector('.menu-item-checkbox[data-index="{{ $index }}"]');
    const productionCheckbox = document.querySelector('.production-checkbox[data-index="{{ $index }}"]');
    const inventoryCheckbox = document.querySelector('.inventory-checkbox[data-index="{{ $index }}"]');
    
    if (itemTypeSelect) {
        // Initialize based on current selection
        handleItemTypeChange();
        
        // Handle item type changes
        itemTypeSelect.addEventListener('change', handleItemTypeChange);
        
        function handleItemTypeChange() {
            const itemType = itemTypeSelect.value;
            const stockRequiredText = document.querySelector('.stock-required-text');
            
            if (itemType === 'buy_sell') {
                // Buy & Sell Item Configuration
                currentStockInput.required = true;
                currentStockInput.style.borderColor = '#F59E0B'; // Amber border for required
                
                // Auto-check appropriate flags
                inventoryCheckbox.checked = true;
                productionCheckbox.checked = false;
                menuItemCheckbox.checked = true; // Most buy & sell items go to menu
                
                // Update helper text
                if (stockRequiredText) {
                    stockRequiredText.innerHTML = '<span class="text-amber-600 font-medium">Required for Buy & Sell items</span> - Enter current inventory level';
                }
                
                showItemTypeInfo('Buy & Sell items require current stock and are sold directly to customers with inventory tracking.');
                
            } else {
                // Clear selection
                currentStockInput.required = false;
                currentStockInput.style.borderColor = '#D1D5DB';
                
                if (stockRequiredText) {
                    stockRequiredText.innerHTML = 'Required for Buy & Sell items';
                }
                showItemTypeInfo('');
            }
        }
        
        function showItemTypeInfo(message) {
            let infoDiv = document.querySelector(`.item-type-info-${{{ $index }}}`);
            if (!infoDiv && message) {
                infoDiv = document.createElement('div');
                infoDiv.className = `item-type-info-${{{ $index }}} mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800`;
                itemTypeSelect.parentNode.appendChild(infoDiv);
            }
            
            if (infoDiv) {
                if (message) {
                    infoDiv.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
                    infoDiv.style.display = 'block';
                } else {
                    infoDiv.style.display = 'none';
                }
            }
        }
    }
});
</script>
