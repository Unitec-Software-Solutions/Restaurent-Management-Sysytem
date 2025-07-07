@extends('layouts.admin')

@section('title', 'Create KOT Menu Items')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-fire text-orange-600 mr-3"></i>Create KOT Menu Items
            </h1>
            <p class="text-gray-600 mt-1">
                Create kitchen order ticket items from production-ready Item Master records
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.menu-items.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Menu Items
            </a>
            <a href="{{ route('admin.inventory.items.create') }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Item Master
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
                    <li>• Only items marked for menu inclusion and requiring production are shown</li>
                    <li>• KOT items require kitchen preparation before serving to customers</li>
                    <li>• Each selected item will be created as a separate menu item with preparation requirements</li>
                    <li>• Items already converted to KOT menu items are automatically excluded</li>
                </ul>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.menu-items.create-kot') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Category Selection -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-tags text-indigo-600 mr-2"></i>Menu Category Selection
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Target Menu Category <span class="text-red-500">*</span>
                    </label>
                    <select name="menu_category_id" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Select a menu category</option>
                        @foreach($menuCategories as $category)
                            <option value="{{ $category->id }}" {{ old('menu_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                                @if($category->description)
                                    - {{ $category->description }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">All selected items will be added to this category</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Default Preparation Time (minutes)
                    </label>
                    <input type="number" name="preparation_time" value="{{ old('preparation_time', 15) }}" 
                           min="1" max="240" step="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <p class="text-xs text-gray-500 mt-1">Default cooking/preparation time for all items</p>
                </div>
            </div>
        </div>

        <!-- Kitchen Station Selection -->
        @if($kitchenStations->count() > 0)
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-utensils text-green-600 mr-2"></i>Kitchen Station Assignment
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kitchen Station</label>
                    <select name="kitchen_station_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Auto-assign based on item type</option>
                        @foreach($kitchenStations as $station)
                            <option value="{{ $station->id }}" {{ old('kitchen_station_id') == $station->id ? 'selected' : '' }}>
                                {{ $station->name }}
                                @if($station->description)
                                    - {{ $station->description }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Leave blank for automatic assignment</p>
                </div>

                <div class="flex items-center pt-8">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_available" value="1" 
                               class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                               {{ old('is_available', true) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-900">Make items available immediately</span>
                    </label>
                </div>
            </div>
        </div>
        @endif

        <!-- Item Selection -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list-check text-blue-600 mr-2"></i>Select Production Items
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Choose Item Master records to convert into KOT menu items
                </p>
            </div>

            <div class="p-6">
                @if($itemMasterRecords->count() > 0)
                    <!-- Select All Option -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <label class="flex items-center">
                            <input type="checkbox" id="select_all" 
                                   class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="ml-2 text-sm font-medium text-gray-900">Select All Items ({{ $itemMasterRecords->count() }} items)</span>
                        </label>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <label class="flex items-center">
                                            <input type="checkbox" id="header_select_all" 
                                                   class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            <span class="ml-2">Select</span>
                                        </label>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($itemMasterRecords as $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="item_master_ids[]" value="{{ $item->id }}" 
                                                       class="item-checkbox rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            </label>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                @if($item->unicode_name)
                                                    <div class="text-sm text-gray-600">{{ $item->unicode_name }}</div>
                                                @endif
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Code: {{ $item->item_code ?: 'N/A' }}
                                                </div>
                                                @if($item->description)
                                                    <div class="text-xs text-gray-600 mt-1 max-w-md truncate">{{ $item->description }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $item->itemCategory->name ?? 'Uncategorized' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <div>Sell: <span class="font-medium">LKR {{ number_format($item->selling_price, 2) }}</span></div>
                                                @if($item->buying_price > 0)
                                                    <div class="text-xs text-gray-500">Cost: LKR {{ number_format($item->buying_price, 2) }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col space-y-1">
                                                @if($item->requires_production)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">
                                                        <i class="fas fa-fire mr-1"></i>Production Required
                                                    </span>
                                                @endif
                                                @if($item->is_active)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                        <i class="fas fa-check mr-1"></i>Active
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                                        <i class="fas fa-pause mr-1"></i>Inactive
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2 text-orange-500"></i>
                            Selected items will be created as KOT menu items with preparation requirements.
                        </div>
                        <div class="flex gap-3">
                            <button type="button" onclick="window.history.back()" 
                                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                                <i class="fas fa-fire mr-2"></i>Create KOT Items
                            </button>
                        </div>
                    </div>

                @else
                    <!-- No Items Available -->
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-exclamation-triangle text-6xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Production Items Available</h3>
                        <p class="text-gray-600 mb-6">
                            There are no Item Master records suitable for KOT creation. Items must be:
                        </p>
                        <ul class="text-sm text-gray-600 space-y-1 mb-6 text-left max-w-md mx-auto">
                            <li>• Marked as menu items (is_menu_item = true)</li>
                            <li>• Active and available for use</li>
                            <li>• Require production or preparation</li>
                            <li>• Not already converted to menu items</li>
                        </ul>
                        <div class="space-x-3">
                            <a href="{{ route('admin.inventory.items.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Create Item Master
                            </a>
                            <a href="{{ route('admin.inventory.items.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-list mr-2"></i>View Item Master
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select_all');
    const headerSelectAllCheckbox = document.getElementById('header_select_all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    
    // Sync select all checkboxes
    function syncSelectAll() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const totalCount = itemCheckboxes.length;
        
        if (checkedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
            headerSelectAllCheckbox.indeterminate = false;
            headerSelectAllCheckbox.checked = false;
        } else if (checkedCount === totalCount) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
            headerSelectAllCheckbox.indeterminate = false;
            headerSelectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
            headerSelectAllCheckbox.indeterminate = true;
        }
    }
    
    // Select/Deselect all functionality
    function toggleAll(checked) {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        syncSelectAll();
    }
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            toggleAll(this.checked);
            headerSelectAllCheckbox.checked = this.checked;
        });
    }
    
    if (headerSelectAllCheckbox) {
        headerSelectAllCheckbox.addEventListener('change', function() {
            toggleAll(this.checked);
            selectAllCheckbox.checked = this.checked;
        });
    }
    
    // Individual checkbox change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', syncSelectAll);
    });
    
    // Initial sync
    syncSelectAll();
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
        const categorySelect = document.querySelector('select[name="menu_category_id"]');
        
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one item to create KOT menu items.');
            return;
        }
        
        if (!categorySelect.value) {
            e.preventDefault();
            alert('Please select a menu category.');
            categorySelect.focus();
            return;
        }
        
        // Confirm action
        const confirmation = confirm(`Create ${selectedItems.length} KOT menu items in the selected category?`);
        if (!confirmation) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endsection
