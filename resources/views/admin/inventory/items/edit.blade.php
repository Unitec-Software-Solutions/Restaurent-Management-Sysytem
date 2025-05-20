@extends('layouts.main')

@section('content')
<div class="">
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Edit Item: {{ $item->name }}</h1>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-800 dark:border-red-600 dark:text-red-100">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.inventory.items.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Item Name</label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Unicode Name</label>
                    <input type="text" name="unicode_name" value="{{ old('unicode_name', $item->unicode_name) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Item Code</label>
                    <input type="text" name="item_code" value="{{ old('item_code', $item->item_code) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Category</label>
                    <select name="item_category_id"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                   focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $item->item_category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Unit of Measurement</label>
                    <input type="text" name="unit_of_measurement" value="{{ old('unit_of_measurement', $item->unit_of_measurement) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Reorder Level</label>
                    <input type="number" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Shelf Life (days)</label>
                    <input type="number" name="shelf_life_in_days" value="{{ old('shelf_life_in_days', $item->shelf_life_in_days) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Cost Price</label>
                    <input type="number" step="0.01" name="buying_price" value="{{ old('buying_price', $item->buying_price) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Selling Price</label>
                    <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $item->selling_price) }}"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                  dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="is_perishable" value="1" {{ $item->is_perishable ? 'checked' : '' }}>
                    <label class="text-sm text-gray-900 dark:text-white">Perishable</label>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="is_menu_item" value="1" {{ $item->is_menu_item ? 'checked' : '' }}>
                    <label class="text-sm text-gray-900 dark:text-white">Menu Item</label>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                     dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description', $item->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Additional Notes</label>
                    <textarea name="additional_notes" rows="2"
                              class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 
                                     dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('additional_notes', $item->additional_notes) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('admin.inventory.items.index') }}" 
                   class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 
                          dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">
                    Cancel
                </a>
                <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-2.5 
                               dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    Update Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
