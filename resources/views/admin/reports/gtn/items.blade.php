@extends('layouts.admin')
@section('header-title', 'GTN Items Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Reports Dashboard', 'link' => route('admin.reports.index')],
            ['name' => 'GTN Reports', 'link' => route('admin.reports.inventory.gtn')],
            ['name' => 'GTN Master', 'link' => route('admin.reports.inventory.gtn.master')],
            ['name' => 'GTN Items', 'link' => route('admin.reports.inventory.gtn.items')],
        ]" active="GTN Items" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">GTN Items Reports</h1>
                <p class="text-gray-600">Detailed item-wise analysis of Goods Transfer Notes with quantities, values, and loss tracking</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.gtn.items', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.gtn.items', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-module-filters 
        :branches="$branches ?? []"
        :customFilters="[
            [
                'name' => 'item_id',
                'label' => 'Item',
                'type' => 'select',
                'placeholder' => 'All Items',
                'options' => isset($items) ? $items->pluck('name', 'id')->toArray() : []
            ],
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
            ],
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'placeholder' => 'All Categories',
                'options' => isset($categories) ? $categories->pluck('name', 'id')->toArray() : []
            ]
        ]"
        showDateRange="true"
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
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Received Qty</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_received_qty']) }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Loss Qty</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_loss_qty']) }}</p>
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
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GTN Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantities</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Values</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loss Analysis</th>
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
                                    <div class="text-sm font-medium text-gray-900">{{ $item['gtn_number'] }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($item['transfer_date'])->format('M d, Y') }}</div>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($item['status'] == 'completed') bg-green-100 text-green-800
                                        @elseif($item['status'] == 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($item['status'] == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($item['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-arrow-right text-gray-400 mr-1"></i>
                                        {{ $item['from_branch'] }}
                                    </div>
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-arrow-left text-gray-400 mr-1"></i>
                                        {{ $item['to_branch'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Sent: {{ number_format($item['transferred_qty'], 2) }}</div>
                                    <div class="text-sm text-green-600">Received: {{ number_format($item['received_qty'], 2) }}</div>
                                    @if($item['damaged_qty'] > 0)
                                        <div class="text-sm text-red-600">Damaged: {{ number_format($item['damaged_qty'], 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($item['unit_price'], 2) }}</div>
                                    @if($item['discounted_price'] < $item['unit_price'])
                                        <div class="text-sm text-green-600">
                                            Disc: {{ number_format($item['discounted_price'], 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ number_format($item['discount_percentage'], 1) }}% off
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Total: {{ number_format($item['total_value'], 2) }}
                                    </div>
                                    @if($item['discount_amount'] > 0)
                                        <div class="text-sm text-green-600">
                                            Discount: -{{ number_format($item['discount_amount'], 2) }}
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            Net: {{ number_format($item['net_value'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item['loss_qty'] > 0)
                                        <div class="text-sm text-red-600">
                                            Loss: {{ number_format($item['loss_qty'], 2) }}
                                        </div>
                                        <div class="text-sm text-red-600">
                                            {{ number_format($item['loss_percentage'], 1) }}%
                                        </div>
                                        <div class="text-sm text-red-600">
                                            Value: {{ number_format($item['loss_value'], 2) }}
                                        </div>
                                    @else
                                        <div class="text-sm text-green-600">No Loss</div>
                                        <div class="text-sm text-gray-500">100% Received</div>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No GTN item records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
