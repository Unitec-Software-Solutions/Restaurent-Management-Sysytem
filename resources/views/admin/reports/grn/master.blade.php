@extends('layouts.admin')
@section('header-title', 'GRN Master Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Reports Dashboard', 'link' => route('admin.reports.index')],
            ['name' => 'GRN Reports', 'link' => route('admin.reports.inventory.grn')],
            ['name' => 'GRN Master', 'link' => route('admin.reports.inventory.grn.master')],
            ['name' => 'GRN Items', 'link' => route('admin.reports.inventory.grn.items')],
        ]" active="GRN Master" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">GRN Master Reports</h1>
                <p class="text-gray-600">Comprehensive overview of Goods Received Notes with payment tracking and discount analysis</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.grn.master', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.grn.master', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </a>
                <a href="{{ route('admin.reports.inventory.grn.master', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-download mr-2"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-module-filters 
        :branches="$branches ?? []"
        :selectedBranch="$branchId ?? ''"
        :statusOptions="[
            'pending' => 'Pending',
            'approved' => 'Approved', 
            'received' => 'Received',
            'cancelled' => 'Cancelled'
        ]"
        :selectedStatus="$status ?? ''"
        :customFilters="[
            [
                'name' => 'supplier_id',
                'label' => 'Supplier',
                'type' => 'select',
                'placeholder' => 'All Suppliers',
                'options' => isset($suppliers) ? $suppliers->pluck('name', 'id')->toArray() : []
            ]
        ]"
        showDateRange="true"
        showBranchFilter="true"
        showStatusFilter="true"
    />

    <!-- Report Results -->
    @if(isset($reportData) && is_array($reportData) && count($reportData['data'] ?? []) > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-import text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total GRNs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_grns'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_value'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-money-bill text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Paid Amount</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_paid'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-percentage text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Discount</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_discount'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $grn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $grn['grn_number'] ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ isset($grn['received_date']) ? \Carbon\Carbon::parse($grn['received_date'])->format('M d, Y') : 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">Reference: {{ $grn['reference_number'] ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $grn['supplier_name'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $grn['supplier_code'] ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $grn['branch_name'] ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Count: {{ $grn['items_count'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-500">Qty: {{ number_format($grn['total_quantity'] ?? 0, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Total: {{ number_format($grn['total_value'] ?? 0, 2) }}</div>
                                    <div class="text-sm text-green-600">Paid: {{ number_format($grn['paid_amount'] ?? 0, 2) }}</div>
                                    @if(($grn['discount_amount'] ?? 0) > 0)
                                        <div class="text-sm text-blue-600">Discount: {{ number_format($grn['discount_amount'], 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if(($grn['status'] ?? '') == 'received') bg-green-100 text-green-800
                                        @elseif(($grn['status'] ?? '') == 'approved') bg-blue-100 text-blue-800
                                        @elseif(($grn['status'] ?? '') == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($grn['status'] ?? 'unknown') }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        By: {{ $grn['created_by'] ?? 'N/A' }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No GRN records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
