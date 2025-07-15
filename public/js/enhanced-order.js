/**
 * Enhanced Order Management with Real-time Stock Checking
 */
class EnhancedOrderManager {
    constructor() {
        this.cart = new Map();
        this.stockCache = new Map();
        this.validationTimer = null;
        this.wsConnection = null;

        this.initializeEventListeners();
        this.initializeWebSocket();
        this.loadInitialStockData();
    }

    /**
     * Initialize all event listeners
     */
    initializeEventListeners() {
        // Menu search and filtering
        $('#menu-search').on('input', debounce((e) => this.filterMenuItems(), 300));
        $('#category-filter').on('change', () => this.filterMenuItems());
        $('#availability-filter').on('change', () => this.filterMenuItems());

        // Cart management
        $(document).on('change', '.item-check', (e) => this.handleItemToggle(e));
        $(document).on('click', '.qty-increase', (e) => this.handleQuantityChange(e, 1));
        $(document).on('click', '.qty-decrease', (e) => this.handleQuantityChange(e, -1));
        $(document).on('input', '.item-qty', (e) => this.handleDirectQuantityChange(e));

        // Form actions
        $('#validate-cart-btn').on('click', () => this.validateCart());
        $('#order-form').on('submit', (e) => this.handleOrderSubmit(e));

        // Alternatives modal
        $(document).on('click', '.alternatives-btn', (e) => this.showAlternatives(e));
        $('#close-alternatives').on('click', () => this.hideAlternatives());

        // Real-time validation
        setInterval(() => this.performBackgroundValidation(), 30000); // Every 30 seconds
    }

    /**
     * Initialize WebSocket connection for real-time updates
     */
    initializeWebSocket() {
        // Placeholder for WebSocket implementation
        // This would connect to a Laravel Broadcasting channel
        /*
        this.wsConnection = new WebSocket('ws://localhost:6001');
        this.wsConnection.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.type === 'stock_update') {
                this.handleStockUpdate(data.payload);
            }
        };
        */

        // For now, simulate with periodic updates
        setInterval(() => this.simulateStockUpdate(), 60000); // Every minute
    }

