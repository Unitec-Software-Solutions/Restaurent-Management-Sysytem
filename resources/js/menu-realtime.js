/**
 * Real-time Menu Management JavaScript
 * Handles live updates for menu changes, order notifications, and UI updates
 */

class MenuRealtimeManager {
    constructor() {
        this.echo = window.Echo;
        this.currentBranchId = null;
        this.menuUpdateCallbacks = [];
        this.orderUpdateCallbacks = [];
        this.init();
    }

    init() {
        this.setupBranchListener();
        this.setupMenuNotifications();
        this.setupOrderNotifications();
        this.setupAdminChannels();
    }

    /**
     * Setup branch-specific listeners
     */
    setupBranchListener() {
        // Get current branch from page context
        const branchSelect = document.getElementById('branch-select') || document.querySelector('[name="branch_id"]');
        if (branchSelect) {
            this.currentBranchId = branchSelect.value;
            branchSelect.addEventListener('change', (e) => {
                this.switchBranch(e.target.value);
            });
        }

        // Auto-detect branch from meta tag
        const branchMeta = document.querySelector('meta[name="current-branch-id"]');
        if (branchMeta && !this.currentBranchId) {
            this.currentBranchId = branchMeta.content;
        }

        if (this.currentBranchId) {
            this.subscribeToBranch(this.currentBranchId);
        }
    }

    /**
     * Switch to a different branch
     */
    switchBranch(branchId) {
        if (this.currentBranchId) {
            this.echo.leave(`private-branch.${this.currentBranchId}`);
        }
        
        this.currentBranchId = branchId;
        if (branchId) {
            this.subscribeToBranch(branchId);
        }
    }

    /**
     * Subscribe to branch-specific updates
     */
    subscribeToLevel(branchId) {
        this.echo.private(`branch.${branchId}`)
            .listen('MenuActivated', (e) => {
                this.handleMenuActivated(e);
            })
            .listen('MenuDeactivated', (e) => {
                this.handleMenuDeactivated(e);
            })
            .listen('MenuItemUpdated', (e) => {
                this.handleMenuItemUpdated(e);
            })
            .listen('order.updated', (e) => {
                this.handleOrderUpdate(e);
            });
    }

    /**
     * Setup general menu notifications
     */
    setupMenuNotifications() {
        this.echo.private('menus')
            .listen('MenuScheduleChanged', (e) => {
                this.handleMenuScheduleChanged(e);
            })
            .listen('MenuTransition', (e) => {
                this.handleMenuTransition(e);
            });
    }

    /**
     * Setup order notifications
     */
    setupOrderNotifications() {
        this.echo.private('orders')
            .listen('order.updated', (e) => {
                this.handleOrderUpdate(e);
            });
    }

    /**
     * Setup admin-specific channels
     */
    setupAdminChannels() {
        if (this.isAdminPage()) {
            this.echo.private('admin.orders')
                .listen('admin.order.updated', (e) => {
                    this.handleAdminOrderUpdate(e);
                });

            this.echo.private('admin.dashboard')
                .listen('MenuSafetyAlert', (e) => {
                    this.handleMenuSafetyAlert(e);
                });
        }
    }

    /**
     * Handle menu activation
     */
    handleMenuActivated(event) {
        const { menu, branch_id } = event;
        
        this.showNotification(
            `Menu "${menu.name}" is now active`,
            'success',
            'fas fa-check-circle'
        );

        // Update menu selector if exists
        this.updateMenuSelector(menu, true);
        
        // Refresh menu items if on order creation page
        if (this.isOrderCreationPage()) {
            this.refreshMenuItems(menu.id);
        }

        // Execute callbacks
        this.menuUpdateCallbacks.forEach(callback => callback('activated', menu));
    }

    /**
     * Handle menu deactivation
     */
    handleMenuDeactivated(event) {
        const { menu, branch_id } = event;
        
        this.showNotification(
            `Menu "${menu.name}" has been deactivated`,
            'warning',
            'fas fa-exclamation-triangle'
        );

        // Update menu selector
        this.updateMenuSelector(menu, false);
        
        // Show warning if current order uses this menu
        if (this.isCurrentMenuInUse(menu.id)) {
            this.showMenuDeactivationWarning(menu);
        }

        this.menuUpdateCallbacks.forEach(callback => callback('deactivated', menu));
    }

    /**
     * Handle menu item updates
     */
    handleMenuItemUpdated(event) {
        const { menu_item, action } = event;
        
        // Update item in UI if visible
        const itemElement = document.querySelector(`[data-item-id="${menu_item.id}"]`);
        if (itemElement) {
            this.updateMenuItemElement(itemElement, menu_item, action);
        }

        // Show notification for important changes
        if (action === 'unavailable') {
            this.showNotification(
                `"${menu_item.name}" is no longer available`,
                'warning',
                'fas fa-times-circle'
            );
        } else if (action === 'available') {
            this.showNotification(
                `"${menu_item.name}" is now available`,
                'success',
                'fas fa-check-circle'
            );
        }
    }

