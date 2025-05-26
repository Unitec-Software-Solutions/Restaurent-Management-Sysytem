@extends('layouts.admin')
@section('content')
<div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $item->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">{{ $item->item_code }}</p>
        </div>
        @if($item->attributes['img'] ?? false)
            <img src="{{ asset('storage/'.$item->attributes['img']) }}" alt="{{ $item->name }}" class="h-24 w-24 object-cover rounded-lg">
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Category</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->category->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Branch</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->branch->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Unit of Measurement</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->unit_of_measurement }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Reorder Level</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->reorder_level }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Perishable</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->is_perishable ? 'Yes' : 'No' }}</p>
                </div>
                @if($item->is_perishable)
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Shelf Life</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->shelf_life_in_days }} days</p>
                </div>
                @endif
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Menu Item</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->is_menu_item ? 'Yes' : 'No' }}</p>
                </div>
            </div>
        </div>

        <!-- Pricing Information -->
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Pricing Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Buying Price</p>
                    <p class="text-gray-900 dark:text-white">{{ number_format($item->buying_price, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Selling Price</p>
                    <p class="text-gray-900 dark:text-white">{{ number_format($item->selling_price, 2) }}</p>
                </div>
                @if($item->is_menu_item && isset($item->attributes['discounts']))
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Current Discount</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['discounts'] }}%</p>
                </div>
                @endif
                @if($item->is_menu_item && isset($item->attributes['prep_time']))
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Preparation Time</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['prep_time'] }} minutes</p>
                </div>
                @endif
                @if($item->is_menu_item && isset($item->attributes['portion_size']))
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Portion Size</p>
                    <p class="text-gray-900 dark:text-white">{{ ucfirst($item->attributes['portion_size']) }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Description & Notes -->
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Description</h3>
            <p class="text-gray-700 dark:text-gray-300">{{ $item->description }}</p>
            
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4 mb-2">Additional Notes</h3>
            <p class="text-gray-700 dark:text-gray-300">{{ $item->additional_notes ?? 'N/A' }}</p>
        </div>

        <!-- Availability -->
        @if($item->is_menu_item)
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Availability</h3>
            <div class="space-y-3">
                @if(isset($item->attributes['available_from']))
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Available From</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['available_from'] }}</p>
                </div>
                @endif
                @if(isset($item->attributes['available_to']))
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Available To</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['available_to'] }}</p>
                </div>
                @endif
                @if(isset($item->attributes['available_days']))
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Available Days</p>
                    <p class="text-gray-900 dark:text-white">{{ str_replace(',', ', ', $item->attributes['available_days']) }}</p>
                </div>
                @endif
                @if(isset($item->attributes['promotions']))
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Promotions</p>
                    <p class="text-gray-900 dark:text-white">{{ $item->attributes['promotions'] ? 'Active' : 'None' }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Attributes -->
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg md:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Additional Attributes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($item->attributes as $key => $value)
                    @if(!in_array($key, ['img', 'discounts', 'prep_time', 'promotions', 'available_from', 'available_to', 'available_days', 'portion_size']))
                    <div class="bg-white dark:bg-gray-600 p-3 rounded">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $key) }}</p>
                        @if(is_array($value))
                            <p class="text-gray-900 dark:text-white">{{ implode(', ', $value) }}</p>
                        @else
                            <p class="text-gray-900 dark:text-white">{{ $value }}</p>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-between">
        <a href="{{ route('admin.inventory.items.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back to List</a>
        <div class="space-x-2">
            <a href="{{ route('admin.inventory.items.edit', $item->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Edit</a>
            <form action="{{ route('admin.inventory.items.destroy', $item->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection