
@extends('layouts.admin')
@section('header-title', 'GTN Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg">
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
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Goods Transfer Note (GTN) Report</h1>
                <p class="text-gray-600">Track inter-branch transfer statuses and stock movements</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-download mr-2"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.reports.inventory.gtn') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Origin Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Origin Status</label>
                    <select name="origin_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ $originStatus == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ $originStatus == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="in_delivery" {{ $originStatus == 'in_delivery' ? 'selected' : '' }}>In Delivery</option>
                        <option value="delivered" {{ $originStatus == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>

                <!-- Receiver Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Status</label>
                    <select name="receiver_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $receiverStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="received" {{ $receiverStatus == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="verified" {{ $receiverStatus == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="accepted" {{ $receiverStatus == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ $receiverStatus == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="partially_accepted" {{ $receiverStatus == 'partially_accepted' ? 'selected' : '' }}>Partially Accepted</option>
                    </select>
                </div>

                <!-- From Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Branch</label>
                    <select name="from_branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $fromBranchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- To Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Branch</label>
                    <select name="to_branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $toBranchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i> Generate Report
                </button>
                <a href="{{ route('admin.reports.inventory.gtn') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-undo mr-2"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Report Results -->
    @if(isset($reportData) && $reportData['gtns']->count() > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600"></i>
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
                        <p class="text-sm font-medium text-gray-500">Total Transfer Value</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_transfer_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Accepted Value</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_accepted_value'], 2) }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Acceptance Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['overall_acceptance_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GTN Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From → To</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Origin Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Receiver Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Value</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Accepted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acceptance Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['gtns'] as $gtn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $gtn['gtn_number'] }}</div>
                                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($gtn['transfer_date'])->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $gtn['from_branch'] }}</span>
                                        <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                        <span class="font-medium">{{ $gtn['to_branch'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">{{ $gtn['items_count'] }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($gtn['total_quantity'], 0) }} units</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $originColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'confirmed' => 'bg-blue-100 text-blue-800',
                                            'in_delivery' => 'bg-yellow-100 text-yellow-800',
                                            'delivered' => 'bg-green-100 text-green-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $originColors[$gtn['origin_status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $gtn['origin_status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $receiverColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'received' => 'bg-blue-100 text-blue-800',
                                            'verified' => 'bg-purple-100 text-purple-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'partially_accepted' => 'bg-orange-100 text-orange-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $receiverColors[$gtn['receiver_status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $gtn['receiver_status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    Rs. {{ number_format($gtn['total_transfer_value'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    {{ number_format($gtn['accepted_quantity'], 0) }}
                                    <div class="text-xs text-gray-500">Rs. {{ number_format($gtn['total_accepted_value'], 0) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    {{ number_format($gtn['rejected_quantity'], 0) }}
                                    <div class="text-xs text-gray-500">Rs. {{ number_format($gtn['total_rejected_value'], 0) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $acceptanceRate = $gtn['acceptance_rate'];
                                        $rateClass = $acceptanceRate >= 90 ? 'bg-green-100 text-green-800' :
                                                    ($acceptanceRate >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $rateClass }}">
                                        {{ number_format($acceptanceRate, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Analysis -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Status Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">GTN Status Distribution</h3>
                @php
                    $statusCounts = $reportData['gtns']->groupBy('receiver_status')->map->count();
                @endphp
                <div class="space-y-3">
                    @foreach($statusCounts as $status => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2">{{ $count }}</span>
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $reportData['summary']['total_gtns'] > 0 ? ($count / $reportData['summary']['total_gtns']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">High Acceptance Rate</div>
                            <div class="text-xs text-gray-500">≥90% acceptance</div>
                        </div>
                        <div class="text-lg font-semibold text-green-600">
                            {{ $reportData['gtns']->where('acceptance_rate', '>=', 90)->count() }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Moderate Acceptance</div>
                            <div class="text-xs text-gray-500">70-89% acceptance</div>
                        </div>
                        <div class="text-lg font-semibold text-yellow-600">
                            {{ $reportData['gtns']->whereBetween('acceptance_rate', [70, 89])->count() }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Low Acceptance</div>
                            <div class="text-xs text-gray-500">&lt;70% acceptance</div>
                        </div>
                        <div class="text-lg font-semibold text-red-600">
                            {{ $reportData['gtns']->where('acceptance_rate', '<', 70)->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No GTNs Found</h3>
            <p class="text-gray-500">No Goods Transfer Notes found for the selected criteria. Try adjusting your filters.</p>
        </div>
    @endif
</div>
@endsection
