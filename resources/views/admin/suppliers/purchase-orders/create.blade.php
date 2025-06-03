@extends('layouts.admin')

@section('title', 'Create Purchase Order')

@section('content')
<div class="p-4 rounded-lg">
    <x-nav-buttons :items=" [
        ['name' => 'Suppliers Management', 'link' => route('admin.suppliers.index')],
        ['name' => 'Purchase Orders', 'link' => route('admin.purchase-orders.index')],
        ['name' => 'Supplier GRNs', 'link' => route('admin.grn.index')],
        ['name' => 'Supplier Payments', 'link' => route('admin.payments.index')],
    ]" active="Purchase Orders" />

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 mt-4">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Create Purchase Order</h2>
            <p class="text-sm text-gray-500">Enter purchase order details below</p>
        </div>

        <form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="poForm">
            @csrf

            <!-- Branch & Supplier -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">Select Branch <span class="text-red-600">*</span></label>
                    <select name="branch_id" id="branch_id" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500">
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Select Supplier <span class="text-red-600">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500">
                        <option value="">-- Select Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="order_date" class="block text-sm font-medium text-gray-700">Order Date <span class="text-red-600">*</span></label>
                    <input type="date" name="order_date" id="order_date" value="{{ old('order_date', date('Y-m-d')) }}"
                           class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500">
                    @error('order_date') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700">Expected Delivery Date <span class="text-red-600">*</span></label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" value="{{ old('expected_delivery_date') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500">
                    @error('expected_delivery_date') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- Items Table -->
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-2">Items <span class="text-red-600">*</span></h2>
                <div class="overflow-x-auto">
                    <table class="w-full" id="itemsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Buying Price</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Line Total</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(old('items'))
                                @foreach(old('items') as $i => $item)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <select name="items[{{ $i }}][item_code]" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500">
                                                <option value="">Select Item</option>
                                                @foreach($items as $itm)
                                                    <option value="{{ $itm->item_code }}" {{ old('items.'.$i.'.item_code') == $itm->item_code ? 'selected' : '' }}>
                                                        {{ $itm->item_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-2"><input type="number" name="items[{{ $i }}][quantity]" value="{{ old('items.'.$i.'.quantity') }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 qty-input"></td>
                                        <td class="px-4 py-2"><input type="number" step="0.01" name="items[{{ $i }}][buying_price]" value="{{ old('items.'.$i.'.buying_price') }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 price-input"></td>
                                        <td class="px-4 py-2 line-total">{{ (old('items.'.$i.'.quantity') * old('items.'.$i.'.buying_price')) }}</td>
                                        <td class="px-4 py-2"><button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-2">
                                        <select name="items[0][item_code]" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500">
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->item_code }}">{{ $item->item_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2"><input type="number" name="items[0][quantity]" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 qty-input"></td>
                                    <td class="px-4 py-2"><input type="number" step="0.01" name="items[0][buying_price]" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 price-input"></td>
                                    <td class="px-4 py-2 line-total">0.00</td>
                                    <td class="px-4 py-2"><button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" onclick="addRow()" class="mt-2 text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-plus mr-1"></i> Add Item
                </button>
                @error('items') <small class="text-red-600 block mt-1">{{ $message }}</small> @enderror
            </div>

            <!-- Notes -->
            <div class="mt-4">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit -->
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> Create Purchase Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let rowIndex = {{ old('items') ? count(old('items')) : 1 }};

function addRow() {
    const table = document.querySelector('#itemsTable tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="px-4 py-2">
            <select name="items[${rowIndex}][item_code]" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500">
                <option value="">Select Item</option>
                @foreach($items as $item)
                    <option value="{{ $item->item_code }}">{{ $item->item_name }}</option>
                @endforeach
            </select>
        </td>
        <td class="px-4 py-2"><input type="number" name="items[${rowIndex}][quantity]" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 qty-input"></td>
        <td class="px-4 py-2"><input type="number" step="0.01" name="items[${rowIndex}][buying_price]" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 price-input"></td>
        <td class="px-4 py-2 line-total">0.00</td>
        <td class="px-4 py-2"><button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button></td>
    `;
    table.appendChild(row);
    rowIndex++;
}

function removeRow(button) {
    const row = button.closest('tr');
    row.remove();
}

document.addEventListener('input', function () {
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        row.querySelector('.line-total').textContent = (qty * price).toFixed(2);
    });
});
</script>
@endpush
