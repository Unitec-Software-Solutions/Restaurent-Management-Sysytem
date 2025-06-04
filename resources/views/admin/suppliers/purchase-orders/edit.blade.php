@extends('layouts.admin')

@section('content')
    <div class="container">
        <?php
        if ($errors->any()) {
            echo '<div class="alert alert-danger">
                            <ul>';
            foreach ($errors->all() as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '  </ul>
                          </div>';
        }
        ?>

        <form action="{{ route('admin.purchase-orders.update', $purchaseOrder->po_id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Supplier Selection -->
            <div class="form-group mb-4">
                <label for="supplier_id" class="form-label">Supplier *</label>
                <select name="supplier_id" id="supplier_id" class="form-control" required>
                    <option value="">Select Supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" 
                            {{ $purchaseOrder->supplier_id == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Branch Information (Display only, can't change branch) -->
            <div class="form-group mb-4">
                <label class="form-label">Branch</label>
                <input type="text" class="form-control" value="{{ $purchaseOrder->branch->name }}" readonly>
                <input type="hidden" name="branch_id" value="{{ $purchaseOrder->branch_id }}">
            </div>

            <!-- Dates -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Order Date</label>
                        <input type="text" class="form-control" 
                               value="{{ $purchaseOrder->order_date->format('Y-m-d') }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expected_delivery_date" class="form-label">Expected Delivery Date *</label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control"
                            value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date->format('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="form-group mb-4">
                <label class="form-label">Items *</label>
                <table class="table table-bordered" id="items-table">
                    <thead class="table-light">
                        <tr>
                            <th width="40%">Item *</th>
                            <th width="20%">Quantity *</th>
                            <th width="20%">Buying Price *</th>
                            <th width="20%">Total</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $index => $item)
                            <tr data-index="{{ $index }}">
                                <td>
                                    <select name="items[{{ $index }}][item_id]" class="form-control item-select" required>
                                        <option value="">Select Item</option>
                                        @foreach ($items as $itemOption)
                                            <option value="{{ $itemOption->id }}"
                                                {{ $item->item_id == $itemOption->id ? 'selected' : '' }}
                                                data-price="{{ $itemOption->buying_price }}">
                                                {{ $itemOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[{{ $index }}][po_detail_id]" value="{{ $item->po_detail_id }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]"
                                        class="form-control item-quantity" min="0.01" step="0.01"
                                        value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" required>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][buying_price]"
                                        class="form-control item-price" min="0" step="0.01"
                                        value="{{ old('items.'.$index.'.buying_price', $item->buying_price) }}" required>
                                </td>
                                <td>
                                    <span class="line-total">{{ number_format($item->line_total, 2) }}</span>
                                </td>
                                <td>
                                    @if ($index === 0)
                                        <button type="button" class="btn btn-success add-row">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-danger remove-row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Grand Total:</th>
                            <th id="grand-total">{{ number_format($purchaseOrder->total_amount, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes -->
            <div class="form-group mb-4">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-control" maxlength="500">{{ old('notes', $purchaseOrder->notes) }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.purchase-orders.show', $purchaseOrder->po_id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Update Purchase Order
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const itemsTable = document.getElementById('items-table');
                let itemCount = {{ count($purchaseOrder->items) }};

                // Function to calculate line total
                function calculateLineTotal(row) {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const total = quantity * price;
                    row.querySelector('.line-total').textContent = total.toFixed(2);
                    return total;
                }

                // Function to update grand total
                function updateGrandTotal() {
                    let grandTotal = 0;
                    document.querySelectorAll('#items-table tbody tr').forEach(row => {
                        grandTotal += calculateLineTotal(row);
                    });
                    document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
                }

                // Add new item row
                function addItemRow() {
                    const newRow = document.createElement('tr');
                    newRow.dataset.index = itemCount;
                    newRow.innerHTML = `
                        <td>
                            <select name="items[${itemCount}][item_id]" class="form-control item-select" required>
                                <option value="">Select Item</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}" data-price="{{ $item->buying_price }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="items[${itemCount}][po_detail_id]" value="">
                        </td>
                        <td>
                            <input type="number" name="items[${itemCount}][quantity]" 
                                   class="form-control item-quantity" min="0.01" step="0.01" required>
                        </td>
                        <td>
                            <input type="number" name="items[${itemCount}][buying_price]" 
                                   class="form-control item-price" min="0" step="0.01" required>
                        </td>
                        <td>
                            <span class="line-total">0.00</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    itemsTable.querySelector('tbody').appendChild(newRow);
                    itemCount++;

                    // Add event listeners to new inputs
                    newRow.querySelector('.item-quantity').addEventListener('input', updateGrandTotal);
                    newRow.querySelector('.item-price').addEventListener('input', updateGrandTotal);
                    
                    // Update price when item selection changes
                    newRow.querySelector('.item-select').addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const priceInput = this.closest('tr').querySelector('.item-price');
                        priceInput.value = selectedOption.dataset.price || 0;
                        updateGrandTotal();
                    });

                    updateGrandTotal();
                }

                // Remove row
                function removeRow(button) {
                    const row = button.closest('tr');
                    if (confirm('Are you sure you want to remove this item?')) {
                        row.remove();
                        updateGrandTotal();
                    }
                }

                // Add event listeners
                itemsTable.addEventListener('click', function(e) {
                    if (e.target.closest('.add-row')) {
                        addItemRow();
                    } else if (e.target.closest('.remove-row')) {
                        removeRow(e.target);
                    }
                });

                // Add event listeners to existing inputs
                document.querySelectorAll('.item-quantity, .item-price').forEach(input => {
                    input.addEventListener('input', updateGrandTotal);
                });

                // Update price when item selection changes
                document.querySelectorAll('.item-select').forEach(select => {
                    select.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const priceInput = this.closest('tr').querySelector('.item-price');
                        priceInput.value = selectedOption.dataset.price || 0;
                        updateGrandTotal();
                    });
                });

                // Initialize calculations
                updateGrandTotal();
            });
        </script>
    @endpush
@endsection