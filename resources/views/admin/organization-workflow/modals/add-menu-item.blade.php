<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-success"></i> Add Menu Item
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addMenuItemForm" onsubmit="submitMenuItemForm(event)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="menu_item_name">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="menu_item_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="menu_item_category">Category <span class="text-danger">*</span></label>
                                <select class="form-control" id="menu_item_category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="appetizer">Appetizers</option>
                                    <option value="main">Main Courses</option>
                                    <option value="dessert">Desserts</option>
                                    <option value="drink">Beverages</option>
                                    <option value="special">Specials</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="menu_item_description">Description</label>
                        <textarea class="form-control" id="menu_item_description" name="description" rows="3" placeholder="Describe the menu item"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="menu_item_price">Price <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="menu_item_price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="preparation_time">Preparation Time (minutes)</label>
                                <input type="number" class="form-control" id="preparation_time" name="preparation_time" min="1" value="15">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="menu_item_status">Status</label>
                                <select class="form-control" id="menu_item_status" name="status">
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="seasonal">Seasonal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_vegetarian" name="is_vegetarian" value="1">
                                    <label class="form-check-label" for="is_vegetarian">
                                        Vegetarian
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_vegan" name="is_vegan" value="1">
                                    <label class="form-check-label" for="is_vegan">
                                        Vegan
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="allergens">Allergens</label>
                        <input type="text" class="form-control" id="allergens" name="allergens" placeholder="e.g., Contains nuts, dairy, gluten">
                    </div>
                    
                    <div class="form-group">
                        <label for="ingredients">Main Ingredients</label>
                        <textarea class="form-control" id="ingredients" name="ingredients" rows="2" placeholder="List main ingredients"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Add Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function submitMenuItemForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('/admin/organizations/{{ $organization->id ?? "0" }}/menu-items', {
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
            $('#addMenuItemModal').modal('hide');
            // Reset form
            document.getElementById('addMenuItemForm').reset();
            // Show success message
            alert('Menu item added successfully!');
            // Optionally reload page or update UI
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to add menu item'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add menu item');
    });
}
</script>
