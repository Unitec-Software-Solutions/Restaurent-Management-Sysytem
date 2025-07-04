<!-- Create Menu Modal -->
<div class="modal fade" id="createMenuModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-list-alt text-success"></i> Create Menu
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createMenuForm" onsubmit="submitMenu(event)">
                <div class="modal-body">
                    <!-- Menu Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Menu Information</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="menu_name">Menu Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="menu_name" name="name" required placeholder="e.g., Lunch Menu, Dinner Menu">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="menu_branch">Branch <span class="text-danger">*</span></label>
                                <select class="form-control" id="menu_branch" name="branch_id" required>
                                    <option value="">Select Branch</option>
                                    @if(isset($organization))
                                        @foreach($organization->branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Schedule -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Menu Schedule</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="menu_start_time">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="menu_start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="menu_end_time">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="menu_end_time" name="end_time" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Active Days</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="monday" name="active_days[]" value="monday" checked>
                                            <label class="form-check-label" for="monday">Monday</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="tuesday" name="active_days[]" value="tuesday" checked>
                                            <label class="form-check-label" for="tuesday">Tuesday</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="wednesday" name="active_days[]" value="wednesday" checked>
                                            <label class="form-check-label" for="wednesday">Wednesday</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="thursday" name="active_days[]" value="thursday" checked>
                                            <label class="form-check-label" for="thursday">Thursday</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="friday" name="active_days[]" value="friday" checked>
                                            <label class="form-check-label" for="friday">Friday</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="saturday" name="active_days[]" value="saturday" checked>
                                            <label class="form-check-label" for="saturday">Saturday</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="sunday" name="active_days[]" value="sunday" checked>
                                            <label class="form-check-label" for="sunday">Sunday</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items Selection -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Menu Items Selection</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Available Menu Items</h6>
                                    <small class="text-muted">Select items to include in this menu</small>
                                </div>
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                    <div id="availableMenuItems">
                                        <div class="text-center">
                                            <i class="fas fa-utensils fa-2x text-muted"></i>
                                            <p class="mt-2 text-muted">Loading menu items...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Selected Items</h6>
                                    <small class="text-muted">Items included in this menu</small>
                                </div>
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                    <div id="selectedMenuItems">
                                        <p class="text-muted text-center">No items selected yet</p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">Selected: <span id="selectedCount">0</span> items</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Description -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="menu_description">Description</label>
                                <textarea class="form-control" id="menu_description" name="description" rows="2" placeholder="Brief description of this menu"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitMenuBtn" disabled>
                        <i class="fas fa-save"></i> Create Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedMenuItems = new Set();

// Load menu items when modal opens
$('#createMenuModal').on('shown.bs.modal', function () {
    loadOrganizationMenuItems();
});

// Set default times
document.getElementById('menu_start_time').value = '08:00';
document.getElementById('menu_end_time').value = '22:00';

function loadOrganizationMenuItems() {
    const container = document.getElementById('availableMenuItems');
    
    fetch('/admin/organizations/{{ $organization->id ?? "0" }}/menu-items')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.menu_items.length > 0) {
                displayAvailableMenuItems(data.menu_items);
            } else {
                container.innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        <p class="mt-2 text-muted">No menu items found.</p>
                        <button type="button" class="btn btn-sm btn-primary" onclick="createMenuItemFirst()">
                            <i class="fas fa-plus"></i> Create Menu Item First
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-danger text-center">Failed to load menu items</p>';
        });
}

function displayAvailableMenuItems(menuItems) {
    const container = document.getElementById('availableMenuItems');
    
    let html = '';
    menuItems.forEach(item => {
        html += `
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="item_${item.id}" value="${item.id}" onchange="toggleMenuItem(${item.id}, '${item.name.replace(/'/g, "\\'")}', ${item.price})">
                <label class="form-check-label" for="item_${item.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.name}</strong>
                            <br><small class="text-muted">${item.description || ''}</small>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-info">LKR ${item.price}</span>
                            <br><small class="text-muted">${item.category}</small>
                        </div>
                    </div>
                </label>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function toggleMenuItem(itemId, itemName, itemPrice) {
    const checkbox = document.getElementById(`item_${itemId}`);
    
    if (checkbox.checked) {
        selectedMenuItems.add({
            id: itemId,
            name: itemName,
            price: itemPrice
        });
    } else {
        selectedMenuItems.forEach(item => {
            if (item.id === itemId) {
                selectedMenuItems.delete(item);
            }
        });
    }
    
    updateSelectedItemsDisplay();
}

function updateSelectedItemsDisplay() {
    const container = document.getElementById('selectedMenuItems');
    const countElement = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitMenuBtn');
    
    countElement.textContent = selectedMenuItems.size;
    
    if (selectedMenuItems.size === 0) {
        container.innerHTML = '<p class="text-muted text-center">No items selected yet</p>';
        submitBtn.disabled = true;
        return;
    }
    
    let html = '';
    selectedMenuItems.forEach(item => {
        html += `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <strong>${item.name}</strong>
                </div>
                <div>
                    <span class="badge badge-info">LKR ${item.price}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="removeMenuItem(${item.id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    submitBtn.disabled = false;
}

function removeMenuItem(itemId) {
    // Uncheck the checkbox
    const checkbox = document.getElementById(`item_${itemId}`);
    if (checkbox) {
        checkbox.checked = false;
    }
    
    // Remove from selected items
    selectedMenuItems.forEach(item => {
        if (item.id === itemId) {
            selectedMenuItems.delete(item);
        }
    });
    
    updateSelectedItemsDisplay();
}

function createMenuItemFirst() {
    $('#createMenuModal').modal('hide');
    $('#addMenuItemModal').modal('show');
}

function submitMenu(event) {
    event.preventDefault();
    
    if (selectedMenuItems.size === 0) {
        alert('Please select at least one menu item');
        return;
    }
    
    const formData = new FormData(event.target);
    const activeDays = [];
    formData.getAll('active_days[]').forEach(day => activeDays.push(day));
    
    const menuData = {
        name: formData.get('name'),
        branch_id: formData.get('branch_id'),
        start_time: formData.get('start_time'),
        end_time: formData.get('end_time'),
        active_days: activeDays,
        description: formData.get('description'),
        menu_items: Array.from(selectedMenuItems).map(item => item.id)
    };
    
    // Disable submit button
    document.getElementById('submitMenuBtn').disabled = true;
    document.getElementById('submitMenuBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Menu...';
    
    fetch('/admin/organizations/{{ $organization->id ?? "0" }}/menus', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(menuData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            $('#createMenuModal').modal('hide');
            
            // Reset form and selections
            document.getElementById('createMenuForm').reset();
            selectedMenuItems.clear();
            updateSelectedItemsDisplay();
            
            alert('Menu created successfully!');
            
            // Optionally reload page
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to create menu'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create menu');
    })
    .finally(() => {
        // Re-enable submit button
        document.getElementById('submitMenuBtn').disabled = false;
        document.getElementById('submitMenuBtn').innerHTML = '<i class="fas fa-save"></i> Create Menu';
    });
}
</script>
