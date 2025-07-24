@extends('layouts.admin')
@section('header-title', 'SRN Master Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Reports Dashboard', 'link' => route('admin.reports.index')],
            ['name' => 'SRN Reports', 'link' => route('admin.reports.inventory.srn')],
            ['name' => 'SRN Master', 'link' => route('admin.reports.inventory.srn.master')],
            ['name' => 'SRN Items', 'link' => route('admin.reports.inventory.srn.items')],
        ]" active="SRN Master" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">SRN Master Reports</h1>
                <p class="text-gray-600">Comprehensive overview of Stock Release Notes with release values and branch analysis</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.srn.master', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.srn.master', array_merge(request()->query(), ['export' => 'pdf'])) }}"
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
        :statusOptions="[
            'pending' => 'Pending',
            'approved' => 'Approved', 
            'released' => 'Released',
            'cancelled' => 'Cancelled'
        ]"
        :selectedStatus="$status ?? ''"
        :customFilters="[
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
        showStatusFilter="true"
    />

    <!-- Report Results -->
    @if(isset($reportData) && count($reportData['data']) > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-export text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total SRNs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_srns'] }}</p>
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
                            <i class="fas fa-layer-group text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Items Count</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_items'] }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SRN Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch & Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Release Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Values</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $srn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $srn['srn_number'] }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($srn['release_date'])->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">Items: {{ $srn['items_count'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $srn['branch_name'] }}</div>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($srn['release_type'] == 'sale') bg-green-100 text-green-800
                                        @elseif($srn['release_type'] == 'damage') bg-red-100 text-red-800
                                        @elseif($srn['release_type'] == 'wastage') bg-orange-100 text-orange-800
                                        @elseif($srn['release_type'] == 'consumption') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($srn['release_type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Qty: {{ number_format($srn['total_released_qty']) }}</div>
                                    @if($srn['reason'])
                                        <div class="text-sm text-gray-500">{{ Str::limit($srn['reason'], 30) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($srn['total_value'], 2) }}</div>
                                    @if($srn['average_cost'] > 0)
                                        <div class="text-sm text-gray-500">Avg: {{ number_format($srn['average_cost'], 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($srn['status'] == 'released') bg-green-100 text-green-800
                                        @elseif($srn['status'] == 'approved') bg-blue-100 text-blue-800
                                        @elseif($srn['status'] == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($srn['status']) }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        By: {{ $srn['created_by'] }}
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No SRN records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
