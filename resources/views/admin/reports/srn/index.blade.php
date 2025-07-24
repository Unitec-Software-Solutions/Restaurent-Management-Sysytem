@extends('layouts.admin')
@section('header-title', 'SRN Reports')

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
        ]" active="Stock Release Note Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">SRN Reports Dashboard</h2>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </div>
        </div>
        <p class="text-gray-600">
            Generate comprehensive Stock Release Note reports including release tracking, valuation analysis, and release type categorization.
        </p>
    </div>

    <!-- Report Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- SRN Master Reports -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">SRN Master Reports</h3>
                    <i class="fas fa-sign-out-alt text-orange-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Comprehensive reports on SRN masters including release values, branch tracking, release type analysis, and approval workflows.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Release value calculations and tracking
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Branch-wise release analysis
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Release type categorization and reporting
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Approval workflow and verification tracking
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.srn.master') }}"
                       class="w-full bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        Generate SRN Master Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- SRN Item Reports -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">SRN Item Reports</h3>
                    <i class="fas fa-list-alt text-red-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Detailed item-level reports including release quantities, pricing, batch tracking, and release purpose analysis.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Item-wise release quantity tracking
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Release pricing and line total calculations
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Batch numbers and expiry date management
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Release purpose and metadata tracking
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.srn.items') }}"
                       class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        Generate SRN Item Reports
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
                <p class="text-sm text-gray-600">Filter reports by SRN release date with custom date ranges</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Branch & Release Type</h4>
                <p class="text-sm text-gray-600">Filter by specific branches and release types for targeted analysis</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Status & Items</h4>
                <p class="text-sm text-gray-600">Filter by SRN status and specific items for detailed insights</p>
            </div>
        </div>
    </div>

    <!-- Release Types -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Common Release Types</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-3 bg-blue-50 rounded-lg text-center">
                <i class="fas fa-utensils text-blue-500 text-xl mb-2"></i>
                <div class="font-medium text-gray-800">Kitchen Release</div>
                <div class="text-sm text-gray-600">Items released for kitchen use</div>
            </div>
            <div class="p-3 bg-green-50 rounded-lg text-center">
                <i class="fas fa-store text-green-500 text-xl mb-2"></i>
                <div class="font-medium text-gray-800">Store Transfer</div>
                <div class="text-sm text-gray-600">Items transferred to stores</div>
            </div>
            <div class="p-3 bg-red-50 rounded-lg text-center">
                <i class="fas fa-trash text-red-500 text-xl mb-2"></i>
                <div class="font-medium text-gray-800">Waste/Damaged</div>
                <div class="text-sm text-gray-600">Items released due to damage</div>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg text-center">
                <i class="fas fa-exchange text-purple-500 text-xl mb-2"></i>
                <div class="font-medium text-gray-800">Return/Exchange</div>
                <div class="text-sm text-gray-600">Items released for returns</div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Key Features</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <i class="fas fa-clipboard-check text-orange-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Release Tracking</div>
                <div class="text-sm text-gray-600">Track all stock release activities</div>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <i class="fas fa-dollar-sign text-red-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Value Analysis</div>
                <div class="text-sm text-gray-600">Calculate release values and costs</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <i class="fas fa-download text-green-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Export Options</div>
                <div class="text-sm text-gray-600">PDF and Excel export capabilities</div>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <i class="fas fa-user-check text-blue-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Approval Workflow</div>
                <div class="text-sm text-gray-600">Track approvals and verifications</div>
            </div>
        </div>
    </div>
</div>
@endsection
