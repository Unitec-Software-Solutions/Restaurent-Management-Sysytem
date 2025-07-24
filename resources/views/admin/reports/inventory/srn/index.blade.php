
@extends('layouts.admin')
@section('header-title', 'SRN Reports')

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
        ]" active="Stock Release Note Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stock Release Note (SRN) Report</h1>
                <p class="text-gray-600">Track wastage, damages, and stock releases with cost analysis</p>
            </div>
            <div class="flex gap-2">
                {{-- <a href="{{ route('admin.exports.multisheet', ['reportType' => 'stock_release_note_master']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel (Multi-Sheet)
                </a> --}}

                <!-- PDF Export Dropdown -->
                <div class="relative inline-block text-left">
                    <button type="button" id="pdf-dropdown-button" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i> Export PDF <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="pdf-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50 hidden">
                        <div class="py-1">
                            <a href="#" onclick="exportPDF('detailed')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-list-alt mr-2"></i> Detailed View
                            </a>
                            <a href="#" onclick="exportPDF('summary')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-chart-bar mr-2"></i> Summary View
                            </a>
                            <a href="#" onclick="exportPDF('master_only')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-table mr-2"></i> Master Records Only
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.reports.inventory.srn', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel (Multi-Sheet)
                </a>
                <a href="{{ route('admin.reports.inventory.srn', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.reports.inventory.srn') }}" class="space-y-4">
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

                <!-- Release Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Release Type</label>
                    <select name="release_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="wastage" {{ $releaseType == 'wastage' ? 'selected' : '' }}>Wastage</option>
                        <option value="damage" {{ $releaseType == 'damage' ? 'selected' : '' }}>Damage</option>
                        <option value="expired" {{ $releaseType == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="theft" {{ $releaseType == 'theft' ? 'selected' : '' }}>Theft</option>
                        <option value="adjustment" {{ $releaseType == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        <option value="other" {{ $releaseType == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="processed" {{ $status == 'processed' ? 'selected' : '' }}>Processed</option>
                    </select>
                </div>

                <!-- Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
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
                <a href="{{ route('admin.reports.inventory.srn') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-undo mr-2"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Report Results -->
    @if(isset($reportData) && $reportData['srns']->count() > 0)
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
                        <p class="text-sm font-medium text-gray-500">Total SRNs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_srns'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-trash text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Released Quantity</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_released_quantity'], 0) }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Total Cost Impact</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_cost_impact'], 2) }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Avg. Daily Loss</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['avg_daily_loss'], 2) }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SRN Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Release Type</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Released Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Impact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['srns'] as $srn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $srn['srn_number'] }}</div>
                                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($srn['release_date'])->format('M d, Y') }}</div>
                                        @if($srn['approved_date'])
                                            <div class="text-xs text-gray-400">Approved: {{ \Carbon\Carbon::parse($srn['approved_date'])->format('M d, Y') }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $srn['branch_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $typeColors = [
                                            'wastage' => 'bg-red-100 text-red-800',
                                            'damage' => 'bg-orange-100 text-orange-800',
                                            'expired' => 'bg-purple-100 text-purple-800',
                                            'theft' => 'bg-gray-100 text-gray-800',
                                            'adjustment' => 'bg-blue-100 text-blue-800',
                                            'other' => 'bg-yellow-100 text-yellow-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $typeColors[$srn['release_type']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($srn['release_type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">{{ $srn['items_count'] }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($srn['total_quantity'], 0) }} units</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'processed' => 'bg-blue-100 text-blue-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$srn['status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($srn['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($srn['released_quantity'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    Rs. {{ number_format($srn['cost_impact'], 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $srn['reason'] ? Str::limit($srn['reason'], 50) : '-' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Analysis -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Release Type Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Loss by Release Type</h3>
                @php
                    $typeAnalysis = $reportData['srns']->groupBy('release_type')
                        ->map(function ($group) {
                            return [
                                'count' => $group->count(),
                                'cost' => $group->sum('cost_impact'),
                                'quantity' => $group->sum('released_quantity')
                            ];
                        })
                        ->sortByDesc('cost');
                @endphp
                <div class="space-y-3">
                    @foreach($typeAnalysis as $type => $data)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ ucfirst($type) }}</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $data['count'] }} SRNs)</span>
                                <div class="text-xs text-gray-500">{{ number_format($data['quantity'], 0) }} units</div>
                            </div>
                            <span class="text-sm font-semibold text-red-600">Rs. {{ number_format($data['cost'], 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Trend Analysis -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Overview</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Approved Releases</div>
                            <div class="text-xs text-gray-500">{{ $reportData['srns']->where('status', 'approved')->count() }} SRNs</div>
                        </div>
                        <div class="text-lg font-semibold text-green-600">
                            Rs. {{ number_format($reportData['srns']->where('status', 'approved')->sum('cost_impact'), 0) }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Pending Approval</div>
                            <div class="text-xs text-gray-500">{{ $reportData['srns']->where('status', 'pending')->count() }} SRNs</div>
                        </div>
                        <div class="text-lg font-semibold text-yellow-600">
                            Rs. {{ number_format($reportData['srns']->where('status', 'pending')->sum('cost_impact'), 0) }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Processed</div>
                            <div class="text-xs text-gray-500">{{ $reportData['srns']->where('status', 'processed')->count() }} SRNs</div>
                        </div>
                        <div class="text-lg font-semibold text-blue-600">
                            Rs. {{ number_format($reportData['srns']->where('status', 'processed')->sum('cost_impact'), 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Impact Summary -->
        <div class="mt-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Branch-wise Loss Analysis</h3>
                @php
                    $branchAnalysis = $reportData['srns']->groupBy('branch_name')
                        ->map(function ($group) {
                            return [
                                'cost' => $group->sum('cost_impact'),
                                'quantity' => $group->sum('released_quantity'),
                                'srn_count' => $group->count(),
                                'avg_cost' => $group->avg('cost_impact')
                            ];
                        })
                        ->sortByDesc('cost');
                @endphp
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Branch</th>
                                <th class="text-center py-2">SRNs</th>
                                <th class="text-right py-2">Total Cost Impact</th>
                                <th class="text-right py-2">Released Quantity</th>
                                <th class="text-right py-2">Avg Cost per SRN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branchAnalysis as $branch => $data)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 text-sm font-medium text-gray-900">{{ $branch }}</td>
                                    <td class="py-3 text-sm text-center text-gray-900">{{ $data['srn_count'] }}</td>
                                    <td class="py-3 text-sm text-right text-red-600">Rs. {{ number_format($data['cost'], 2) }}</td>
                                    <td class="py-3 text-sm text-right text-gray-900">{{ number_format($data['quantity'], 0) }}</td>
                                    <td class="py-3 text-sm text-right text-gray-600">Rs. {{ number_format($data['avg_cost'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No SRNs Found</h3>
            <p class="text-gray-500">No Stock Release Notes found for the selected criteria. Try adjusting your filters.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // PDF Dropdown functionality
    const pdfDropdownButton = document.getElementById('pdf-dropdown-button');
    const pdfDropdownMenu = document.getElementById('pdf-dropdown-menu');

    if (pdfDropdownButton && pdfDropdownMenu) {
        pdfDropdownButton.addEventListener('click', function(e) {
            e.preventDefault();
            pdfDropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!pdfDropdownButton.contains(e.target) && !pdfDropdownMenu.contains(e.target)) {
                pdfDropdownMenu.classList.add('hidden');
            }
        });
    }

    // PDF Export function
    function exportPDF(viewType) {
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.set('export', 'pdf');
        currentParams.set('view_type', viewType);

        const exportUrl = `{{ route('admin.reports.inventory.srn') }}?${currentParams.toString()}`;
        window.open(exportUrl, '_blank');

        // Hide dropdown after export
        document.getElementById('pdf-dropdown-menu').classList.add('hidden');
    }
</script>
@endpush
