@extends('layouts.admin')
@section('header-title', 'Item Details')
@section('content')
<div class="p-4 rounded-lg">
    <!-- Header with KPI Cards -->
    {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Item Status Card -->
        <x-partials.cards.stats-card 
            title="Item Status" 
            value="{{ $item->deleted_at ? 'Inactive' : 'Active' }}" 
            trend="" 
            icon="fas fa-box" 
            color="{{ $item->deleted_at ? 'red' : 'green' }}" />

        <!-- Inventory Card -->
        <x-partials.cards.stats-card 
            title="Current Stock" 
            value="{{ $item->current_stock ?? 'N/A' }}" 
            trend="" 
            icon="fas fa-warehouse" 
            color="blue" />
    </div> --}}

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Item Profile Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden col-span-1 md:col-span-2 lg:col-span-1">
            <div class="p-6 flex flex-col items-center">
                @if ($item->attributes['img'] ?? false)
                <img src="{{ asset('storage/' . $item->attributes['img']) }}" 
                     alt="{{ $item->name }}"
                     class="h-32 w-32 object-cover rounded-lg mb-4 shadow-md">
                @else
                <div class="h-32 w-32 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-box text-indigo-600 dark:text-indigo-300 text-4xl"></i>
                </div>
                @endif
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white text-center">{{ $item->name }}</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $item->item_code }}</p>
                
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full text-xs">
                        {{ $item->category->name ?? 'No Category' }}
                    </span>
                    @if($item->is_perishable)
                    <span class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full text-xs">
                        Perishable
                    </span>
                    @endif
                    @if($item->is_menu_item)
                    <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-xs">
                        Menu Item
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Basic Info Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                    Basic Information
                </h3>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Unit</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->unit_of_measurement }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Reorder Level</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->reorder_level ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Branch</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->branch->name ?? 'N/A' }}</p>
                </div>
                @if($item->is_perishable)
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Shelf Life</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->shelf_life_in_days ?? 'N/A' }} days</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Pricing Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-tag text-indigo-600 mr-2"></i>
                    Pricing
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Buying Price</span>
                    <span class="font-medium text-gray-900 dark:text-white">Rs. {{ number_format($item->buying_price, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Selling Price</span>
                    <span class="font-medium text-gray-900 dark:text-white">Rs. {{ number_format($item->selling_price, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Profit Margin</span>
                    <span class="font-medium text-green-600 dark:text-green-400">
                        {{ number_format((($item->selling_price - $item->buying_price) / $item->buying_price * 100), 2) }}%
                    </span>
                </div>
                @if($item->is_menu_item && isset($item->attributes['discount']))
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Current Discount</span>
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $item->attributes['discount'] }}%</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Description Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden md:col-span-2">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-align-left text-indigo-600 mr-2"></i>
                    Description & Notes
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description</h4>
                    <p class="text-gray-700 dark:text-gray-300">{{ $item->description ?? 'No description available' }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Additional Notes</h4>
                    <p class="text-gray-700 dark:text-gray-300">{{ $item->additional_notes ?? 'No additional notes' }}</p>
                </div>
            </div>
        </div>

        <!-- Menu Item Attributes -->
        @if($item->is_menu_item)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden md:col-span-2">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-utensils text-indigo-600 mr-2"></i>
                    Menu Item Details
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @if(isset($item->attributes['prep_time']))
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Prep Time</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['prep_time'] }} mins</p>
                </div>
                @endif
                @if(isset($item->attributes['portion_size']))
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Portion Size</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['portion_size'] }}</p>
                </div>
                @endif
                @if(isset($item->attributes['ingredients']))
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Ingredients</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['ingredients'] }}</p>
                </div>
                @endif
                @if(isset($item->attributes['available_from']))
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Available From</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['available_from'] }}</p>
                </div>
                @endif
                @if(isset($item->attributes['available_to']))
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Available To</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['available_to'] }}</p>
                </div>
                @endif
                @if(isset($item->attributes['available_days']))
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Available Days</p>
                    <p class="text-gray-900 dark:text-white">
                        {{ str_replace(',', ', ', $item->attributes['available_days']) }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Additional Attributes -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden md:col-span-3">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-list text-indigo-600 mr-2"></i>
                    Additional Attributes
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @if (is_array($item->attributes) || is_object($item->attributes))
                    @foreach ($item->attributes as $key => $value)
                        @if (!in_array($key, ['img', 'discount', 'prep_time', 'portion_size', 'ingredients', 'available_from', 'available_to', 'available_days']))
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">
                                    {{ str_replace('_', ' ', $key) }}
                                </p>
                                @if (is_array($value))
                                    <p class="text-gray-900 dark:text-white">{{ implode(', ', $value) }}</p>
                                @else
                                    <p class="text-gray-900 dark:text-white">{{ $value }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-span-full text-center py-4">
                        <p class="text-gray-500 dark:text-gray-400">No additional attributes available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 flex flex-col sm:flex-row justify-between gap-4">
        <a href="{{ route('admin.inventory.items.index') }}" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Items
        </a>
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('admin.inventory.items.edit', $item->id) }}" 
               class="px-6 py-2 bg-indigo-600 border border-transparent rounded-lg text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
                <i class="fas fa-edit mr-2"></i> Edit Item
            </a>
            <form action="{{ route('admin.inventory.items.destroy', $item->id) }}" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="w-full px-6 py-2 bg-red-600 border border-transparent rounded-lg text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        onclick="return confirm('Are you sure you want to delete this item?')">
                    <i class="fas fa-trash-alt mr-2"></i> Delete Item
                </button>
            </form>
        </div>
    </div>
</div>
@endsection