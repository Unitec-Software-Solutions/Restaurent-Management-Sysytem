<!-- Create Takeaway Order Modal -->
<div class="modal fade" id="createTakeawayOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-bag text-warning"></i> Create Takeaway Order
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createTakeawayOrderForm" onsubmit="submitTakeawayOrder(event)">
                <div class="modal-body">
                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Customer Information</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customer_phone">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="order_branch">Branch <span class="text-danger">*</span></label>
                                <select class="form-control" id="order_branch" name="branch_id" required>
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

                    <!-- Menu Items Selection -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Menu Items</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Available Menu Items</h6>
                                </div>
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                    <div id="menuItemsList">
                                        <!-- Menu items will be loaded here via AJAX -->
                                        <div class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <p class="mt-2">Loading menu items...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Order Items</h6>
                                </div>
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                    <div id="orderItemsList">
                                        <p class="text-muted text-center">No items added yet</p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><strong>Total:</strong></span>
                                        <span class="h5 mb-0" id="orderTotal">LKR 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Instructions -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="special_instructions">Special Instructions</label>
                                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="2" placeholder="Any special requests or cooking instructions"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="submitOrderBtn" disabled>
                        <i class="fas fa-shopping-bag"></i> Create Order & Print KOT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let orderItems = [];
let orderTotal = 0;

// Load menu items when branch is selected
document.getElementById('order_branch').addEventListener('change', function() {
    const branchId = this.value;
    if (branchId) {
        loadMenuItems(branchId);
    } else {
        document.getElementById('menuItemsList').innerHTML = '<p class="text-muted text-center">Select a branch to view menu items</p>';
    }
});

function loadMenuItems(branchId) {
    document.getElementById('menuItemsList').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i><p class="mt-2">Loading menu items...</p></div>';
    
    fetch(`/admin/menu-items/all-items?branch_id=${branchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMenuItems(data.items);
            } else {
                document.getElementById('menuItemsList').innerHTML = '<p class="text-muted text-center">No menu items found</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('menuItemsList').innerHTML = '<p class="text-danger text-center">Failed to load menu items</p>';
        });
}

function displayMenuItems(menuItems) {
    const container = document.getElementById('menuItemsList');
    
    if (menuItems.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No menu items available</p>';
        return;
    }
    
    let html = '';
    menuItems.forEach(item => {
        const typeIcon = item.type === 1 ? 'fas fa-boxes text-blue' : 'fas fa-utensils text-orange';
        const typeBadge = item.type === 1 ? 'badge-info' : 'badge-warning';
        const availabilityClass = item.can_make ? '' : 'opacity-50';
        const availabilityText = item.can_make ? '' : '(Out of Stock)';
        
        html += `
            <div class="card mb-2 ${availabilityClass}">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="mb-0 mr-2">${item.name}</h6>
                                <i class="${typeIcon}" title="${item.type_name}"></i>
                            </div>
                            <small class="text-muted">${item.description || ''}</small>
                            <div class="mt-1">
                                <span class="badge badge-primary">LKR ${parseFloat(item.price).toFixed(2)}</span>
                                <span class="badge ${typeBadge}">${item.type_name}</span>
                                ${item.is_vegetarian ? '<span class="badge badge-success">Veg</span>' : ''}
                                ${!item.can_make ? '<span class="badge badge-danger">Out of Stock</span>' : ''}
                            </div>
                            ${item.type === 1 ? `<small class="text-info">Stock: ${item.current_stock}</small>` : 
                              `<small class="text-warning">Prep Time: ${item.preparation_time || 15} mins</small>`}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="addToOrder(${item.id}, '${item.name}', ${item.price})"
                                ${!item.can_make ? 'disabled' : ''}>
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function addToOrder(itemId, itemName, itemPrice) {
    // Check if item already exists
    const existingIndex = orderItems.findIndex(item => item.id === itemId);
    
    if (existingIndex >= 0) {
        orderItems[existingIndex].quantity++;
    } else {
        orderItems.push({
            id: itemId,
            name: itemName,
            price: itemPrice,
            quantity: 1
        });
    }
    
    updateOrderDisplay();
}

function removeFromOrder(itemId) {
    orderItems = orderItems.filter(item => item.id !== itemId);
    updateOrderDisplay();
}

function updateQuantity(itemId, quantity) {
    const index = orderItems.findIndex(item => item.id === itemId);
    if (index >= 0) {
        if (quantity <= 0) {
            removeFromOrder(itemId);
        } else {
            orderItems[index].quantity = quantity;
            updateOrderDisplay();
        }
    }
}

function updateOrderDisplay() {
    const container = document.getElementById('orderItemsList');
    const totalElement = document.getElementById('orderTotal');
    const submitBtn = document.getElementById('submitOrderBtn');
    
    if (orderItems.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No items added yet</p>';
        totalElement.textContent = 'LKR 0.00';
        submitBtn.disabled = true;
        return;
    }
    
    let html = '';
    orderTotal = 0;
    
    orderItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        orderTotal += itemTotal;
        
        html += `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.name}</h6>
                    <small class="text-muted">LKR ${item.price} each</small>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <span class="mx-2">${item.quantity}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="removeFromOrder(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    totalElement.textContent = `LKR ${orderTotal.toFixed(2)}`;
    submitBtn.disabled = false;
}

function submitTakeawayOrder(event) {
    event.preventDefault();
    
    if (orderItems.length === 0) {
        alert('Please add at least one item to the order');
        return;
    }
    
    const formData = new FormData(event.target);
    const orderData = {
        customer_name: formData.get('customer_name'),
        customer_phone: formData.get('customer_phone'),
        branch_id: formData.get('branch_id'),
        special_instructions: formData.get('special_instructions'),
        order_type: 'takeaway',
        items: orderItems,
        total: orderTotal
    };
    
    // Disable submit button
    document.getElementById('submitOrderBtn').disabled = true;
    document.getElementById('submitOrderBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Order...';
    
    fetch('/admin/organizations/{{ $organization->id ?? "0" }}/orders', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            $('#createTakeawayOrderModal').modal('hide');
            
            // Reset form and order items
            document.getElementById('createTakeawayOrderForm').reset();
            orderItems = [];
            updateOrderDisplay();
            
            alert('Order created successfully!');
            
            // Open KOT print window
            if (result.kot_url) {
                window.open(result.kot_url, '_blank', 'width=800,height=600');
            }
            
            // Optionally reload page
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to create order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create order');
    })
    .finally(() => {
        // Re-enable submit button
        document.getElementById('submitOrderBtn').disabled = false;
        document.getElementById('submitOrderBtn').innerHTML = '<i class="fas fa-shopping-bag"></i> Create Order & Print KOT';
    });
}
</script>
