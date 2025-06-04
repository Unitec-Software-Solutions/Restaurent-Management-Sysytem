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

        <form action="{{ route('admin.purchase-orders.store') }}" method="POST">
            @csrf

            <!-- Supplier Selection -->
            <div class="form-group mb-4">
                <label for="supplier_id" class="form-label">Supplier *</label>
                <select name="supplier_id" id="supplier_id" class="form-control" required>
                    <option value="">Select Supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>

            </div>



            <!-- Branch Selection -->
            <div class="form-group mb-4">
                <label for="branch_id" class="form-label">Branch *</label>
                <select name="branch_id" id="branch_id" class="form-control" required>
                    <option value="">Select Branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Dates -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="order_date" class="form-label">Order Date *</label>
                        <input type="date" name="order_date" id="order_date" class="form-control"
                            value="{{ old('order_date', date('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expected_delivery_date" class="form-label">Expected Delivery Date *</label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control"
                            value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
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
                        @php
                            $oldItems = old('items', [['item_id' => '', 'quantity' => '', 'buying_price' => '']]);
                        @endphp

                        @foreach ($oldItems as $index => $item)
                            <tr data-index="{{ $index }}">
                                <td>
                                    <select name="items[{{ $index }}][item_id]" class="form-control item-select"
                                        required>
                                        <option value="">Select Item</option>
                                        @foreach ($items as $itemOption)
                                            <option value="{{ $itemOption->id }}"
                                                {{ $item['item_id'] == $itemOption->id ? 'selected' : '' }}>
                                                {{ $itemOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]"
                                        class="form-control item-quantity" min="0.01" step="0.01"
                                        value="{{ $item['quantity'] }}" required>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][buying_price]"
                                        class="form-control item-price" min="0" step="0.01"
                                        value="{{ $item['buying_price'] }}" required>
                                </td>
                                <td>
                                    <span class="line-total">0.00</span>
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
                            <th id="grand-total">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes -->
            <div class="form-group mb-4">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-control" maxlength="500">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-2"></i> Create Purchase Order
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const itemsTable = document.getElementById('items-table');
                let itemCount = {{ count($oldItems) }};

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
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
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

                    updateGrandTotal();
                }

                // Remove row
                function removeRow(button) {
                    const row = button.closest('tr');
                    row.remove();
                    updateGrandTotal();
                }

                // Add event listeners
                document.getElementById('items-table').addEventListener('click', function(e) {
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

                // Initialize calculations
                updateGrandTotal();

                // Set min date for expected delivery
                document.getElementById('order_date').addEventListener('change', function() {
                    const expectedDate = document.getElementById('expected_delivery_date');
                    expectedDate.min = this.value;
                    if (new Date(expectedDate.value) < new Date(this.value)) {
                        expectedDate.value = this.value;
                    }
                });
            });
        </script>
    @endpush
@endsection