    /**
     * Handle order updates
     */
    handleOrderUpdate(event) {
        const { order, action } = event;
        
        // Update order in lists/tables
        this.updateOrderInTable(order);
        
        // Show notification for new orders
        if (action === 'created') {
            this.showNotification(
                `New order #${order.id} from ${order.customer_name}`,
                'info',
                'fas fa-shopping-cart'
            );
        }

        this.orderUpdateCallbacks.forEach(callback => callback(order, action));
    }

    /**
     * Handle admin order updates
     */
    handleAdminOrderUpdate(event) {
        const { order } = event;
        
        // Update admin dashboard
        this.updateAdminDashboard(order);
        
        // Update order statistics
        this.refreshOrderStats();
    }

    /**
     * Handle menu safety alerts
     */
    handleMenuSafetyAlert(event) {
        const { alert } = event;
        
        this.showNotification(
            alert.message,
            alert.type,
            'fas fa-shield-alt'
        );

        // Update safety indicators
        this.updateSafetyIndicators(alert);
    }

    /**
     * Handle menu schedule changes
     */
    handleMenuScheduleChanged(event) {
        const { schedule } = event;
        
        this.showNotification(
            'Menu schedule has been updated',
            'info',
            'fas fa-calendar-alt'
        );

        // Refresh calendar view if visible
        if (document.getElementById('menu-calendar')) {
            this.refreshMenuCalendar();
        }
    }

    /**
     * Handle menu transitions
     */
    handleMenuTransition(event) {
        const { from_menu, to_menu, branch_id } = event;
        
        this.showNotification(
            `Menu switched from "${from_menu.name}" to "${to_menu.name}"`,
            'info',
            'fas fa-exchange-alt'
        );

        // Refresh current page if affected
        if (this.currentBranchId == branch_id) {
            this.refreshMenuContent();
        }
    }

    /**
     * Utility methods
     */
    
    isAdminPage() {
        return document.body.classList.contains('admin-page') || 
               window.location.pathname.includes('/admin');
    }

    isOrderCreationPage() {
        return document.body.classList.contains('order-creation-page') ||
               window.location.pathname.includes('/orders/create') ||
               window.location.pathname.includes('/orders/enhanced-create');
    }

    isCurrentMenuInUse(menuId) {
        const menuSelect = document.querySelector('[name="menu_id"]');
        return menuSelect && menuSelect.value == menuId;
    }

    showNotification(message, type = 'info', icon = 'fas fa-info-circle') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} fade-in`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="${icon}"></i>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;

        // Add to notification container
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }

        container.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    updateMenuSelector(menu, isActive) {
        const menuSelect = document.getElementById('menu-select');
        if (!menuSelect) return;

        const option = menuSelect.querySelector(`option[value="${menu.id}"]`);
        if (option) {
            if (isActive) {
                option.disabled = false;
                option.textContent = `${menu.name} (${menu.type})`;
            } else {
                option.disabled = true;
                option.textContent = `${menu.name} (${menu.type}) - Inactive`;
            }
        }
    }

    updateMenuItemElement(element, menuItem, action) {
        const availabilityIndicator = element.querySelector('.availability-indicator');
        const checkbox = element.querySelector('.item-checkbox');
        
        if (action === 'unavailable') {
            element.classList.add('opacity-50');
            if (checkbox) checkbox.disabled = true;
            if (availabilityIndicator) {
                availabilityIndicator.innerHTML = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Unavailable</span>';
            }
        } else if (action === 'available') {
            element.classList.remove('opacity-50');
            if (checkbox) checkbox.disabled = false;
            if (availabilityIndicator) {
                availabilityIndicator.innerHTML = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Available</span>';
            }
        }

        // Update stock info
        if (menuItem.current_stock !== undefined) {
            const stockSpan = availabilityIndicator?.querySelector('.stock-info') || 
                           document.createElement('span');
            stockSpan.className = 'text-xs text-gray-500 ml-2 stock-info';
            stockSpan.textContent = `Stock: ${menuItem.current_stock}`;
            if (!stockSpan.parentElement && availabilityIndicator) {
                availabilityIndicator.appendChild(stockSpan);
            }
        }
    }

    updateOrderInTable(order) {
        const orderRow = document.querySelector(`tr[data-order-id="${order.id}"]`);
        if (!orderRow) return;

        // Update status
        const statusCell = orderRow.querySelector('.order-status');
        if (statusCell) {
            statusCell.textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);
            statusCell.className = `order-status px-2 py-1 text-xs font-semibold rounded-full 
                ${this.getStatusColor(order.status)}`;
        }

        // Update total
        const totalCell = orderRow.querySelector('.order-total');
        if (totalCell) {
            totalCell.textContent = `LKR ${parseFloat(order.total_amount).toFixed(2)}`;
        }
    }

    getStatusColor(status) {
        const colors = {
            'active': 'bg-blue-100 text-blue-800',
            'submitted': 'bg-yellow-100 text-yellow-800',
            'preparing': 'bg-orange-100 text-orange-800',
            'ready': 'bg-green-100 text-green-800',
            'completed': 'bg-gray-100 text-gray-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    showMenuDeactivationWarning(menu) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 bg-black/50 flex items-center justify-center';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mr-3"></i>
                    <h3 class="text-lg font-semibold">Menu Deactivated</h3>
                </div>
                <p class="text-gray-600 mb-6">
                    The menu "${menu.name}" you are currently using has been deactivated. 
                    Please refresh the page or select a different active menu.
                </p>
                <div class="flex justify-end gap-3">
                    <button class="px-4 py-2 text-gray-600 hover:text-gray-800" onclick="this.closest('.fixed').remove()">
                        Dismiss
                    </button>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" onclick="window.location.reload()">
                        Refresh Page
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    refreshMenuItems(menuId) {
        const itemsGrid = document.getElementById('items-grid');
        if (!itemsGrid) return;

        // Show loading state
        itemsGrid.innerHTML = '<div class="col-span-full text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';

        // Fetch updated menu items
        fetch(`/admin/orders/get-menu-items?menu_id=${menuId}`)
            .then(response => response.json())
            .then(data => {
                if (data.items) {
                    this.renderMenuItems(data.items);
                }
            })
            .catch(error => {
                console.error('Failed to refresh menu items:', error);
                itemsGrid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500">Failed to load menu items</div>';
            });
    }

    refreshMenuCalendar() {
        // Implement calendar refresh logic
        const calendar = document.getElementById('menu-calendar');
        if (calendar && typeof refreshCalendarData === 'function') {
            refreshCalendarData();
        }
    }

    refreshMenuContent() {
        // Refresh current menu-related content
        if (this.isOrderCreationPage()) {
            const menuSelect = document.getElementById('menu-select');
            if (menuSelect && menuSelect.value) {
                this.refreshMenuItems(menuSelect.value);
            }
        }
    }

    refreshOrderStats() {
        // Refresh order statistics on dashboard
        const statsContainers = document.querySelectorAll('[data-order-stats]');
        statsContainers.forEach(container => {
            // Implement stats refresh
            fetch('/admin/orders/stats')
                .then(response => response.json())
                .then(data => {
                    // Update stats in container
                    this.updateStatsContainer(container, data);
                })
                .catch(error => console.error('Failed to refresh order stats:', error));
        });
    }

    updateAdminDashboard(order) {
        // Update recent orders list
        const recentOrdersList = document.getElementById('recent-orders');
        if (recentOrdersList) {
            this.prependOrderToList(recentOrdersList, order);
        }

        // Update order counts
        this.updateOrderCounts();
    }

    updateSafetyIndicators(alert) {
        const safetyPanel = document.getElementById('menu-safety-panel');
        if (safetyPanel) {
            const alertElement = document.createElement('div');
            alertElement.className = `alert alert-${alert.type} mb-2`;
            alertElement.textContent = alert.message;
            safetyPanel.appendChild(alertElement);
        }
    }

    /**
     * Public API methods
     */
    
    onMenuUpdate(callback) {
        this.menuUpdateCallbacks.push(callback);
    }

    onOrderUpdate(callback) {
        this.orderUpdateCallbacks.push(callback);
    }

    getCurrentBranchId() {
        return this.currentBranchId;
    }
}

// CSS for toast notifications
const toastStyles = `
<style>
.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-left: 4px solid;
    max-width: 350px;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.toast.fade-in {
    transform: translateX(0);
}

.toast.fade-out {
    transform: translateX(100%);
}

.toast-success { border-left-color: #10b981; }
.toast-warning { border-left-color: #f59e0b; }
.toast-info { border-left-color: #3b82f6; }
.toast-error { border-left-color: #ef4444; }

.toast-content {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.toast-close {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #6b7280;
}

.toast-close:hover {
    color: #374151;
}
</style>
`;

// Add styles to document
document.head.insertAdjacentHTML('beforeend', toastStyles);

// Initialize the real-time manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if Echo is available (WebSocket connection)
    if (window.Echo) {
        window.menuRealtimeManager = new MenuRealtimeManager();
    } else {
        console.warn('Laravel Echo not available. Real-time features disabled.');
    }
});

// Export for global access
window.MenuRealtimeManager = MenuRealtimeManager;
