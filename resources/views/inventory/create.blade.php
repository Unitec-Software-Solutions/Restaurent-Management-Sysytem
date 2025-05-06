@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Inventory Item</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Initial Stock Location</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="stock_type" id="single_branch" value="single" checked>
                                    <label class="form-check-label" for="single_branch">Single Branch</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="stock_type" id="multiple_branches" value="multiple">
                                    <label class="form-check-label" for="multiple_branches">Multiple Branches</label>
                                </div>
                            </div>

                            <div class="col-md-6" id="single_branch_select">
                                <label for="branch" class="form-label">Select Branch</label>
                                <select class="form-select" name="branch_id" id="branch">
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6" id="multiple_branches_select" style="display: none;">
                                <label for="branches" class="form-label">Select Branches</label>
                                <select class="form-select" name="branch_ids[]" id="branches" multiple>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Item Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                        id="sku" name="sku" value="{{ old('sku') }}" required>
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select @error('inventory_category_id') is-invalid @enderror" 
                                id="category" name="inventory_category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('inventory_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('inventory_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit_of_measurement" class="form-label">Unit of Measurement</label>
                                    <select class="form-select" id="unit_of_measurement" name="unit_of_measurement" required>
                                        <option value="kg">Kilogram (kg)</option>
                                        <option value="g">Gram (g)</option>
                                        <option value="ltr">Liter (ltr)</option>
                                        <option value="ml">Milliliter (ml)</option>
                                        <option value="pcs">Pieces (pcs)</option>
                                        <option value="box">Box</option>
                                        <option value="pack">Pack</option>
                                        <option value="bottle">Bottle</option>
                                        <option value="can">Can</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="initial_quantity" class="form-label">Initial Quantity</label>
                                    <input type="number" step="0.001" class="form-control" 
                                        id="initial_quantity" name="initial_quantity" value="{{ old('initial_quantity', 0) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_purchase_price" class="form-label">Purchase Price</label>
                                    <input type="number" step="0.01" class="form-control" 
                                        id="default_purchase_price" name="default_purchase_price" 
                                        value="{{ old('default_purchase_price') }}" required>
                                    <div id="calculated_value" class="form-text mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_purchase_price" class="form-label">Purchase Price</label>
                                    <input type="number" step="0.01" class="form-control" 
                                        id="default_purchase_price" name="default_purchase_price" 
                                        value="{{ old('default_purchase_price') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="selling_price" class="form-label">Selling Price</label>
                                    <input type="number" step="0.01" class="form-control" 
                                        id="selling_price" name="selling_price" 
                                        value="{{ old('selling_price') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reorder_level" class="form-label">Reorder Level</label>
                                    <input type="number" step="0.001" class="form-control" 
                                        id="reorder_level" name="reorder_level" value="{{ old('reorder_level') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="minimum_quantity" class="form-label">Minimum Quantity</label>
                                    <input type="number" step="0.001" class="form-control" 
                                        id="minimum_quantity" name="minimum_quantity" value="{{ old('minimum_quantity') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_perishable" 
                                        name="is_perishable" value="1" {{ old('is_perishable') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_perishable">Is Perishable</label>
                                </div>
                            </div>
                        </div>

                        <div id="perishable_settings" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="shelf_life_days" class="form-label">Shelf Life (Days)</label>
                                        <input type="number" class="form-control" id="shelf_life_days" 
                                            name="shelf_life_days" value="{{ old('shelf_life_days') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="expiry_date" class="form-label">Initial Stock Expiry Date</label>
                                        <input type="date" class="form-control" id="expiry_date" 
                                            name="expiry_date" value="{{ old('expiry_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Branch selection toggle
    const stockType = document.getElementsByName('stock_type');
    const singleBranchSelect = document.getElementById('single_branch_select');
    const multipleBranchesSelect = document.getElementById('multiple_branches_select');

    function toggleBranchSelection() {
        if (document.getElementById('single_branch').checked) {
            singleBranchSelect.style.display = '';
            multipleBranchesSelect.style.display = 'none';
        } else {
            singleBranchSelect.style.display = 'none';
            multipleBranchesSelect.style.display = '';
        }
    }

    stockType.forEach(radio => radio.addEventListener('change', toggleBranchSelection));

    // Perishable settings toggle
    const isPerishable = document.getElementById('is_perishable');
    const perishableSettings = document.getElementById('perishable_settings');
    const shelfLifeDays = document.getElementById('shelf_life_days');
    const expiryDate = document.getElementById('expiry_date');

    function togglePerishableSettings() {
        perishableSettings.style.display = isPerishable.checked ? '' : 'none';
        if (isPerishable.checked) {
            shelfLifeDays.setAttribute('required', 'required');
            expiryDate.setAttribute('required', 'required');
        } else {
            shelfLifeDays.removeAttribute('required');
            expiryDate.removeAttribute('required');
        }
    }

    isPerishable.addEventListener('change', togglePerishableSettings);
    togglePerishableSettings();

    // Calculate margin percentage
    const sellingPrice = document.getElementById('selling_price');
    const purchasePrice = document.getElementById('default_purchase_price');
    const initialQuantity = document.getElementById('initial_quantity');
    const marginDisplay = document.createElement('small');
    marginDisplay.classList.add('text-muted', 'ms-2');
    sellingPrice.parentElement.appendChild(marginDisplay);

    function updateMargin() {
        const sp = parseFloat(sellingPrice.value) || 0;
        const pp = parseFloat(purchasePrice.value) || 0;
        if (pp > 0 && sp > 0) {
            const margin = ((sp - pp) / pp) * 100;
            marginDisplay.textContent = `Margin: ${margin.toFixed(1)}%`;
            marginDisplay.classList.remove('text-danger', 'text-success');
            marginDisplay.classList.add(margin >= 20 ? 'text-success' : 'text-danger');
        } else {
            marginDisplay.textContent = '';
        }
    }

    sellingPrice.addEventListener('input', updateMargin);
    purchasePrice.addEventListener('input', updateMargin);

    // Calculate and display total value
    const calculatedValue = document.getElementById('calculated_value');
    function updateValue() {
        const qty = parseFloat(initialQuantity.value) || 0;
        const pp = parseFloat(purchasePrice.value) || 0;
        if (qty > 0 && pp > 0) {
            calculatedValue.textContent = `Total Value: $${(qty * pp).toFixed(2)}`;
        } else {
            calculatedValue.textContent = '';
        }
    }
    initialQuantity.addEventListener('input', updateValue);
    purchasePrice.addEventListener('input', updateValue);
    updateValue();
});
</script>
@endpush