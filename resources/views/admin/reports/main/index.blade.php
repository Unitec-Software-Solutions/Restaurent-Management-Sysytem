@extends('layouts.admin')
@section('header-title', 'Reports Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Reports Overview -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Reports Dashboard</h2>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </div>
        </div>
        <p class="text-gray-600">
            Access comprehensive reports for inventory management, including GRN, GTN, SRN, and stock reports.
        </p>
    </div>

    <!-- Report Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- GRN Reports Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">GRN Reports</h3>
                    <i class="fas fa-truck text-blue-500 text-2xl"></i>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    Goods Receipt Note reports with payment tracking and discount calculations
                </div>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $reportSummary['grn']['total_count'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Total GRNs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${{ number_format($reportSummary['grn']['total_value'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Total Value</div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reports.inventory.grn') }}"
                       class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        View Reports
                    </a>
                    <a href="{{ route('admin.reports.inventory.grn.master') }}"
                       class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        Master
                    </a>
                </div>
            </div>
        </div>

        <!-- GTN Reports Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">GTN Reports</h3>
                    <i class="fas fa-exchange-alt text-purple-500 text-2xl"></i>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    Goods Transfer Note reports with transfer values and item-wise tracking
                </div>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $reportSummary['gtn']['total_count'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Total GTNs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${{ number_format($reportSummary['gtn']['total_value'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Total Value</div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reports.inventory.gtn') }}"
                       class="flex-1 bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        View Reports
                    </a>
                    <a href="{{ route('admin.reports.inventory.gtn.master') }}"
                       class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        Master
                    </a>
                </div>
            </div>
        </div>

        <!-- SRN Reports Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">SRN Reports</h3>
                    <i class="fas fa-sign-out-alt text-orange-500 text-2xl"></i>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    Stock Release Note reports with release tracking and valuation
                </div>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $reportSummary['srn']['total_count'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Total SRNs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${{ number_format($reportSummary['srn']['total_value'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Total Value</div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reports.inventory.srn') }}"
                       class="flex-1 bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        View Reports
                    </a>
                    <a href="{{ route('admin.reports.inventory.srn.master') }}"
                       class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        Master
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Reports Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Stock Reports</h3>
                    <i class="fas fa-boxes text-green-500 text-2xl"></i>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    Current stock, stock movement, and stock valuation reports
                </div>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $reportSummary['stock']['total_transactions'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Transactions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${{ number_format($reportSummary['stock']['net_value'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Net Value</div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reports.inventory.stock') }}"
                       class="flex-1 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        View Reports
                    </a>
                    <a href="{{ route('admin.reports.inventory.stock.current') }}"
                       class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded text-sm text-center transition-colors">
                        Current
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.reports.inventory.stock.current') }}"
               class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <i class="fas fa-chart-bar text-blue-500 mr-3"></i>
                <div>
                    <div class="font-medium text-gray-800">Current Stock Report</div>
                    <div class="text-sm text-gray-600">View current inventory levels</div>
                </div>
            </a>
            <a href="{{ route('admin.reports.inventory.stock.movement') }}"
               class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                <i class="fas fa-arrows-alt text-purple-500 mr-3"></i>
                <div>
                    <div class="font-medium text-gray-800">Stock Movement</div>
                    <div class="text-sm text-gray-600">Track inventory transactions</div>
                </div>
            </a>
            <a href="{{ route('admin.reports.inventory.stock.valuation') }}"
               class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <i class="fas fa-dollar-sign text-green-500 mr-3"></i>
                <div>
                    <div class="font-medium text-gray-800">Stock Valuation</div>
                    <div class="text-sm text-gray-600">Calculate inventory value</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