    /**
     * Load initial stock data
     */
    async loadInitialStockData() {
        try {
            const response = await fetch('/admin/api/stock-summary', {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateStockSummary(data);
            }
        } catch (error) {
            console.error('Failed to load stock data:', error);
        }
    }

    /**
     * Filter menu items based on search and filters
     */
    filterMenuItems() {
        const searchTerm = $('#menu-search').val().toLowerCase();
        const categoryFilter = $('#category-filter').val();
        const availabilityFilter = $('#availability-filter').val();

        $('.menu-item-card').each((index, element) => {
            const $card = $(element);
            const name = $card.data('name').toLowerCase();
            const category = $card.data('category');
            const availability = $card.data('availability');
            const itemType = $card.data('type') || 'KOT';
            const stock = parseInt($card.data('stock')) || 0;

            let show = true;

            // Search filter
            if (searchTerm && !name.includes(searchTerm)) {
                show = false;
            }

            // Category filter
            if (categoryFilter && category !== categoryFilter) {
                show = false;
            }

            // Availability filter
            if (availabilityFilter) {
                if (availabilityFilter === 'available') {
                    // For Buy & Sell items, check stock; for KOT items, always available
                    if (itemType === 'Buy & Sell' && stock <= 0) {
                        show = false;
                    }
                } else if (availabilityFilter === 'low_stock') {
                    // Only show Buy & Sell items with low stock (1-4 items)
                    if (itemType !== 'Buy & Sell' || stock < 1 || stock > 4) {
                        show = false;
                    }
                } else if (availabilityFilter === 'out_of_stock') {
                    // Only show Buy & Sell items with no stock
                    if (itemType !== 'Buy & Sell' || stock > 0) {
                        show = false;
                    }
                }
            }

            $card.toggle(show);
        });

        this.updateAvailabilitySummary();
    }

    /**
     * Handle item toggle (add/remove from cart)
     */
    handleItemToggle(event) {
        const $checkbox = $(event.target);
        const itemId = $checkbox.data('item-id');
        const $card = $checkbox.closest('.menu-item-card');
        const $quantityControls = $card.find('.quantity-controls');

        if ($checkbox.is(':checked')) {
            // Add to cart
            const itemData = this.extractItemData($card);
            this.cart.set(itemId, itemData);

            $card.addClass('selected');
            $quantityControls.show();

            // Enable quantity controls with proper button states
            const $qtyInput = $card.find('.item-qty');
            const $increaseBtn = $card.find('.qty-increase');
            const $decreaseBtn = $card.find('.qty-decrease');

            $qtyInput.prop('disabled', false);

            const currentValue = parseInt($qtyInput.val()) || 1;
            const maxValue = parseInt($qtyInput.attr('max')) || 99;

            $decreaseBtn.prop('disabled', currentValue <= 1);
            $increaseBtn.prop('disabled', currentValue >= maxValue);

        } else {
            // Remove from cart
            this.cart.delete(itemId);

            $card.removeClass('selected');
            $quantityControls.hide();

            // Reset quantity
            $card.find('.item-qty').val(1);
        }

        this.updateCartDisplay();
        this.validateCartInBackground();
    }

    /**
     * Handle quantity changes
     */
    handleQuantityChange(event, delta) {
        const $button = $(event.target).closest('button');
        const itemId = $button.data('item-id');
        const $qtyInput = $(`.item-qty[data-item-id="${itemId}"]`);
        const currentQty = parseInt($qtyInput.val()) || 1;
        const newQty = Math.max(1, Math.min(99, currentQty + delta));
        $qtyInput.val(newQty);
        // Update button states
        const $decreaseBtn = $(`.qty-decrease[data-item-id="${itemId}"]`);
        const $increaseBtn = $(`.qty-increase[data-item-id="${itemId}"]`);
        $decreaseBtn.prop('disabled', newQty <= 1);
        $increaseBtn.prop('disabled', newQty >= 99);
        this.handleDirectQuantityChange({ target: $qtyInput[0] });
    }

    /**
     * Handle direct quantity input changes
     */
    handleDirectQuantityChange(event) {
        const $input = $(event.target);
        const itemId = $input.data('item-id');
        let quantity = Math.max(1, Math.min(99, parseInt($input.val()) || 1));
        $input.val(quantity); // Ensure value is within bounds
        // Update button states
        const $decreaseBtn = $(`.qty-decrease[data-item-id="${itemId}"]`);
        const $increaseBtn = $(`.qty-increase[data-item-id="${itemId}"]`);
        $decreaseBtn.prop('disabled', quantity <= 1);
        $increaseBtn.prop('disabled', quantity >= 99);
        if (this.cart.has(itemId)) {
            const itemData = this.cart.get(itemId);
            itemData.quantity = quantity;
            itemData.total = itemData.price * quantity;
            this.cart.set(itemId, itemData);
            this.updateCartDisplay();
            this.validateCartInBackground();
        }
    }

    /**
     * Extract item data from card
     */
    extractItemData($card) {
        const itemId = $card.data('item-id');
        const name = $card.data('name');
        const priceText = $card.find('.font-bold').text();
        const price = parseFloat(priceText.replace(/[^\d.]/g, ''));
        const quantity = parseInt($card.find('.item-qty').val()) || 1;

        return {
            id: itemId,
            name: name,
            price: price,
            quantity: quantity,
            total: price * quantity,
            availability: $card.data('availability')
        };
    }

    /**
     * Update cart display
     */
    updateCartDisplay() {
        const $cartItems = $('#cart-items');
        const $cartCount = $('#cart-count');
        const $cartSummary = $('#cart-summary');
        const $emptyCart = $('#empty-cart');

        if (this.cart.size === 0) {
            $emptyCart.show();
            $cartSummary.hide();
            $cartCount.text('0');
            $('#place-order-btn').prop('disabled', true);
            return;
        }

        $emptyCart.hide();
        $cartSummary.show();

        // Build cart items HTML
        let cartHTML = '';
        let subtotal = 0;

        this.cart.forEach((item, itemId) => {
            subtotal += item.total;

            cartHTML += `
                <div class="cart-item p-4 border-b" data-item-id="${itemId}">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-medium text-gray-900">${item.name}</h4>
                        <button type="button" class="text-red-500 hover:text-red-700 remove-cart-item" data-item-id="${itemId}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Qty: ${item.quantity}</span>
                        <span class="font-medium">LKR ${item.total.toFixed(2)}</span>
                    </div>
                    <div class="stock-status-${item.availability} text-xs mt-1">
                        ${this.getAvailabilityBadge(item.availability)}
                    </div>
                </div>
            `;
        });

        $cartItems.html(cartHTML);

        // Update summary
        const tax = subtotal * 0.1;
        const total = subtotal + tax;

        $('#cart-subtotal').text(`LKR ${subtotal.toFixed(2)}`);
        $('#cart-tax').text(`LKR ${tax.toFixed(2)}`);
        $('#cart-total').text(`LKR ${total.toFixed(2)}`);
        $cartCount.text(this.cart.size);

        // Add remove item handlers
        $('.remove-cart-item').on('click', (e) => {
            const itemId = $(e.target).closest('button').data('item-id');
            this.removeFromCart(itemId);
        });

        $('#place-order-btn').prop('disabled', this.cart.size === 0);
    }

    /**
     * Remove item from cart
     */
    removeFromCart(itemId) {
        this.cart.delete(itemId);

        // Uncheck the item and hide controls
        const $checkbox = $(`.item-check[data-item-id="${itemId}"]`);
        $checkbox.prop('checked', false);

        const $card = $checkbox.closest('.menu-item-card');
        $card.removeClass('selected');
        $card.find('.quantity-controls').hide();
        $card.find('.item-qty').val(1);

        this.updateCartDisplay();
    }

    /**
     * Get availability badge HTML
     */
    getAvailabilityBadge(status) {
        const badges = {
            'available': '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Available</span>',
            'low_stock': '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>',
            'out_of_stock': '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Out of Stock</span>',
            'unavailable': '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Unavailable</span>'
        };
        return badges[status] || badges['unavailable'];
    }

    /**
     * Validate cart with server
     */
    async validateCart() {
        if (this.cart.size === 0) {
            this.showAlert('warning', 'Your cart is empty. Please add items before validating.');
            return;
        }

        const cartItems = Array.from(this.cart.values()).map(item => ({
            menu_item_id: item.id,
            quantity: item.quantity
        }));

        try {
            const response = await fetch('/admin/api/validate-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    items: cartItems,
                    branch_id: $('input[name="branch_id"]').val()
                })
            });

