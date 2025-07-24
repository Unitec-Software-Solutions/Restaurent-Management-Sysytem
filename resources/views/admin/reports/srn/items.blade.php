@extends('layouts.admin')
@section('header-title', 'SRN Items Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Reports Dashboard', 'link' => route('admin.reports.index')],
            ['name' => 'SRN Reports', 'link' => route('admin.reports.inventory.srn')],
            ['name' => 'SRN Master', 'link' => route('admin.reports.inventory.srn.master')],
            ['name' => 'SRN Items', 'link' => route('admin.reports.inventory.srn.items')],
        ]" active="SRN Items" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">SRN Items Reports</h1>
                <p class="text-gray-600">Detailed item-wise analysis of Stock Release Notes with release tracking and cost analysis</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.srn.items', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.srn.items', array_merge(request()->query(), ['export' => 'pdf'])) }}"
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
                'name' => 'item_id',
                'label' => 'Item',
                'type' => 'select',
                'placeholder' => 'All Items',
                'options' => isset($items) ? $items->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'placeholder' => 'All Categories',
                'options' => isset($categories) ? $categories->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'release_type',
                'label' => 'Release Type',
                'type' => 'select',
                'placeholder' => 'All Types',
                'options' => [
                    'sale' => 'Sale',
                    'damage' => 'Damage',
                    'wastage' => 'Wastage',
                    'consumption' => 'Consumption',
                    'adjustment' => 'Adjustment'
                ]
            ]
        ]"
        showDateRange="true"
        showBranchFilter="true"
    />

    <!-- Report Results -->
    @if(isset($reportData) && count($reportData['data']) > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes text-indigo-600"></i>
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
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-minus-circle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Released Qty</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_released_qty']) }}</p>
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
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calculator text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Avg Cost</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['average_cost'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Top Release</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['top_release_type'] }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SRN Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch & Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Release Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Analysis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item['item_name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['item_code'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">{{ $item['category'] ?? 'Uncategorized' }}</div>
                                    <div class="text-xs text-gray-400">Unit: {{ $item['unit'] ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item['srn_number'] }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($item['release_date'])->format('M d, Y') }}</div>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($item['status'] == 'released') bg-green-100 text-green-800
                                        @elseif($item['status'] == 'approved') bg-blue-100 text-blue-800
                                        @elseif($item['status'] == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($item['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item['branch_name'] }}</div>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($item['release_type'] == 'sale') bg-green-100 text-green-800
                                        @elseif($item['release_type'] == 'damage') bg-red-100 text-red-800
                                        @elseif($item['release_type'] == 'wastage') bg-orange-100 text-orange-800
                                        @elseif($item['release_type'] == 'consumption') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($item['release_type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Qty: {{ number_format($item['released_qty'], 2) }}
                                    </div>
                                    @if($item['before_stock'] !== null)
                                        <div class="text-sm text-blue-600">
                                            Before: {{ number_format($item['before_stock'], 2) }}
                                        </div>
                                    @endif
                                    @if($item['after_stock'] !== null)
                                        <div class="text-sm text-green-600">
                                            After: {{ number_format($item['after_stock'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Unit: {{ number_format($item['unit_cost'], 2) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        Total: {{ number_format($item['total_cost'], 2) }}
                                    </div>
                                    @if($item['cost_per_unit'] > 0 && $item['cost_per_unit'] != $item['unit_cost'])
                                        <div class="text-sm text-blue-600">
                                            Avg: {{ number_format($item['cost_per_unit'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($item['reason'])
                                        <div class="text-sm text-gray-900 max-w-xs">
                                            {{ Str::limit($item['reason'], 50) }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-400">No reason provided</div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">
                                        By: {{ $item['created_by'] }}
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No SRN item records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
