/**
 * Real-time Dashboard for Inventory and Order Management
 */
class RealtimeDashboard {
    constructor() {
        this.updateInterval = 30000; // 30 seconds
        this.charts = {};
        this.websocket = null;
        
        this.initializeCharts();
        this.initializeEventListeners();
        this.initializeWebSocket();
        this.startPeriodicUpdates();
    }

    /**
     * Initialize all charts
     */
    initializeCharts() {
        this.initializeStockLevelsChart();
    }

    /**
     * Initialize stock levels chart
     */
    initializeStockLevelsChart() {
        const ctx = document.getElementById('stock-levels-chart');
        if (!ctx) return;

        this.charts.stockLevels = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Current Stock',
                    data: [],
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }, {
                    label: 'Reorder Level',
                    data: [],
                    backgroundColor: 'rgba(245, 158, 11, 0.6)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 1,
                    type: 'line'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Items'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' units';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });

        // Load initial data
        this.updateStockLevelsChart();
    }

    /**
     * Initialize event listeners
     */
    initializeEventListeners() {
        // Refresh stock button
        $('#refresh-stock').on('click', () => this.refreshStockData());
        
        // Update menu availability button
        $('#update-menu-availability').on('click', () => this.updateMenuAvailability());
        
        // Export stock report button
        $('#export-stock-report').on('click', () => this.exportStockReport());
        
        // Auto-refresh toggle (if implemented)
        $('#auto-refresh-toggle').on('change', (e) => {
            if (e.target.checked) {
                this.startPeriodicUpdates();
            } else {
                this.stopPeriodicUpdates();
            }
        });
    }

    /**
     * Initialize WebSocket connection for real-time updates
     */
    initializeWebSocket() {
        // Placeholder for WebSocket implementation
        // In a real implementation, you would connect to Laravel Broadcasting
        /*
        this.websocket = new WebSocket('ws://localhost:6001');
        
        this.websocket.onopen = () => {
            console.log('WebSocket connected');
            this.showNotification('success', 'Real-time updates connected');
        };

        this.websocket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleWebSocketMessage(data);
        };

        this.websocket.onclose = () => {
            console.log('WebSocket disconnected');
            this.showNotification('warning', 'Real-time updates disconnected. Attempting to reconnect...');
            setTimeout(() => this.initializeWebSocket(), 5000);
        };

        this.websocket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
        */
    }

