<!-- Add Inventory Item Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-primary"></i> Add Inventory Item
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addInventoryForm" onsubmit="submitInventoryForm(event)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_name">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="item_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_category">Category <span class="text-danger">*</span></label>
                                <select class="form-control" id="item_category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="food">Food Items</option>
                                    <option value="beverage">Beverages</option>
                                    <option value="supplies">Kitchen Supplies</option>
                                    <option value="cleaning">Cleaning Supplies</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="current_stock">Current Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="current_stock" name="current_stock" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="min_stock">Minimum Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="min_stock" name="min_stock" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <select class="form-control" id="unit" name="unit">
                                    <option value="kg">Kilograms (kg)</option>
                                    <option value="g">Grams (g)</option>
                                    <option value="l">Liters (l)</option>
                                    <option value="ml">Milliliters (ml)</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="plate">Plate (plate)</option>
                                    <option value="bottle">Bottle (bottle)</option>
                                    <option value="packet">Packet (packet)</option>
                                    <option value="box">Box (box)</option>
                                    <option value="pack">Pack (pack)</option>
                                    <option value="dozen">Dozen (dozen)</option>
                                    <option value="carton">Carton (carton)</option>
                                    <option value="roll">Roll (roll)</option>
                                    <option value="bundle">Bundle (bundle)</option>
                                    <option value="sachet">Sachet (sachet)</option>
                                    <option value="barrel">Barrel (barrel)</option>
                                    <option value="jar">Jar (jar)</option>
                                    <option value="tube">Tube (tube)</option>
                                    <option value="tray">Tray (tray)</option>
                                    <option value="case">Case (case)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cost_price">Cost Price</label>
                                <input type="number" class="form-control" id="cost_price" name="cost_price" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" placeholder="Enter supplier name">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter item description (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function submitInventoryForm(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    fetch('/admin/organizations/{{ $organization->id ?? "0" }}/inventory', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            $('#addInventoryModal').modal('hide');
            // Reset form
            document.getElementById('addInventoryForm').reset();
            // Show success message
            alert('Inventory item added successfully!');
            // Optionally reload page or update UI
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to add inventory item'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add inventory item');
    });
}
</script>
