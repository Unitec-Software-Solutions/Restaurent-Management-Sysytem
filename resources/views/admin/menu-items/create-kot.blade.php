@extends('layouts.admin')

@section('title', 'Bulk Add Menu Items from Inventory')

@section('content')
<div class="p-6">
    <!-- Breadcrumb Navigation -->
    <x-breadcrumb 
        :items="[
            ['name' => 'Menu Items', 'url' => route('admin.menu-items.enhanced.index')],
            ['name' => 'Bulk Add from Inventory']
        ]"
        current="Bulk Import Items"
        type="menu-items" />

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Bulk Add Menu Items from Inventory</h1>
                <p class="text-gray-600 mb-2">Select multiple items from Item Master to add as menu items</p>
                <div class="flex items-center text-sm">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs mr-3">
                        📦 Bulk Import
                    </span>
                    <span class="text-gray-500">Items will be classified as KOT or Buy & Sell automatically</span>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.menu-items.enhanced.index') }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Menu Items
                </a>
                <a href="{{ route('admin.inventory.items.index') }}" 
                   class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                    <i class="fas fa-boxes mr-2"></i>View Item Master
                </a>
            </div>
        </div>
    </div>

    <!-- Explanation Box -->
    <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div class="flex items-start">
            <i class="fas fa-warehouse text-blue-500 text-xl mt-0.5 mr-4"></i>
            <div class="flex-1">
                <h3 class="font-semibold text-blue-900 mb-2">Bulk Add Menu Items from Inventory</h3>
                <div class="text-sm text-blue-800 space-y-2">
                    <p><strong>Select multiple items from your Item Master</strong> to quickly add them as menu items. The system will automatically classify each item as:</p>
                    <div class="grid md:grid-cols-2 gap-4 mt-3">
                        <div>
                            <h4 class="font-medium text-blue-900 mb-1">🍽️ KOT Items (Kitchen Preparation):</h4>
                            <ul class="text-blue-700 text-xs space-y-1">
                                <li>• Items that require cooking/preparation</li>
                                <li>• Dishes made from multiple ingredients</li>
                                <li>• Custom recipes and preparations</li>
                                <li>• Items with cooking instructions</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium text-green-900 mb-1">🛒 Buy & Sell Items (Inventory):</h4>
                            <ul class="text-green-700 text-xs space-y-1">
                                <li>• Pre-packaged items with stock</li>
                                <li>• Bottled beverages and snacks</li>
                                <li>• Items sold directly from inventory</li>
                                <li>• Ready-to-serve products</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

    <form action="{{ route('admin.menu-items.store-kot') }}" method="POST" class="space-y-6">
        @csrf
        
        @auth('admin')
            @if(auth('admin')->user()->is_super_admin)
                <!-- Organization and Branch Selection for Super Admin -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization Context</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div>
                            <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Organization <span class="text-red-500">*</span>
                            </label>
                            <select id="organization_id" name="organization_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('organization_id') border-red-500 @enderror">
                                <option value="">Select Organization</option>
                                @foreach($organizations ?? [] as $organization)
                                    <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
                                        {{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('organization_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Branch <span class="text-gray-400">(Optional)</span>
                            </label>
                            <select id="branch_id" name="branch_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('branch_id') border-red-500 @enderror">
                                <option value="">All Branches</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif
        @endauth
        
        <!-- Configuration Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">KOT Configuration</h3>
            
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
                </div>
                
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">
                        Default Preparation Time (minutes)
                    </label>
                    <input type="number" id="preparation_time" name="preparation_time" 
                           value="{{ old('preparation_time', 15) }}" min="1" max="240"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-sm text-gray-500 mt-1">This will be applied to all selected items</p>
                </div>
            </div>
        </div>        
        <!-- Item Selection Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Items from Item Master</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <label class="flex items-center">
                                    <input type="checkbox" id="select_all" 
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2">All</span>
                                </label>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($itemMasterRecords as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="item_master_ids[]" value="{{ $item->id }}" 
                                           id="item_{{ $item->id }}" class="item-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           {{ in_array($item->id, old('item_master_ids', [])) ? 'checked' : '' }}>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label for="item_{{ $item->id }}" class="text-sm font-medium text-gray-900 cursor-pointer">
                                        {{ $item->name }}
                                    </label>
                                    @if($item->unicode_name)
                                        <div class="text-sm text-gray-500">{{ $item->unicode_name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->item_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->itemCategory->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">LKR {{ number_format($item->selling_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->is_active)
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center">
                                    <div class="text-gray-500">
                                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                        <p>No items available for KOT creation.</p>
                                        <a href="{{ route('admin.inventory.items.create') }}" class="text-indigo-600 hover:text-indigo-500">
                                            Create Item Master
                                        </a> first.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($itemMasterRecords->count() > 0)
            <!-- Options Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Options</h3>
                
                <label class="flex items-center">
                    <input type="checkbox" id="is_available" name="is_available" value="1" 
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                           {{ old('is_available', true) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-900">Make items available immediately</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-2"></i>
                    Selected items will be created as KOT menu items with preparation required.
                </div>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create KOT Items
                </button>
            </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select_all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    
    // Select/Deselect all functionality
    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Update select all checkbox when individual items are changed
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked').length;
            const totalItems = itemCheckboxes.length;
            
            selectAllCheckbox.checked = checkedItems === totalItems;
            selectAllCheckbox.indeterminate = checkedItems > 0 && checkedItems < totalItems;
        });
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked').length;
        const categorySelected = document.getElementById('menu_category_id').value;
        
        if (!categorySelected) {
            e.preventDefault();
            alert('Please select a menu category.');
            return;
        }
        
        if (checkedItems === 0) {
            e.preventDefault();
            alert('Please select at least one item to create KOT menu items.');
            return;
        }
        
        if (!confirm(`Create ${checkedItems} KOT menu item(s)?`)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endsection