    /**
     * Handle WebSocket messages
     */
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'stock_update':
                this.handleStockUpdate(data.payload);
                break;
            case 'new_order':
                this.handleNewOrder(data.payload);
                break;
            case 'order_status_change':
                this.handleOrderStatusChange(data.payload);
                break;
            case 'low_stock_alert':
                this.handleLowStockAlert(data.payload);
                break;
        }
    }

    /**
     * Start periodic updates
     */
    startPeriodicUpdates() {
        this.updateTimer = setInterval(() => {
            this.refreshDashboardData();
        }, this.updateInterval);
    }

    /**
     * Stop periodic updates
     */
    stopPeriodicUpdates() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
            this.updateTimer = null;
        }
    }

    /**
     * Refresh all dashboard data
     */
    async refreshDashboardData() {
        try {
            await Promise.all([
                this.updateStockSummary(),
                this.updateRecentOrders(),
                this.updateLowStockAlerts(),
                this.updateMenuAvailability(),
                this.updateStockLevelsChart()
            ]);
            
            this.showNotification('info', 'Dashboard updated', 2000);
        } catch (error) {
            console.error('Failed to refresh dashboard:', error);
        }
    }

    /**
     * Update stock summary cards
     */
    async updateStockSummary() {
        try {
            const response = await fetch('/admin/api/stock-summary', {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                
                $('#low-stock-count').text(data.low_stock_count || 0);
                $('#unavailable-count').text(data.unavailable_menu_items_count || 0);
                
                // Update critical alerts if any
                if (data.out_of_stock_count > 0) {
                    this.showCriticalAlerts(data);
                } else {
                    $('#critical-alerts').addClass('hidden');
                }
            }
        } catch (error) {
            console.error('Failed to update stock summary:', error);
        }
    }

    /**
     * Update recent orders list
     */
    async updateRecentOrders() {
        try {
            const response = await fetch('/admin/api/recent-orders', {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const orders = await response.json();
                this.renderRecentOrders(orders);
            }
        } catch (error) {
            console.error('Failed to update recent orders:', error);
        }
    }

    /**
     * Update low stock alerts
     */
    async updateLowStockAlerts() {
        try {
            const response = await fetch('/admin/api/low-stock-items', {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const items = await response.json();
                this.renderLowStockAlerts(items);
            }
        } catch (error) {
            console.error('Failed to update low stock alerts:', error);
        }
    }

    /**
     * Update stock levels chart
     */
    async updateStockLevelsChart() {
        try {
            const response = await fetch('/admin/api/stock-levels-chart', {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                
                if (this.charts.stockLevels) {
                    this.charts.stockLevels.data.labels = data.labels;
                    this.charts.stockLevels.data.datasets[0].data = data.current_stock;
                    this.charts.stockLevels.data.datasets[1].data = data.reorder_levels;
                    this.charts.stockLevels.update('none'); // No animation for real-time updates
                }
            }
        } catch (error) {
            console.error('Failed to update stock levels chart:', error);
        }
    }

    /**
     * Refresh stock data manually
     */
    async refreshStockData() {
        const $button = $('#refresh-stock');
        const originalIcon = $button.find('i').attr('class');
        
        // Show loading state
        $button.find('i').attr('class', 'fas fa-spinner fa-spin');
        $button.prop('disabled', true);

        try {
            await this.updateStockSummary();
            await this.updateStockLevelsChart();
            this.showNotification('success', 'Stock data refreshed successfully');
        } catch (error) {
            this.showNotification('error', 'Failed to refresh stock data');
        } finally {
            // Restore button state
            $button.find('i').attr('class', originalIcon);
            $button.prop('disabled', false);
        }
    }

    /**
     * Update menu availability
     */
    async updateMenuAvailability() {
        const $button = $('#update-menu-availability');
        const originalText = $button.html();
        
        // Show loading state
        $button.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...').prop('disabled', true);

        try {
            const branchId = $('meta[name="branch-id"]').attr('content') || 1;
            const response = await fetch(`/admin/api/update-menu-availability/${branchId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.showNotification('success', `Menu availability updated. ${result.updated_count} items affected.`);
                
                // Refresh related sections
                await this.updateStockSummary();
                await this.updateMenuAvailabilityWidget();
            } else {
                throw new Error('Failed to update menu availability');
            }
        } catch (error) {
            console.error('Failed to update menu availability:', error);
            this.showNotification('error', 'Failed to update menu availability');
        } finally {
            // Restore button state
            $button.html(originalText).prop('disabled', false);
        }
    }

    /**
     * Export stock report
     */
    async exportStockReport() {
        const $button = $('#export-stock-report');
        const originalText = $button.html();
        
        // Show loading state
        $button.html('<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...').prop('disabled', true);

        try {
            const response = await fetch('/admin/api/export-stock-report', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `stock-report-${new Date().toISOString().split('T')[0]}.xlsx`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                this.showNotification('success', 'Stock report exported successfully');
            } else {
                throw new Error('Failed to export stock report');
            }
        } catch (error) {
            console.error('Failed to export stock report:', error);
            this.showNotification('error', 'Failed to export stock report');
        } finally {
            // Restore button state
            $button.html(originalText).prop('disabled', false);
        }
    }

    /**
     * Show critical alerts
     */
    showCriticalAlerts(data) {
        const alertsContent = $('#critical-alerts-content');
        let alertsHtml = '';

        if (data.out_of_stock_count > 0) {
            alertsHtml += `<p><strong>${data.out_of_stock_count}</strong> items are completely out of stock and may affect menu availability.</p>`;
        }

        if (data.unavailable_menu_items_count > 0) {
            alertsHtml += `<p><strong>${data.unavailable_menu_items_count}</strong> menu items are currently unavailable due to ingredient shortages.</p>`;
        }

        alertsContent.html(alertsHtml);
        $('#critical-alerts').removeClass('hidden');
    }

    /**
     * Render recent orders
     */
    renderRecentOrders(orders) {
        const container = $('#recent-orders');
        let html = '';

        orders.forEach(order => {
            const statusClass = {
                'confirmed': 'bg-green-100 text-green-800',
                'pending': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800'
            }[order.status] || 'bg-gray-100 text-gray-800';

            html += `
                <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-shopping-cart text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Order #${order.order_number || order.id}</p>
                            <p class="text-sm text-gray-600">${order.customer_name || 'Guest'} • ${this.timeAgo(order.created_at)}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-900">LKR ${this.formatNumber(order.total_amount)}</p>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </span>
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    /**
     * Render low stock alerts
     */
    renderLowStockAlerts(items) {
        const container = $('#low-stock-alerts');
        let html = '';

        items.forEach(item => {
            html += `
                <div class="p-4 border-b last:border-b-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">${item.item.name}</p>
                            <p class="text-sm text-gray-600">Current: ${item.current_stock} ${item.item.unit_of_measurement}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Low Stock
                            </span>
                            <p class="text-xs text-gray-500 mt-1">Reorder: ${item.item.reorder_level}</p>
                        </div>
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    /**
     * Update menu availability widget
     */
    async updateMenuAvailabilityWidget() {
        try {
            const response = await fetch('/admin/api/menu-availability-stats', {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const stats = await response.json();
                
                // Update the widget with new stats
                $('#menu-availability .text-2xl.text-green-600').text(stats.available || 0);
                $('#menu-availability .text-2xl.text-yellow-600').text(stats.low_stock || 0);
                $('#menu-availability .text-2xl.text-red-600').text(stats.out_of_stock || 0);
                
                // Update progress bar
                const percentage = stats.availability_percentage || 0;
                $('#menu-availability .bg-green-500').css('width', percentage + '%');
                $('#menu-availability .text-sm.font-semibold').text(percentage + '%');
            }
        } catch (error) {
            console.error('Failed to update menu availability widget:', error);
        }
    }

    /**
     * Handle real-time stock update
     */
    handleStockUpdate(data) {
        // Update relevant sections
        this.updateStockSummary();
        this.updateStockLevelsChart();
        
        // Show notification if significant change
        if (data.critical_changes) {
            this.showNotification('warning', 'Critical stock changes detected. Please review inventory levels.');
        }
    }

    /**
     * Handle new order notification
     */
    handleNewOrder(order) {
        // Update order count
        const currentCount = parseInt($('#total-orders').text()) || 0;
        $('#total-orders').text(currentCount + 1);
        
        // Update revenue
        const currentRevenue = parseFloat($('#total-revenue').text().replace(/[^\d.]/g, '')) || 0;
        const newRevenue = currentRevenue + parseFloat(order.total_amount);
        $('#total-revenue').text('LKR ' + this.formatNumber(newRevenue));
        
        // Add to recent orders (prepend)
        this.updateRecentOrders();
        
        // Show notification
        this.showNotification('info', `New order #${order.order_number} received (LKR ${this.formatNumber(order.total_amount)})`);
    }

    /**
     * Handle low stock alert
     */
    handleLowStockAlert(item) {
        this.showNotification('warning', `Low stock alert: ${item.name} (${item.current_stock} remaining)`);
        this.updateLowStockAlerts();
    }

    /**
     * Show notification
     */
    showNotification(type, message, duration = 5000) {
        const notificationContainer = $('#notification-container');
        const notificationId = 'notification-' + Date.now();
        
        const typeStyles = {
            success: 'bg-green-50 text-green-800 border-green-200',
            error: 'bg-red-50 text-red-800 border-red-200',
            warning: 'bg-yellow-50 text-yellow-800 border-yellow-200',
            info: 'bg-blue-50 text-blue-800 border-blue-200'
        };

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const notificationHtml = `
            <div id="${notificationId}" class="notification-enter ${typeStyles[type]} border rounded-lg p-4 shadow-lg max-w-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${icons[type]} text-lg"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button type="button" class="inline-flex text-current hover:opacity-75" onclick="$('#${notificationId}').fadeOut(() => $('#${notificationId}').remove())">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        notificationContainer.append(notificationHtml);

        // Auto-remove after specified duration
        if (duration > 0) {
            setTimeout(() => {
                $(`#${notificationId}`).fadeOut(() => {
                    $(`#${notificationId}`).remove();
                });
            }, duration);
        }
    }

    /**
     * Utility functions
     */
    formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }
}

// Initialize dashboard when document is ready
$(document).ready(() => {
    window.dashboard = new RealtimeDashboard();
});
