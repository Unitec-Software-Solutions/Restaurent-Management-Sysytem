@extends('layouts.admin')
@section('header-title', 'GTN Reports')

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
        ]" active="Goods Transfer Note Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">GTN Reports Dashboard</h2>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </div>
        </div>
        <p class="text-gray-600">
            Generate comprehensive Goods Transfer Note reports including transfer values, loss tracking, and item-wise analysis between branches.
        </p>
    </div>

    <!-- Report Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- GTN Master Reports -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">GTN Master Reports</h3>
                    <i class="fas fa-exchange-alt text-blue-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Comprehensive reports on GTN masters including transfer values, branch-wise tracking, loss analysis, and transfer status monitoring.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Transfer value calculations and tracking
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        From/To branch analysis and routing
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Loss percentage calculations
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Transfer status tracking and timing
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.gtn.master') }}"
                       class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        Generate GTN Master Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- GTN Item Reports -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">GTN Item Reports</h3>
                    <i class="fas fa-list-alt text-purple-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Detailed item-level reports including transfer quantities, received quantities, damage tracking, and quality assessment.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Item-wise quantity tracking (transferred, received, damaged)
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Transfer pricing and line total calculations
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Quality notes and acceptance/rejection tracking
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Batch tracking and expiry management
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.gtn.items') }}"
                       class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        Generate GTN Item Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Options Preview -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Available Filter Options</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Date Range Filtering</h4>
                <p class="text-sm text-gray-600">Filter reports by GTN transfer date with custom date ranges</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Branch Transfer Routes</h4>
                <p class="text-sm text-gray-600">Filter by from/to branches for route-specific analysis</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Status & Items</h4>
                <p class="text-sm text-gray-600">Filter by transfer status and specific items for detailed insights</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Key Features</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <i class="fas fa-route text-blue-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Transfer Routing</div>
                <div class="text-sm text-gray-600">Track inter-branch transfer routes</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <i class="fas fa-chart-line text-purple-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Loss Analysis</div>
                <div class="text-sm text-gray-600">Calculate and track transfer losses</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <i class="fas fa-download text-green-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Export Options</div>
                <div class="text-sm text-gray-600">PDF and Excel export capabilities</div>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <i class="fas fa-quality-control text-orange-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Quality Tracking</div>
                <div class="text-sm text-gray-600">Monitor item quality and acceptance</div>
            </div>
        </div>
    </div>
</div>
@endsection
