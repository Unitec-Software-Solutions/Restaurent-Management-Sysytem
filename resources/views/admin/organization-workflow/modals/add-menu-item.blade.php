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
            
            <!-- Menu Item Type Selection -->
            <div class="bg-light border-bottom p-3">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-2"><i class="fas fa-info-circle text-blue"></i> Menu Item Types</h6>
                        <div class="small text-muted">
                            <div><strong>Buy & Sell:</strong> Items from inventory (beverages, packaged foods)</div>
                            <div><strong>KOT Recipe:</strong> Dishes made by cooking ingredients</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">Item Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="menu_item_type" name="item_type" required onchange="toggleMenuItemFields()">
                            <option value="">Select Type</option>
                            <option value="buy_sell">Buy & Sell Item (From Inventory)</option>
                            <option value="kot_recipe">KOT Recipe (Kitchen Prepared)</option>
                        </select>
                    </div>
                </div>
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
function toggleMenuItemFields() {
    const itemType = document.getElementById('menu_item_type').value;
    const buysellFields = document.getElementById('buy_sell_fields');
    const kotFields = document.getElementById('kot_recipe_fields');
    
    if (itemType === 'buy_sell') {
        buysellFields.style.display = 'block';
        kotFields.style.display = 'none';
        document.getElementById('preparation_time').value = '0';
        document.getElementById('preparation_time').disabled = true;
    } else if (itemType === 'kot_recipe') {
        buyellFields.style.display = 'none';
        kotFields.style.display = 'block';
        document.getElementById('preparation_time').disabled = false;
        document.getElementById('preparation_time').value = '15';
    } else {
        buyellFields.style.display = 'none';
        kotFields.style.display = 'none';
    }
}

function submitMenuItemForm(event) {
    event.preventDefault();
    
    const itemType = document.getElementById('menu_item_type').value;
    if (!itemType) {
        alert('Please select a menu item type');
        return;
    }
    
    const formData = new FormData(event.target);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Add type information
    data.item_type = itemType;
    
    // Determine the appropriate endpoint based on type
    let endpoint;
    if (itemType === 'buy_sell') {
        endpoint = '/admin/menu-items/create-from-item-master';
    } else {
        endpoint = '/admin/menu-items/create-kot';
    }
    
    fetch(endpoint, {
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

function toggleMenuItemFields() {
    const itemType = document.getElementById('menu_item_type').value;
    const kotFields = document.querySelectorAll('.kot-recipe-field');
    const buySellFields = document.querySelectorAll('.buy-sell-field');
    
    if (itemType === 'kot_recipe') {
        kotFields.forEach(field => field.style.display = 'block');
        buySellFields.forEach(field => field.style.display = 'none');
    } else if (itemType === 'buy_sell') {
        kotFields.forEach(field => field.style.display = 'none');
        buySellFields.forEach(field => field.style.display = 'block');
    } else {
        kotFields.forEach(field => field.style.display = 'none');
        buySellFields.forEach(field => field.style.display = 'none');
    }
}
</script>
