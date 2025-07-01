/**
 * Order System JavaScript
 * Handles real-time stock validation, summary page actions, and dynamic availability badges
 */

document.addEventListener('DOMContentLoaded', () => {
    initializeOrderSystem();
});

function initializeOrderSystem() {
    initializeStockValidation();
    initializeSummaryPageActions();
    initializeDynamicAvailabilityBadges();
    initializeStockReservation();
}

/**
 * Real-time stock validation for order items
 */
function initializeStockValidation() {
    const addToOrderButtons = document.querySelectorAll('.add-to-order');
    
    addToOrderButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const itemId = this.dataset.itemId;
            const itemType = parseInt(this.dataset.itemType || '1');
            const stock = parseInt(this.dataset.stock || '0');
            const requestedQuantity = getRequestedQuantity(itemId);
            
            // Check stock for buy/sell items (TYPE_BUY_SELL = 1)
            if (itemType === 1 && stock < requestedQuantity) {
                e.preventDefault();
                alert('Insufficient stock!');
                this.checked = false;
                return false;
            }
            
            // Handle item addition/removal
            if (this.checked) {
                enableQuantityControls(itemId);
                updateStockDisplay(itemId, -requestedQuantity);
            } else {
                disableQuantityControls(itemId);
                updateStockDisplay(itemId, requestedQuantity);
            }
            
            updateOrderSummary();
        });
    });
}

/**
 * Summary page action handlers
 */
function initializeSummaryPageActions() {
    // Submit Order: redirect to OrderConfirmationController
    const submitButton = document.querySelector('#submit-order-btn');
    if (submitButton) {
        submitButton.addEventListener('click', function() {
            const form = document.querySelector('#order-form');
            if (form && validateOrderItems()) {
                form.action = form.dataset.submitRoute || '/admin/orders';
                form.submit();
            }
        });
    }

    // Update Order: redirect to OrderEditController  
    const updateButton = document.querySelector('#update-order-btn');
    if (updateButton) {
        updateButton.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (orderId) {
                window.location.href = `/admin/orders/${orderId}/edit`;
            }
        });
    }

    // Add Another: redirect to OrderCreateController
    const addAnotherButton = document.querySelector('#add-another-order-btn');
    if (addAnotherButton) {
        addAnotherButton.addEventListener('click', function() {
            const reservationId = this.dataset.reservationId;
            const createUrl = reservationId 
                ? `/admin/orders/create?reservation_id=${reservationId}`
                : '/admin/orders/create';
            window.location.href = createUrl;
        });
    }
}

/**
 * Dynamic availability badges for menu items
 */
function initializeDynamicAvailabilityBadges() {
    updateAllAvailabilityBadges();
    
    // Update badges when stock changes
    document.addEventListener('stock-updated', function(e) {
        updateAvailabilityBadge(e.detail.itemId, e.detail.newStock);
    });
}

/**
 * Update availability badge for a specific item
 */
function updateAvailabilityBadge(itemId, stock = null) {
    const menuItem = document.querySelector(`[data-item-id="${itemId}"]`);
    if (!menuItem) return;

    const itemType = parseInt(menuItem.dataset.itemType || '1');
    const currentStock = stock !== null ? stock : parseInt(menuItem.dataset.stock || '0');
    const isAvailable = menuItem.dataset.isAvailable === 'true';

    let badgeHtml = '';
    
    // TYPE_BUY_SELL = 1, TYPE_KOT = 2 as defined in MenuItem model
    if (itemType === 1) {
        if (currentStock > 0) {
            badgeHtml = `<span class="badge bg-success">
                <i class="fas fa-check-circle mr-1"></i>In Stock (${currentStock})
            </span>`;
        } else {
            badgeHtml = `<span class="badge bg-danger">
                <i class="fas fa-times-circle mr-1"></i>Out of Stock
            </span>`;
        }
    } else if (itemType === 2) {
        if (isAvailable) {
            badgeHtml = `<span class="badge bg-success">
                <i class="fas fa-check-circle mr-1"></i>Available
            </span>`;
        } else {
            badgeHtml = `<span class="badge bg-warning">
                <i class="fas fa-pause-circle mr-1"></i>Unavailable
            </span>`;
        }
    }

    // Update or create badge container
    let badgeContainer = menuItem.querySelector('.availability-badge');
    if (!badgeContainer) {
        badgeContainer = document.createElement('div');
        badgeContainer.className = 'availability-badge mt-2';
        menuItem.appendChild(badgeContainer);
    }
    badgeContainer.innerHTML = badgeHtml;
}

/**
 * Update all availability badges
 */
function updateAllAvailabilityBadges() {
    document.querySelectorAll('.menu-item').forEach(menuItem => {
        const itemId = menuItem.dataset.itemId;
        updateAvailabilityBadge(itemId);
    });
}

/**
 * Stock reservation during ordering as requested in refactoring
 */
function initializeStockReservation() {
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.add-to-order').forEach(button => {
            button.addEventListener('click', () => {
                const itemId = button.dataset.itemId;
                const stock = parseInt(button.dataset.stock || '0');
                const itemType = parseInt(button.dataset.itemType || '1');
                
                // Stock validation for TYPE_BUY_SELL items
                if (itemType === 1 && stock < 1) {
                    alert('Insufficient stock!');
                    button.checked = false;
                    return;
                }
                
                // Proceed with order addition
                if (button.checked) {
                    reserveStockForItem(itemId);
                } else {
                    releaseStockForItem(itemId);
                }
            });
        });
    });
}