            const result = await response.json();
            this.handleValidationResult(result);

        } catch (error) {
            console.error('Cart validation failed:', error);
            this.showAlert('error', 'Failed to validate cart. Please try again.');
        }
    }

    /**
     * Handle validation result
     */
    handleValidationResult(result) {
        const $alertsContainer = $('#stock-alerts');

        if (result.valid && result.warnings.length === 0) {
            $alertsContainer.addClass('hidden');
            this.showAlert('success', 'Your cart has been validated successfully!');
            $('#place-order-btn').prop('disabled', false);
        } else {
            let message = '';

            if (!result.valid) {
                message += 'Some items are not available: ' + result.errors.join(', ');
                $('#place-order-btn').prop('disabled', true);
            } else if (result.warnings.length > 0) {
                message += 'Warnings: ' + result.warnings.join(', ');
                $('#place-order-btn').prop('disabled', false);
            }

            $('#stock-alert-message').text(message);
            $alertsContainer.removeClass('hidden');

            // Update individual item status
            result.items.forEach(item => {
                this.updateItemAvailability(item.id, item);
            });
        }
    }

    /**
     * Validate cart in background (without user interaction)
     */
    validateCartInBackground() {
        if (this.validationTimer) {
            clearTimeout(this.validationTimer);
        }

        this.validationTimer = setTimeout(async () => {
            if (this.cart.size > 0) {
                await this.validateCart();
            }
        }, 1000); // Validate 1 second after last change
    }

    /**
     * Show alternatives modal
     */
    async showAlternatives(event) {
        const itemId = $(event.target).data('item-id');

        try {
            const response = await fetch(`/admin/api/menu-alternatives/${itemId}`, {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const alternatives = await response.json();
                this.displayAlternatives(alternatives);
                $('#alternatives-modal').removeClass('hidden');
            }
        } catch (error) {
            console.error('Failed to load alternatives:', error);
        }
    }

    /**
     * Display alternatives in modal
     */
    displayAlternatives(alternatives) {
        const $content = $('#alternatives-content');

        if (alternatives.length === 0) {
            $content.html('<p class="text-gray-500 text-center py-4">No alternatives available at the moment.</p>');
            return;
        }

        let html = '<div class="grid gap-4">';

        alternatives.forEach(item => {
            html += `
                <div class="border rounded-lg p-4 flex justify-between items-center">
                    <div>
                        <h4 class="font-medium">${item.name}</h4>
                        <p class="text-sm text-gray-600">${item.description || ''}</p>
                        <p class="text-lg font-bold text-indigo-600">LKR ${item.price}</p>
                    </div>
                    <button type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg add-alternative" data-item-id="${item.id}">
                        Add to Cart
                    </button>
                </div>
            `;
        });

        html += '</div>';
        $content.html(html);

        // Add event handlers for alternative selection
        $('.add-alternative').on('click', (e) => {
            const itemId = $(e.target).data('item-id');
            const $checkbox = $(`.item-check[data-item-id="${itemId}"]`);
            $checkbox.prop('checked', true).trigger('change');
            this.hideAlternatives();
        });
    }

    /**
     * Hide alternatives modal
     */
    hideAlternatives() {
        $('#alternatives-modal').addClass('hidden');
    }

    /**
     * Handle order form submission
     */
    async handleOrderSubmit(event) {
        event.preventDefault();

        // Final validation before submission
        await this.validateCart();

        if ($('#place-order-btn').prop('disabled')) {
            this.showAlert('error', 'Please resolve cart issues before placing the order.');
            return;
        }

        // Show loading state
        const $submitBtn = $('#place-order-btn');
        const originalText = $submitBtn.html();
        $submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...').prop('disabled', true);

        // Submit the form
        try {
            const formData = new FormData(event.target);
            const response = await fetch(event.target.action, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.showAlert('success', 'Order placed successfully!');
                    setTimeout(() => {
                        window.location.href = result.redirect || '/admin/orders';
                    }, 2000);
                } else {
                    this.showAlert('error', result.message || 'Failed to place order.');
                }
            } else {
                throw new Error('Server error');
            }
        } catch (error) {
            console.error('Order submission failed:', error);
            this.showAlert('error', 'Failed to place order. Please try again.');
        } finally {
            $submitBtn.html(originalText).prop('disabled', false);
        }
    }

    /**
     * Update stock summary widget
     */
    updateStockSummary(data) {
        $('#available-count').text(data.available_count || 0);
        $('#low-stock-count').text(data.low_stock_count || 0);
        $('#out-stock-count').text(data.out_of_stock_count || 0);
    }

    /**
     * Update availability summary
     */
    updateAvailabilitySummary() {
        const visible = $('.menu-item-card:visible').length;
        const available = $('.menu-item-card:visible[data-availability="available"]').length;
        const lowStock = $('.menu-item-card:visible[data-availability="low_stock"]').length;
        const outOfStock = $('.menu-item-card:visible[data-availability="out_of_stock"]').length;

        $('#availability-summary').text(`Showing ${visible} items (${available} available, ${lowStock} low stock, ${outOfStock} out of stock)`);
    }

    /**
     * Update individual item availability
     */
    updateItemAvailability(itemId, itemData) {
        const $card = $(`.menu-item-card[data-item-id="${itemId}"]`);
        const $indicator = $(`.stock-indicator[data-item-id="${itemId}"]`);
        const $percentage = $(`.stock-percentage[data-item-id="${itemId}"]`);
        const $bar = $(`.stock-bar[data-item-id="${itemId}"]`);

        $card.attr('data-availability', itemData.status);
        $indicator.html(this.getAvailabilityBadge(itemData.status));

        if (itemData.stock_percentage !== undefined) {
            $percentage.text(itemData.stock_percentage + '%');
            $bar.css({
                'width': itemData.stock_percentage + '%',
                'background-color': itemData.stock_percentage > 50 ? '#10B981' : (itemData.stock_percentage > 20 ? '#F59E0B' : '#EF4444')
            });
        }

        // Update card styling
        $card.removeClass('out-of-stock low-stock');
        if (itemData.status === 'out_of_stock') {
            $card.addClass('out-of-stock');
        } else if (itemData.status === 'low_stock') {
            $card.addClass('low-stock');
        }
    }

    /**
     * Simulate stock update (replace with real WebSocket data)
     */
    simulateStockUpdate() {
        // This would be replaced with actual WebSocket data
        console.log('Simulating stock update...');
    }

    /**
     * Handle real-time stock updates
     */
    handleStockUpdate(data) {
        // Update stock cache
        this.stockCache.set(data.branch_id, data);

        // Update UI elements
        this.updateStockSummary(data.summary);

        // Update individual item statuses
        if (data.updated_items) {
            data.updated_items.forEach(item => {
                this.updateItemAvailability(item.id, item);
            });
        }

        // Re-validate cart if needed
        if (this.cart.size > 0) {
            this.validateCartInBackground();
        }
    }

    /**
     * Show alert message
     */
    showAlert(type, message) {
        const alertClasses = {
            success: 'bg-green-50 text-green-700 border-green-200',
            warning: 'bg-yellow-50 text-yellow-700 border-yellow-200',
            error: 'bg-red-50 text-red-700 border-red-200',
            info: 'bg-blue-50 text-blue-700 border-blue-200'
        };

        const alertHTML = `
            <div class="alert-message ${alertClasses[type]} border rounded-lg p-4 mb-4" style="position: fixed; top: 20px; right: 20px; z-index: 1000; max-width: 400px;">
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')} mr-2"></i>
                    <span>${message}</span>
                    <button type="button" class="ml-auto text-current opacity-75 hover:opacity-100" onclick="$(this).closest('.alert-message').fadeOut()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

        $('body').append(alertHTML);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            $('.alert-message').last().fadeOut(() => {
                $(this).remove();
            });
        }, 5000);
    }
}

/**
 * Debounce utility function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize when document is ready
$(document).ready(() => {
    window.orderManager = new EnhancedOrderManager();
});
