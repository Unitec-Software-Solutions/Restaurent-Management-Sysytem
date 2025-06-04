{{-- resources/views/admin/suppliers/grn/edit.blade.php --}}
@extends('layouts.admin')

@section('header-title', 'Edit GRN')
@section('content')
    <div class="p-4 rounded-lg">
        <x-nav-buttons :items="[
            ['name' => 'GRN Dashboard', 'link' => route('admin.grn.index')],
            ['name' => 'View GRN', 'link' => route('admin.grn.show', $grn->grn_id)],
            ['name' => 'Edit GRN', 'link' => route('admin.grn.edit', $grn->grn_id)],
        ]" active="Edit GRN" />

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('admin.grn.update', $grn->grn_id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Header Section -->
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Edit GRN: {{ $grn->grn_number }}</h2>
                    <div class="flex space-x-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Update GRN
                        </button>
                    </div>
                </div>

                <!-- Basic Information Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- GRN Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GRN Number</label>
                        <input type="text" value="{{ $grn->grn_number }}" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
                    </div>

                    <!-- Branch -->
                    <div>
                        <label for="branch_id"  class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <select id="branch_id" name="branch_id" class="w-full px-4 py-2 border rounded-lg" required>
                            <option value="">Select Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $grn->branch_id == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <select id="supplier_id" name="supplier_id" class="w-full px-4 py-2 border rounded-lg" required>
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $grn->supplier_id == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Dates Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Received Date -->
                    <div>
                        <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Received Date *</label>
                        <input type="date" id="received_date" name="received_date" value="{{ $grn->received_date->format('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg" required>
                    </div>

                    <!-- Delivery Note Number -->
                    <div>
                        <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note Number</label>
                        <input type="text" id="delivery_note_number" name="delivery_note_number" value="{{ $grn->delivery_note_number }}" class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <!-- Invoice Number -->
                    <div>
                        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number" value="{{ $grn->invoice_number }}" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Received Items</h3>
                        <button type="button" id="addItemBtn" class="px-3 py-1 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch No</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered Qty</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Qty</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accepted Qty</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected Qty</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buying Price</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MFG Date</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EXP Date</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejection Reason</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="itemsContainer">
                                @foreach ($grn->items as $item)
                                    <tr class="item-row">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <select name="items[{{ $loop->index }}][item_id]" class="w-full border rounded item-select" required>
                                                <option value="">Select Item</option>
                                                @foreach ($items as $itemOption) {{-- FIXED: Use $items instead of $grn->items --}}
                                                    <option value="{{ $itemOption->id }}" 
                                                        data-code="{{ $itemOption->item_code }}"
                                                        data-price="{{ $itemOption->buying_price }}"
                                                        {{ $item->item_id == $itemOption->id ? 'selected' : '' }}>
                                                        {{ $itemOption->name }} ({{ $itemOption->item_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            {{-- FIXED: Add po_detail_id --}}
                                            <input type="hidden" name="items[{{ $loop->index }}][po_detail_id]" value="{{ $item->po_detail_id }}">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="text" name="items[{{ $loop->index }}][batch_no]" value="{{ $item->batch_no }}" class="w-full border rounded">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="number" step="0.01" name="items[{{ $loop->index }}][ordered_quantity]" value="{{ $item->ordered_quantity }}" class="w-full border rounded ordered-qty" required>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="number" step="0.01" name="items[{{ $loop->index }}][received_quantity]" value="{{ $item->received_quantity }}" class="w-full border rounded received-qty" required>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="number" step="0.01" name="items[{{ $loop->index }}][accepted_quantity]" value="{{ $item->accepted_quantity }}" class="w-full border rounded accepted-qty" required>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="number" step="0.01" name="items[{{ $loop->index }}][rejected_quantity]" value="{{ $item->rejected_quantity }}" class="w-full border rounded rejected-qty" required>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="number" step="0.01" name="items[{{ $loop->index }}][buying_price]" value="{{ $item->buying_price }}" class="w-full border rounded price" required>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap line-total">
                                            ${{ number_format($item->line_total, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="date" name="items[{{ $loop->index }}][manufacturing_date]" value="{{ $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : '' }}" class="w-full border rounded">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="date" name="items[{{ $loop->index }}][expiry_date]" value="{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}" class="w-full border rounded">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="text" name="items[{{ $loop->index }}][rejection_reason]" value="{{ $item->rejection_reason }}" class="w-full border rounded">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <button type="button" class="remove-item text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ $grn->notes }}</textarea>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCounter = {{ count($grn->items) }};
            
            // Add item row
            document.getElementById('addItemBtn').addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row';
                newRow.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap">
                        <select name="items[${itemCounter}][item_id]" class="w-full border rounded item-select" required>
                            <option value="">Select Item</option>
                            @foreach ($items as $itemOption)
                                <option value="{{ $itemOption->id }}" 
                                    data-code="{{ $itemOption->item_code }}"
                                    data-price="{{ $itemOption->buying_price }}">
                                    {{ $itemOption->name }} ({{ $itemOption->item_code }})
                                </option>
                            @endforeach
                        </select>
                        {{-- FIXED: Add po_detail_id --}}
                        <input type="hidden" name="items[${itemCounter}][po_detail_id]" value="">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="text" name="items[${itemCounter}][batch_no]" class="w-full border rounded">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" step="0.01" name="items[${itemCounter}][ordered_quantity]" class="w-full border rounded ordered-qty" required>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" step="0.01" name="items[${itemCounter}][received_quantity]" class="w-full border rounded received-qty" required>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" step="0.01" name="items[${itemCounter}][accepted_quantity]" class="w-full border rounded accepted-qty" required>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" step="0.01" name="items[${itemCounter}][rejected_quantity]" class="w-full border rounded rejected-qty" required>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" step="0.01" name="items[${itemCounter}][buying_price]" class="w-full border rounded price" required>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap line-total">
                        $0.00
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="date" name="items[${itemCounter}][manufacturing_date]" class="w-full border rounded">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="date" name="items[${itemCounter}][expiry_date]" class="w-full border rounded">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="text" name="items[${itemCounter}][rejection_reason]" class="w-full border rounded">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <button type="button" class="remove-item text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                document.getElementById('itemsContainer').appendChild(newRow);
                itemCounter++;
            });

            // Remove item row
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-item')) {
                    const row = e.target.closest('.item-row');
                    row.remove();
                }
            });

            // Item selection handler
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-select')) {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const row = e.target.closest('.item-row');
                    
                    if (selectedOption) {
                        // Set item code
                        row.querySelector('input[name$="[item_code]"]').value = selectedOption.dataset.code;
                        
                        // Set buying price
                        const priceInput = row.querySelector('.price');
                        priceInput.value = selectedOption.dataset.price || '';
                        
                        // Trigger calculation
                        calculateLineTotal(row);
                    }
                }
            });

            // Quantity/price change handler - FIXED: Add accepted_quantity
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('accepted-qty') || 
                    e.target.classList.contains('price') ||
                    e.target.classList.contains('received-qty')) {
                    
                    const row = e.target.closest('.item-row');
                    calculateLineTotal(row);
                }
            });

            // Calculate line total - FIXED: Use accepted_quantity
            function calculateLineTotal(row) {
                const qtyInput = row.querySelector('.accepted-qty');
                const priceInput = row.querySelector('.price');
                const lineTotalCell = row.querySelector('.line-total');
                
                if (qtyInput && priceInput) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    const total = qty * price;
                    
                    lineTotalCell.textContent = '$' + total.toFixed(2);
                }
            }

            // Initialize existing rows
            document.querySelectorAll('.item-row').forEach(row => {
                calculateLineTotal(row);
            });
        });
    </script>
@endpush