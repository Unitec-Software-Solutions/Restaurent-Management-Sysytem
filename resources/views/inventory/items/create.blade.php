@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white">Add New Inventory Item</h2>
    <form method="POST" action="{{ route('inventory.items.store') }}">
        @csrf

        <!-- === Item Details === -->
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Item Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Name*</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- SKU -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU*</label>
                <input type="text" name="sku" value="{{ old('sku') }}" required 
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category*</label>
                <select name="inventory_category_id" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('inventory_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Size/Variant -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Size/Variant</label>
                <input type="text" name="size_variant" value="{{ old('size_variant') }}" 
                       placeholder="e.g., 500ml, 1L, Large, Small"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Unit of Measurement -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measurement*</label>
                <select name="unit_of_measurement" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Select Unit</option>
                    <option value="piece" {{ old('unit_of_measurement') == 'piece' ? 'selected' : '' }}>Piece</option>
                    <option value="kg" {{ old('unit_of_measurement') == 'kg' ? 'selected' : '' }}>Kilogram</option>
                    <option value="g" {{ old('unit_of_measurement') == 'g' ? 'selected' : '' }}>Gram</option>
                    <option value="L" {{ old('unit_of_measurement') == 'L' ? 'selected' : '' }}>Liter</option>
                    <option value="ml" {{ old('unit_of_measurement') == 'ml' ? 'selected' : '' }}>Milliliter</option>
                    <option value="box" {{ old('unit_of_measurement') == 'box' ? 'selected' : '' }}>Box</option>
                    <option value="pack" {{ old('unit_of_measurement') == 'pack' ? 'selected' : '' }}>Pack</option>
                </select>
            </div>

            <!-- Reorder Level -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reorder Level*</label>
                <input type="number" step="0.001" name="reorder_level" value="{{ old('reorder_level') }}" required 
                placeholder="Enter minimum stock level" class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Show in Menu -->
            <div class="flex items-center">
                <input type="checkbox" name="show_in_menu" {{ old('show_in_menu') ? 'checked' : '' }} 
                       class="rounded dark:bg-gray-700">
                <span class="ml-2 text-gray-700 dark:text-gray-300">Show in Menu</span>
            </div>
        </div>

        <!-- === Pricing Information === -->
        <hr class="my-6 border-gray-300 dark:border-gray-600">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Pricing Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Purchase Price -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase Price*</label>
                <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price') }}" required 
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Selling Price -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling Price*</label>
                <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required 
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <!-- === Perishability Options === -->
        <hr class="my-6 border-gray-300 dark:border-gray-600">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Perishability & Storage</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Is Perishable -->
            <div class="flex items-center">
                <input type="checkbox" name="is_perishable" id="is_perishable" {{ old('is_perishable') ? 'checked' : '' }} 
                       class="rounded dark:bg-gray-700">
                <span class="ml-2 text-gray-700 dark:text-gray-300">Is Perishable?</span>
            </div>

            <!-- Storage Method -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Storage Method</label>
                <select name="storage_method" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Select Storage Method</option>
                    <option value="room_temp" {{ old('storage_method') == 'room_temp' ? 'selected' : '' }}>Room Temperature</option>
                    <option value="refrigerated" {{ old('storage_method') == 'refrigerated' ? 'selected' : '' }}>Refrigerated</option>
                    <option value="frozen" {{ old('storage_method') == 'frozen' ? 'selected' : '' }}>Frozen</option>
                    <option value="dry" {{ old('storage_method') == 'dry' ? 'selected' : '' }}>Dry Storage</option>
                </select>
            </div>

            <!-- Shelf Life -->
            <div id="shelf_life_wrapper" style="{{ old('is_perishable') ? '' : 'display:none;' }}">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shelf Life (days)</label>
                <input type="number" name="shelf_life_days" value="{{ old('shelf_life_days') }}" 
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Expiry Date -->
            <div id="expiry_date_wrapper" style="{{ old('is_perishable') ? '' : 'display:none;' }}">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" 
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <!-- === Initial Inventory === -->
        {{-- 
        
        <hr class="my-6 border-gray-300 dark:border-gray-600">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Initial Inventory</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Branch -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch*</label>
                <select name="branch_id" required 
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Transaction Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type*</label>
                <select name="transaction_type" required 
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="purchase" {{ old('transaction_type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="transfer_in" {{ old('transaction_type') == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                    <option value="adjustment" {{ old('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>
            </div>

            <!-- Initial Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Initial Quantity*</label>
                <input type="number" step="0.001" name="quantity" value="{{ old('quantity') }}" required 
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Supplier -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                <select name="supplier_id" 
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
        </div>

        --}}
        
        <!-- === Additional Information === -->
        <hr class="my-6 border-gray-300 dark:border-gray-600">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Additional Information</h3>

        <div class="grid grid-cols-1 gap-4">
            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" rows="3" 
                          class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 flex justify-end space-x-4">
            <a href="{{ route('inventory.items.store') }}" 
               class="px-5 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Save Item
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('is_perishable').addEventListener('change', function () {
        const show = this.checked;
        document.getElementById('shelf_life_wrapper').style.display = show ? 'block' : 'none';
        document.getElementById('expiry_date_wrapper').style.display = show ? 'block' : 'none';
    });
</script>
@endsection