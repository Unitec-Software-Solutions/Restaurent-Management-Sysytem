@extends('layouts.admin')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Dashboard Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Real-time Inventory & Orders Dashboard</h1>
                <p class="text-gray-600">Monitor stock levels, order status, and menu availability in real-time</p>
            </div>

            <!-- Stock Alert Banner -->
            <div id="critical-alerts" class="hidden mb-6">
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800">Critical Stock Alerts</h3>
                            <div id="critical-alerts-content" class="mt-2 text-sm text-red-700">
                                <!-- Critical alerts will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Orders Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Orders Today</h3>
                            <p class="text-3xl font-bold text-gray-900" id="total-orders">{{ $stats['total_orders'] ?? 0 }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <span id="orders-change">+12%</span> from yesterday
                            </p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Revenue Today</h3>
                            <p class="text-3xl font-bold text-gray-900" id="total-revenue">LKR {{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <span id="revenue-change">+8%</span> from yesterday
                            </p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Low Stock Items</h3>
                            <p class="text-3xl font-bold text-gray-900" id="low-stock-count">{{ $stats['low_stock_count'] ?? 0 }}</p>
                            <p class="text-sm text-yellow-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Needs attention
                            </p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-box text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Unavailable Menu Items -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Unavailable Items</h3>
                            <p class="text-3xl font-bold text-gray-900" id="unavailable-count">{{ $stats['unavailable_menu_items_count'] ?? 0 }}</p>
                            <p class="text-sm text-red-600 mt-1">
                                <i class="fas fa-times-circle mr-1"></i>
                                Out of stock
                            </p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-ban text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Stock Status & Recent Orders -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Real-time Stock Status -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-line mr-2 text-indigo-600"></i>
                                    Real-time Stock Levels
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <div class="flex items-center text-sm">
                                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                        <span class="text-gray-600">Live Updates</span>
                                    </div>
                                    <button id="refresh-stock" class="text-indigo-600 hover:text-indigo-800">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="stock-levels-chart" style="height: 300px;">
                                <!-- Chart will be rendered here -->
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-clock mr-2 text-blue-600"></i>
                                Recent Orders
                            </h3>
                        </div>
                        <div class="divide-y" id="recent-orders">
                            @foreach($recentOrders ?? [] as $order)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-shopping-cart text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Order #{{ $order->order_number ?? $order->id }}</p>
                                        <p class="text-sm text-gray-600">{{ $order->customer_name ?? 'Guest' }} • {{ $order->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">LKR {{ number_format($order->total_amount, 2) }}</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $order->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Quick Actions & Alerts -->
                <div class="space-y-8">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('admin.orders.enhanced-create') }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Create New Order
                            </a>
                            <button id="update-menu-availability" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-sync mr-2"></i>
                                Update Menu Availability
                            </button>
                            <a href="{{ route('admin.inventory.stock.index') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-warehouse mr-2"></i>
                                Manage Inventory
                            </a>
                            <button id="export-stock-report" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export Stock Report
                            </button>
                        </div>
                    </div>

                    <!-- Low Stock Alerts -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>
                                Low Stock Alerts
                            </h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto" id="low-stock-alerts">
                            @foreach($lowStockItems ?? [] as $item)
                            <div class="p-4 border-b last:border-b-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $item['item']->name }}</p>
                                        <p class="text-sm text-gray-600">Current: {{ $item['current_stock'] }} {{ $item['item']->unit_of_measurement }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low Stock
                                        </span>
                                        <p class="text-xs text-gray-500 mt-1">Reorder: {{ $item['item']->reorder_level }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Menu Availability -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-utensils mr-2 text-green-600"></i>
                                Menu Availability
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4" id="menu-availability">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Available Items</span>
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $menuStats['availability_percentage'] ?? 0 }}%"></div>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900">{{ $menuStats['availability_percentage'] ?? 0 }}%</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <p class="text-2xl font-bold text-green-600">{{ $menuStats['available'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-600">Available</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-yellow-600">{{ $menuStats['low_stock'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-600">Low Stock</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-red-600">{{ $menuStats['out_of_stock'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-600">Out of Stock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 400px;">
    <!-- Real-time notifications will appear here -->
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/dashboard-realtime.js') }}"></script>
@endpush

@push('styles')
<style>
.pulse-dot {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.notification-enter {
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.chart-container {
    position: relative;
    height: 300px;
}
</style>
@endpush
