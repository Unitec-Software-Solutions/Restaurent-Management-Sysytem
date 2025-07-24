@extends('layouts.admin')
@section('header-title', 'Stock Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Inventory Report', 'link' => route('admin.reports.inventory.index')],
            ['name' => 'Stock Report', 'link' => route('admin.reports.inventory.stock')],
            ['name' => 'Category Report', 'link' => route('admin.reports.inventory.category')],
            ['name' => 'Goods Transfer Note Report', 'link' => route('admin.reports.inventory.gtn')],
            ['name' => 'Goods Receipt Note Report', 'link' => route('admin.reports.inventory.grn')],
            ['name' => 'Stock Release Note Report', 'link' => route('admin.reports.inventory.srn')],
        ]" active="Stock Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Stock Reports Dashboard</h2>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </div>
        </div>
        <p class="text-gray-600">
            Generate comprehensive stock reports including current stock levels, stock movement tracking, and stock valuation analysis across all inventory items.
        </p>
    </div>

    <!-- Report Types -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Current Stock Report -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Current Stock Levels</h3>
                    <i class="fas fa-boxes text-blue-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Real-time inventory levels with stock status analysis, reorder alerts, and valuation summaries.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Current stock quantities by item and branch
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Stock status indicators (Low, Normal, Overstock)
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Reorder level monitoring and alerts
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Current stock valuation calculations
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.stock.current') }}"
                       class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        View Current Stock Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Movement Report -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Stock Movement</h3>
                    <i class="fas fa-arrows-alt text-purple-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Detailed transaction tracking for all inventory movements including receipts, transfers, and releases.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        All stock-in and stock-out transactions
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Transaction type categorization and analysis
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Reference document tracking (GRN, GTN, SRN)
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Stock before/after transaction tracking
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.stock.movement') }}"
                       class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        View Movement Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Valuation Report -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Stock Valuation</h3>
                    <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Comprehensive inventory valuation using FIFO, LIFO, or weighted average methods for accurate financial reporting.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Multiple valuation methods (FIFO, LIFO, Weighted Avg)
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Historical cost vs current value analysis
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Variance calculations and reporting
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        As-of-date valuation snapshots
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.stock.valuation') }}"
                       class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        View Valuation Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Options Preview -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Available Filter Options</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Date Range</h4>
                <p class="text-sm text-gray-600">Filter by custom date ranges for historical analysis</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Branch Selection</h4>
                <p class="text-sm text-gray-600">Select specific branches for location-based reporting</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Item Categories</h4>
                <p class="text-sm text-gray-600">Filter by item categories and individual items</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Stock Status</h4>
                <p class="text-sm text-gray-600">Filter by stock levels (Low, Normal, Overstock)</p>
            </div>
        </div>
    </div>

    <!-- Stock Status Overview -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock Status Categories</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 bg-green-50 rounded-lg text-center">
                <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Normal Stock</div>
                <div class="text-sm text-gray-600">Items within optimal levels</div>
            </div>
            <div class="p-4 bg-yellow-50 rounded-lg text-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Low Stock</div>
                <div class="text-sm text-gray-600">Items below reorder level</div>
            </div>
            <div class="p-4 bg-red-50 rounded-lg text-center">
                <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Out of Stock</div>
                <div class="text-sm text-gray-600">Items with zero quantity</div>
            </div>
            <div class="p-4 bg-blue-50 rounded-lg text-center">
                <i class="fas fa-arrow-up text-blue-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Overstock</div>
                <div class="text-sm text-gray-600">Items above maximum levels</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.reports.inventory.stock.current') }}?low_stock_only=1"
               class="flex items-center p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                <div>
                    <div class="font-medium text-gray-800">Low Stock Alert</div>
                    <div class="text-sm text-gray-600">View items requiring reorder</div>
                </div>
            </a>
            <a href="{{ route('admin.reports.inventory.stock.movement') }}?transaction_type=stock_in"
               class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-down text-green-500 mr-3"></i>
                <div>
                    <div class="font-medium text-gray-800">Recent Stock In</div>
                    <div class="text-sm text-gray-600">View recent inventory receipts</div>
                </div>
            </a>
            <a href="{{ route('admin.reports.inventory.stock.valuation') }}?valuation_method=fifo"
               class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <i class="fas fa-calculator text-blue-500 mr-3"></i>
                <div>
                    <div class="font-medium text-gray-800">FIFO Valuation</div>
                    <div class="text-sm text-gray-600">Calculate FIFO-based values</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
