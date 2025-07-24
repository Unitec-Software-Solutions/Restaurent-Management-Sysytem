@extends('layouts.admin')
@section('header-title', 'GRN Item Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Reports Dashboard', 'link' => route('admin.reports.index')],
            ['name' => 'GRN Reports', 'link' => route('admin.reports.inventory.grn')],
            ['name' => 'GRN Master', 'link' => route('admin.reports.inventory.grn.master')],
            ['name' => 'GRN Items', 'link' => route('admin.reports.inventory.grn.items')],
        ]" active="GRN Items" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">GRN Item Reports</h1>
                <p class="text-gray-600">Detailed item-wise analysis of Goods Receipt Notes with discounts and pricing</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.grn.items', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.grn.items', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-module-filters 
        :branches="$branches ?? []"
        :selectedBranch="$branchId ?? ''"
        :customFilters="[
            [
                'name' => 'supplier_id',
                'label' => 'Supplier',
                'type' => 'select',
                'placeholder' => 'All Suppliers',
                'options' => isset($suppliers) ? $suppliers->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'item_id',
                'label' => 'Item',
                'type' => 'select',
                'placeholder' => 'All Items',
                'options' => isset($items) ? $items->pluck('name', 'id')->toArray() : []
            ]
        ]"
        showDateRange="true"
        showBranchFilter="true"
    />

    <!-- Report Results -->
    @if(isset($reportData) && count($reportData['data']) > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_items'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-weight-hanging text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Quantity</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_quantity']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-percentage text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Discounts</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_discounts'], 2) }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantities</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Info</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item['item_name'] }}</div>
                                    <div class="text-sm text-gray-500">Code: {{ $item['item_code'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $item['item_category'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item['grn_number'] }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($item['received_date'])->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $item['supplier_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Ordered: {{ number_format($item['ordered_quantity']) }}</div>
                                    <div class="text-sm text-green-600">Received: {{ number_format($item['received_quantity']) }}</div>
                                    <div class="text-sm text-blue-600">Accepted: {{ number_format($item['accepted_quantity']) }}</div>
                                    @if($item['rejected_quantity'] > 0)
                                        <div class="text-sm text-red-600">Rejected: {{ number_format($item['rejected_quantity']) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Unit: {{ number_format($item['buying_price'], 2) }}</div>
                                    <div class="text-sm text-gray-600">Total: {{ number_format($item['line_total'], 2) }}</div>
                                    @if($item['discount_amount'] > 0)
                                        <div class="text-sm text-red-600">Discount: {{ number_format($item['discount_amount'], 2) }} ({{ number_format($item['discount_percentage'], 1) }}%)</div>
                                    @endif
                                    <div class="text-sm text-green-600 font-medium">Net: {{ number_format($item['net_amount'], 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item['batch_no'] }}</div>
                                    @if($item['manufacturing_date'])
                                        <div class="text-sm text-gray-500">Mfg: {{ \Carbon\Carbon::parse($item['manufacturing_date'])->format('M d, Y') }}</div>
                                    @endif
                                    @if($item['expiry_date'])
                                        <div class="text-sm text-gray-500">Exp: {{ \Carbon\Carbon::parse($item['expiry_date'])->format('M d, Y') }}</div>
                                    @endif
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No GRN item records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
