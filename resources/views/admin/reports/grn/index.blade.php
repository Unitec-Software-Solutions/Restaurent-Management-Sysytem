@extends('layouts.admin')
@section('header-title', 'GRN Reports')

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
        ]" active="Goods Receipt Note Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">GRN Reports Dashboard</h2>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </div>
        </div>
        <p class="text-gray-600">
            Generate comprehensive Goods Receipt Note reports including payment tracking, discount calculations, and item-wise analysis.
        </p>
    </div>

    <!-- Report Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- GRN Master Reports -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">GRN Master Reports</h3>
                    <i class="fas fa-file-invoice text-blue-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Comprehensive reports on GRN masters including payment status, supplier details, discount calculations, and verification tracking.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Payment tracking and outstanding amounts
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Grand discount and item-wise discount calculations
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Supplier and branch-wise analysis
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        PDF and Excel export options
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.grn.master') }}"
                       class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        Generate GRN Master Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- GRN Item Reports -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">GRN Item Reports</h3>
                    <i class="fas fa-list-alt text-purple-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    Detailed item-level reports including quantities, pricing, discounts, batch tracking, and rejection analysis.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Item-wise quantity tracking (ordered, received, accepted)
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Individual item discounts and pricing
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Batch numbers and expiry date tracking
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Rejection reasons and quality analysis
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.reports.inventory.grn.items') }}"
                       class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors block">
                        Generate GRN Item Reports
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
                <p class="text-sm text-gray-600">Filter reports by GRN received date with custom date ranges</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Branch & Supplier</h4>
                <p class="text-sm text-gray-600">Filter by specific branches and suppliers for targeted analysis</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Status & Items</h4>
                <p class="text-sm text-gray-600">Filter by GRN status and specific items for detailed insights</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Key Features</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <i class="fas fa-dollar-sign text-blue-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Payment Tracking</div>
                <div class="text-sm text-gray-600">Track payments and outstanding amounts</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <i class="fas fa-percentage text-purple-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Discount Analysis</div>
                <div class="text-sm text-gray-600">Item-wise and grand discount calculations</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <i class="fas fa-download text-green-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Export Options</div>
                <div class="text-sm text-gray-600">PDF and Excel export capabilities</div>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <i class="fas fa-print text-orange-500 text-2xl mb-2"></i>
                <div class="font-medium text-gray-800">Print Preview</div>
                <div class="text-sm text-gray-600">Browser print preview and download</div>
            </div>
        </div>
    </div>
</div>
@endsection