/**
 * Real-time stock validation
 */
function checkStock(itemId, quantity, callback) {
    fetch('/orders/check-stock', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            item_id: itemId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (callback) callback(data);
    })
    .catch(error => {
        console.error('Stock check error:', error);
        if (callback) callback({available: false, message: 'Error checking stock'});
    });
}

/**
 * Setup summary page actions
 */
function setupSummaryPageActions() {
    // Submit Order button
    const submitBtn = document.getElementById('submit-order-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            const form = document.getElementById('order-form');
            if (form) {
                form.action = form.action.replace('/store', '/submit');
                form.submit();
            }
        });
    }

    // Update Order button
    const updateBtn = document.getElementById('update-order-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            window.location.href = `/orders/${orderId}/edit`;
        });
    }

    // Add Another Order button
    const addAnotherBtn = document.getElementById('add-another-btn');
    if (addAnotherBtn) {
        addAnotherBtn.addEventListener('click', function() {
            window.location.href = '/orders/create';
        });
    }
}

// Initialize summary page actions when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupSummaryPageActions();
});

/**
 * Helper Functions
 */
function getRequestedQuantity(itemId) {
    const quantityInput = document.querySelector(`[data-item-id="${itemId}"] .item-qty`);
    return quantityInput ? parseInt(quantityInput.value || '1') : 1;
}

function enableQuantityControls(itemId) {
    const menuItem = document.querySelector(`[data-item-id="${itemId}"]`);
    if (!menuItem) return;
    
    const qtyInput = menuItem.querySelector('.item-qty');
    const increaseBtn = menuItem.querySelector('.qty-increase');
    const decreaseBtn = menuItem.querySelector('.qty-decrease');
    
    // Enable controls
    if (qtyInput) {
        qtyInput.disabled = false;
        qtyInput.name = `items[${itemId}][quantity]`;
        
        // Set proper button states
        const currentValue = parseInt(qtyInput.value) || 1;
        const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;
        
        if (decreaseBtn) {
            decreaseBtn.disabled = currentValue <= 1;
        }
        if (increaseBtn) {
            increaseBtn.disabled = currentValue >= maxValue;
        }
    }
}

function disableQuantityControls(itemId) {
    const menuItem = document.querySelector(`[data-item-id="${itemId}"]`);
    const controls = menuItem.querySelectorAll('.qty-decrease, .qty-increase, .item-qty');
    controls.forEach(control => control.disabled = true);
    
    // Disable form submission for this item
    const qtyInput = menuItem.querySelector('.item-qty');
    if (qtyInput) {
        qtyInput.removeAttribute('name');
    }
}

function updateStockDisplay(itemId, quantityChange) {
    const stockIndicator = document.querySelector(`[data-item-id="${itemId}"] .stock-indicator`);
    if (stockIndicator) {
        const currentStock = parseInt(stockIndicator.dataset.stock || '0');
        const newStock = Math.max(0, currentStock + quantityChange);
        
        stockIndicator.dataset.stock = newStock;
        stockIndicator.textContent = `Stock: ${newStock}`;
        
        // Update menu item data attribute
        const menuItem = document.querySelector(`[data-item-id="${itemId}"]`);
        if (menuItem) {
            menuItem.dataset.stock = newStock;
        }
        
        // Dispatch stock update event
        document.dispatchEvent(new CustomEvent('stock-updated', {
            detail: { itemId: itemId, newStock: newStock }
        }));
        
        // Dispatch order item added event
        document.dispatchEvent(new CustomEvent('order-item-added', {
            detail: { 
                itemId: itemId, 
                quantity: Math.abs(quantityChange) 
            }
        }));
    }
}

function reserveStockForItem(itemId) {
    // This would typically make an AJAX call to reserve stock
    console.log(`Reserving stock for item ${itemId}`);
}

function releaseStockForItem(itemId) {
    // This would typically make an AJAX call to release stock
    console.log(`Releasing stock for item ${itemId}`);
}

function updateOrderSummary() {
    const selectedItems = document.querySelectorAll('.add-to-order:checked');
    let total = 0;
    let itemCount = 0;

    selectedItems.forEach(checkbox => {
        const menuItem = checkbox.closest('.menu-item');
        const price = parseFloat(menuItem.dataset.price || '0');
        const quantity = parseInt(menuItem.querySelector('.item-qty').value || '1');
        
        total += price * quantity;
        itemCount += quantity;
    });

    // Update summary elements
    const totalElement = document.querySelector('#order-total');
    const countElement = document.querySelector('#order-count');
    
    if (totalElement) {
        totalElement.textContent = `LKR ${total.toFixed(2)}`;
    }
    
    if (countElement) {
        countElement.textContent = `${itemCount} items`;
    }
}

function validateOrderItems() {
    const selectedItems = document.querySelectorAll('.add-to-order:checked');
    if (selectedItems.length === 0) {
        alert('Please select at least one item for the order');
        return false;
    }
    return true;
}
