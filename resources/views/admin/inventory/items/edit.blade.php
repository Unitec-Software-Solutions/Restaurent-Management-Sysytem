@extends('layouts.admin')

@section('header-title', 'Edit Item: ' . $item->name)

@section('content')
<div class="p-4 rounded-lg">
    {{-- <!-- Header with KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Items Card -->
        <x-partials.cards.stats-card 
            title="Total Items" 
            value="{{ $totalItems }}" 
            trend="+{{ $newItemsToday }} today" 
            icon="fas fa-box-open" 
            color="indigo" />

        <!-- Active Items Card -->
        <x-partials.cards.stats-card 
            title="Active Items" 
            value="{{ $activeItems }}" 
            trend="{{ $activeItemsChange > 0 ? '+' : '' }}{{ $activeItemsChange }} from yesterday" 
            icon="fas fa-check-circle" 
            color="green" />

        <!-- Inactive Items Card -->
        <x-partials.cards.stats-card 
            title="Inactive Items" 
            value="{{ $inactiveItems }}" 
            trend="{{ $inactiveItemsChange > 0 ? '+' : '' }}{{ $inactiveItemsChange }} from yesterday" 
            icon="fas fa-times-circle" 
            color="red" />
    </div> --}}

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Edit Item: {{ $item->name }}</h2>
                <p class="text-sm text-gray-500">Update item details in your inventory</p>
            </div>
            
            <div class="flex space-x-2">
                <a href="{{ route('admin.inventory.items.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Items
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-800 dark:border-green-600 dark:text-green-100">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-800 dark:border-red-600 dark:text-red-100">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>There were some problems with your input.</span>
                </div>
                <ul class="mt-2 list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Section -->
        <div class="p-6">
            <form action="{{ route('admin.inventory.items.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information Column -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                            <div class="relative">
                                <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Enter item name">
                                <i class="fas fa-box absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unicode Name</label>
                            <input type="text" name="unicode_name" value="{{ old('unicode_name', $item->unicode_name) }}"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter unicode name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Item Code *</label>
                            <div class="relative">
                                <input type="text" name="item_code" value="{{ old('item_code', $item->item_code) }}" required
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Enter item code">
                                <i class="fas fa-barcode absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select name="item_category_id" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ $item->item_category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measurement *</label>
                            <input type="text" name="unit_of_measurement" required
                                value="{{ old('unit_of_measurement', $item->unit_of_measurement) }}"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="e.g., kg, pcs, liters">
                        </div>
                    </div>

                    <!-- Pricing & Status Column -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (Rs.) *</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="buying_price" required
                                    value="{{ old('buying_price', $item->buying_price) }}"
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="0.00">
                                <i class="fas fa-rupee-sign absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (Rs.) *</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="selling_price" required
                                    value="{{ old('selling_price', $item->selling_price) }}"
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="0.00">
                                <i class="fas fa-rupee-sign absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level *</label>
                            <input type="number" name="reorder_level" required
                                value="{{ old('reorder_level', $item->reorder_level) }}"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Minimum stock level">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shelf Life (days)</label>
                            <input type="number" name="shelf_life_in_days"
                                value="{{ old('shelf_life_in_days', $item->shelf_life_in_days) }}"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Expiry period in days">
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input type="hidden" name="is_perishable" value="0">
                                <input type="checkbox" name="is_perishable" value="1" id="is_perishable" 
                                    {{ old('is_perishable', $item->is_perishable) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_perishable" class="ml-2 block text-sm text-gray-700">Perishable</label>
                            </div>

                            <div class="flex items-center">
                                <input type="hidden" name="is_menu_item" value="0">
                                <input type="checkbox" name="is_menu_item" value="1" id="is_menu_item"
                                    {{ old('is_menu_item', $item->is_menu_item) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_menu_item" class="ml-2 block text-sm text-gray-700">Menu Item</label>
                            </div>
                        </div>
                    </div>

                    <!-- Full Width Fields -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Detailed item description">{{ old('description', $item->description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                        <textarea name="additional_notes" rows="2"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Any special notes about this item">{{ old('additional_notes', $item->additional_notes) }}</textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.inventory.items.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 border border-transparent rounded-lg text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection