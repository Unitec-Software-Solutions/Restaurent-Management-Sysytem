@extends('layouts.admin')
@section('header-title', 'GTN Master Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    {{-- <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Reports Dashboard', 'link' => route('admin.reports.index')],
            ['name' => 'GTN Reports', 'link' => route('admin.reports.inventory.gtn')],
            ['name' => 'GTN Master', 'link' => route('admin.reports.inventory.gtn.master')],
            ['name' => 'GTN Items', 'link' => route('admin.reports.inventory.gtn.items')],
        ]" active="GTN Master" />
    </div> --}}

    <!-- Report Header -->
    {{-- <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">GTN Master Reports</h1>
                <p class="text-gray-600">Comprehensive overview of Goods Transfer Notes with transfer values and branch analysis</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.gtn.master', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.gtn.master', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </a>
            </div>
        </div>
    </div> --}}

    <!-- Filters -->
    {{-- <x-module-filters 
        :branches="$branches ?? []"
        :statusOptions="[
            'pending' => 'Pending',
            'confirmed' => 'Confirmed', 
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ]"
        :selectedStatus="$status ?? ''"
        :customFilters="[
            [
                'name' => 'from_branch_id',
                'label' => 'From Branch',
                'type' => 'select',
                'placeholder' => 'All Branches',
                'options' => isset($branches) ? $branches->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'to_branch_id',
                'label' => 'To Branch',
                'type' => 'select',
                'placeholder' => 'All Branches',
                'options' => isset($branches) ? $branches->pluck('name', 'id')->toArray() : []
            ]
        ]"
        showDateRange="true"
        showStatusFilter="true"
    /> --}}

    <!-- Report Results -->
    {{-- @if(isset($reportData) && count($reportData['data']) > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total GTNs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_gtns'] }}</p>
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
                            <i class="fas fa-truck text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Transferred Qty</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_transferred_qty']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Loss %</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['overall_loss_percentage'], 1) }}%</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GTN Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantities</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value & Loss</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $gtn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $gtn['gtn_number'] }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($gtn['transfer_date'])->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">Items: {{ $gtn['items_count'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-arrow-right text-gray-400 mx-2"></i>
                                        From: {{ $gtn['from_branch'] }}
                                    </div>
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-arrow-left text-gray-400 mx-2"></i>
                                        To: {{ $gtn['to_branch'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Sent: {{ number_format($gtn['transferred_qty']) }}</div>
                                    <div class="text-sm text-green-600">Received: {{ number_format($gtn['received_qty']) }}</div>
                                    @if($gtn['damaged_qty'] > 0)
                                        <div class="text-sm text-red-600">Damaged: {{ number_format($gtn['damaged_qty']) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Value: {{ number_format($gtn['total_value'], 2) }}</div>
                                    @if($gtn['loss_percentage'] > 0)
                                        <div class="text-sm text-red-600">Loss: {{ number_format($gtn['loss_percentage'], 1) }}%</div>
                                    @else
                                        <div class="text-sm text-green-600">No Loss</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($gtn['status'] == 'completed') bg-green-100 text-green-800
                                        @elseif($gtn['status'] == 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($gtn['status'] == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($gtn['status']) }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        By: {{ $gtn['created_by'] }}
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No GTN records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif --}}
</div>
@endsection
