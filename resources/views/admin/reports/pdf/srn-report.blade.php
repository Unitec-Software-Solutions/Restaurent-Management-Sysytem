@extends('admin.reports.pdf.base')

@section('content')
<!-- Filters Applied -->
@if(!empty($filters))
<div class="filters-section">
    <div class="filters-title">Applied Filters</div>
    <div class="filters-grid">
        @if(isset($filters['date_from']) && $filters['date_from'])
            <div class="filter-item">
                <span class="filter-label">From Date:</span> {{ $filters['date_from'] }}
            </div>
        @endif
        @if(isset($filters['date_to']) && $filters['date_to'])
            <div class="filter-item">
                <span class="filter-label">To Date:</span> {{ $filters['date_to'] }}
            </div>
        @endif
        @if(isset($filters['status']) && $filters['status'])
            <div class="filter-item">
                <span class="filter-label">Status:</span> {{ ucfirst($filters['status']) }}
            </div>
        @endif
        @if(isset($filters['branch_name']) && $filters['branch_name'])
            <div class="filter-item">
                <span class="filter-label">Branch:</span> {{ $filters['branch_name'] }}
            </div>
        @endif
        @if(isset($filters['release_type']) && $filters['release_type'])
            <div class="filter-item">
                <span class="filter-label">Release Type:</span> {{ ucfirst($filters['release_type']) }}
            </div>
        @endif
    </div>
</div>
@endif

<!-- Summary Statistics -->
@php
    $totalSrns = is_countable($srns) ? $srns->count() : count($srns);
    $wastageCount = collect($srns)->where('release_type', 'wastage')->count();
    $damageCount = collect($srns)->where('release_type', 'damage')->count();
    $usageCount = collect($srns)->where('release_type', 'usage')->count();
    $totalQuantity = collect($srns)->sum('total_quantity');
    $totalReleasedQty = collect($srns)->sum('released_quantity');
    $totalCostImpact = collect($srns)->sum('cost_impact');
    $avgCostImpact = $totalSrns > 0 ? $totalCostImpact / $totalSrns : 0;
@endphp

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalSrns) }}</div>
            <div class="summary-label">Total SRNs</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($wastageCount) }}</div>
            <div class="summary-label">Wastage</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-danger">{{ number_format($damageCount) }}</div>
            <div class="summary-label">Damage</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-primary">{{ number_format($usageCount) }}</div>
            <div class="summary-label">Usage</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalQuantity, 2) }}</div>
            <div class="summary-label">Total Quantity</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalReleasedQty, 2) }}</div>
            <div class="summary-label">Released Qty</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-danger">Rs. {{ number_format($totalCostImpact, 2) }}</div>
            <div class="summary-label">Cost Impact</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">Rs. {{ number_format($avgCostImpact, 2) }}</div>
            <div class="summary-label">Avg Cost/SRN</div>
        </div>
    </div>
</div>

@if($viewType === 'summary')
    <!-- Summary View - Release Type Analysis -->
    <div class="avoid-break">
        <h3 class="mb-2" style="font-size: 14px; font-weight: bold; color: #374151;">Release Type Summary</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%">Release Type</th>
                    <th style="width: 10%">SRNs</th>
                    <th style="width: 15%">Total Quantity</th>
                    <th style="width: 15%">Released Quantity</th>
                    <th style="width: 15%">Cost Impact</th>
                    <th style="width: 15%">Avg Cost/SRN</th>
                    <th style="width: 10%">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $releaseTypeStats = collect($srns)->groupBy('release_type')->map(function($items, $type) use ($totalCostImpact) {
                        $costImpact = $items->sum('cost_impact');
                        $percentage = $totalCostImpact > 0 ? ($costImpact / $totalCostImpact) * 100 : 0;

                        return [
                            'type' => ucfirst($type ?: 'unknown'),
                            'count' => $items->count(),
                            'total_quantity' => $items->sum('total_quantity'),
                            'released_quantity' => $items->sum('released_quantity'),
                            'cost_impact' => $costImpact,
                            'avg_cost' => $items->avg('cost_impact'),
                            'percentage' => $percentage
                        ];
                    })->sortByDesc('cost_impact');
                @endphp
                @foreach($releaseTypeStats as $stat)
                <tr>
                    <td class="font-semibold">{{ $stat['type'] }}</td>
                    <td class="text-center">{{ number_format($stat['count']) }}</td>
                    <td class="text-center">{{ number_format($stat['total_quantity'], 2) }}</td>
                    <td class="text-center">{{ number_format($stat['released_quantity'], 2) }}</td>
                    <td class="text-right text-danger font-semibold">Rs. {{ number_format($stat['cost_impact'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($stat['avg_cost'], 2) }}</td>
                    <td class="text-center">{{ number_format($stat['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Branch-wise Analysis -->
    <div class="avoid-break mt-4">
        <h3 class="mb-2" style="font-size: 14px; font-weight: bold; color: #374151;">Branch-wise Loss Analysis</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%">Branch</th>
                    <th style="width: 10%">SRNs</th>
                    <th style="width: 15%">Released Quantity</th>
                    <th style="width: 20%">Cost Impact</th>
                    <th style="width: 15%">Avg Cost/SRN</th>
                    <th style="width: 15%">% of Total Loss</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $branchStats = collect($srns)->groupBy('branch_name')->map(function($items, $branch) use ($totalCostImpact) {
                        $costImpact = $items->sum('cost_impact');
                        $percentage = $totalCostImpact > 0 ? ($costImpact / $totalCostImpact) * 100 : 0;

                        return [
                            'branch' => $branch ?: 'Unknown',
                            'count' => $items->count(),
                            'released_quantity' => $items->sum('released_quantity'),
                            'cost_impact' => $costImpact,
                            'avg_cost' => $items->avg('cost_impact'),
                            'percentage' => $percentage
                        ];
                    })->sortByDesc('cost_impact');
                @endphp
                @foreach($branchStats as $stat)
                <tr>
                    <td class="font-semibold">{{ $stat['branch'] }}</td>
                    <td class="text-center">{{ number_format($stat['count']) }}</td>
                    <td class="text-center">{{ number_format($stat['released_quantity'], 2) }}</td>
                    <td class="text-right text-danger font-semibold">Rs. {{ number_format($stat['cost_impact'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($stat['avg_cost'], 2) }}</td>
                    <td class="text-center">{{ number_format($stat['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($viewType === 'master_only')
    <!-- Master Only View - Basic SRN Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%">#</th>
                    <th style="width: 15%">SRN Number</th>
                    <th style="width: 12%">Date</th>
                    <th style="width: 15%">Branch</th>
                    <th style="width: 12%">Release Type</th>
                    <th style="width: 12%">Released Qty</th>
                    <th style="width: 15%">Cost Impact</th>
                    <th style="width: 13%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($srns as $index => $srn)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $srn['srn_number'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ isset($srn['release_date']) ? \Carbon\Carbon::parse($srn['release_date'])->format('M j, Y') : 'N/A' }}</td>
                    <td>{{ $srn['branch_name'] ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($srn['release_type'] ?? '') === 'usage') status-confirmed
                            @elseif(($srn['release_type'] ?? '') === 'wastage') status-pending
                            @else status-out-of-stock
                            @endif">
                            {{ ucfirst($srn['release_type'] ?? 'unknown') }}
                        </span>
                    </td>
                    <td class="text-center">{{ number_format($srn['released_quantity'] ?? 0, 2) }}</td>
                    <td class="text-right text-danger font-semibold">Rs. {{ number_format($srn['cost_impact'] ?? 0, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($srn['status'] ?? '') === 'approved') status-active
                            @elseif(($srn['status'] ?? '') === 'processed') status-confirmed
                            @else status-pending
                            @endif">
                            {{ ucfirst($srn['status'] ?? 'pending') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <!-- Detailed View - Complete SRN Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%">#</th>
                    <th style="width: 12%">SRN Number</th>
                    <th style="width: 8%">Date</th>
                    <th style="width: 12%">Branch</th>
                    <th style="width: 10%">Release Type</th>
                    <th style="width: 8%">Total Qty</th>
                    <th style="width: 8%">Released</th>
                    <th style="width: 12%">Cost Impact</th>
                    <th style="width: 15%">Reason</th>
                    <th style="width: 6%">Items</th>
                    <th style="width: 5%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($srns as $index => $srn)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $srn['srn_number'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ isset($srn['release_date']) ? \Carbon\Carbon::parse($srn['release_date'])->format('M j, Y') : 'N/A' }}</td>
                    <td class="text-sm">{{ $srn['branch_name'] ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($srn['release_type'] ?? '') === 'usage') status-confirmed
                            @elseif(($srn['release_type'] ?? '') === 'wastage') status-pending
                            @else status-out-of-stock
                            @endif">
                            {{ ucfirst($srn['release_type'] ?? 'unknown') }}
                        </span>
                    </td>
                    <td class="text-center">{{ number_format($srn['total_quantity'] ?? 0, 2) }}</td>
                    <td class="text-center font-semibold">{{ number_format($srn['released_quantity'] ?? 0, 2) }}</td>
                    <td class="text-right text-danger font-semibold">Rs. {{ number_format($srn['cost_impact'] ?? 0, 2) }}</td>
                    <td class="text-sm">{{ Str::limit($srn['reason'] ?? 'N/A', 25) }}</td>
                    <td class="text-center">{{ $srn['items_count'] ?? 0 }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($srn['status'] ?? '') === 'approved') status-active
                            @elseif(($srn['status'] ?? '') === 'processed') status-confirmed
                            @else status-pending
                            @endif">
                            {{ ucfirst($srn['status'] ?? 'pending') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($viewType === 'detailed')
    <!-- Additional Analysis for Detailed View -->
    <div class="page-break">
        <h3 class="mb-4" style="font-size: 14px; font-weight: bold; color: #374151;">Cost Impact Analysis</h3>

        <!-- High Cost Impact SRNs -->
        @php
            $highCostSRNs = collect($srns)->sortByDesc('cost_impact')->take(15);
        @endphp

        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #dc2626;">Top 15 SRNs by Cost Impact</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SRN Number</th>
                        <th>Branch</th>
                        <th>Release Type</th>
                        <th>Date</th>
                        <th>Released Qty</th>
                        <th>Cost Impact</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($highCostSRNs as $srn)
                    <tr>
                        <td class="font-semibold">{{ $srn['srn_number'] ?? 'N/A' }}</td>
                        <td>{{ $srn['branch_name'] ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="status-badge
                                @if(($srn['release_type'] ?? '') === 'usage') status-confirmed
                                @elseif(($srn['release_type'] ?? '') === 'wastage') status-pending
                                @else status-out-of-stock
                                @endif">
                                {{ ucfirst($srn['release_type'] ?? 'unknown') }}
                            </span>
                        </td>
                        <td class="text-sm">{{ isset($srn['release_date']) ? \Carbon\Carbon::parse($srn['release_date'])->format('M j, Y') : 'N/A' }}</td>
                        <td class="text-center">{{ number_format($srn['released_quantity'] ?? 0, 2) }}</td>
                        <td class="text-right text-danger font-semibold">Rs. {{ number_format($srn['cost_impact'] ?? 0, 2) }}</td>
                        <td class="text-sm">{{ Str::limit($srn['reason'] ?? 'N/A', 30) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Monthly Trend -->
        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #4b5563;">Monthly Cost Impact Trend</h4>
            @php
                $monthlyTrend = collect($srns)->groupBy(function($srn) {
                    return isset($srn['release_date']) ? \Carbon\Carbon::parse($srn['release_date'])->format('Y-m') : 'Unknown';
                })->map(function($items, $month) {
                    return [
                        'month' => $month !== 'Unknown' ? \Carbon\Carbon::parse($month . '-01')->format('M Y') : 'Unknown',
                        'count' => $items->count(),
                        'cost_impact' => $items->sum('cost_impact'),
                        'avg_cost' => $items->avg('cost_impact')
                    ];
                })->sortBy('month');
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>SRNs Count</th>
                        <th>Total Cost Impact</th>
                        <th>Average Cost/SRN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyTrend as $trend)
                    <tr>
                        <td class="font-semibold">{{ $trend['month'] }}</td>
                        <td class="text-center">{{ number_format($trend['count']) }}</td>
                        <td class="text-right text-danger font-semibold">Rs. {{ number_format($trend['cost_impact'], 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($trend['avg_cost'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
            <div class="summary-label">Total SRNs</div>
        </div>
        {{-- <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($pendingSrns) }}</div>
            <div class="summary-label">Pending</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-primary">{{ number_format($approvedSrns) }}</div>
            <div class="summary-label">Approved</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($releasedSrns) }}</div>
            <div class="summary-label">Released</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalItems) }}</div>
            <div class="summary-label">Total Items</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalReleasedQty, 2) }}</div>
            <div class="summary-label">Released Qty</div>
        </div> --}}
    </div>
</div>

<!-- SRN Data Table -->
<div class="avoid-break">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th style="width: 12%">SRN Number</th>
                <th style="width: 10%">Date</th>
                <th style="width: 15%">Branch</th>
                <th style="width: 12%">Department</th>
                <th style="width: 8%">Items</th>
                <th style="width: 10%">Total Qty</th>
                <th style="width: 10%">Status</th>
                <th style="width: 17%">Requested By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($srns as $index => $srn)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-semibold">{{ $srn->srn_number }}</td>
                <td class="text-sm">{{ \Carbon\Carbon::parse($srn->created_at)->format('M j, Y') }}</td>
                <td class="text-sm">{{ $srn->branch->name ?? 'N/A' }}</td>
                <td class="text-sm">{{ $srn->department ?? 'N/A' }}</td>
                <td class="text-center">{{ $srn->items ? $srn->items->count() : 0 }}</td>
                <td class="text-center font-semibold">
                    {{ $srn->items ? number_format($srn->items->sum('quantity'), 2) : '0.00' }}
                </td>
                <td class="text-center">
                    @php
                        $statusClass = match($srn->status) {
                            'pending' => 'status-pending',
                            'approved' => 'status-confirmed',
                            'released' => 'status-active',
                            'rejected' => 'status-out-of-stock',
                            default => 'status-badge'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($srn->status) }}
                    </span>
                </td>
                <td class="text-sm">{{ $srn->requestedBy->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Additional Analysis (if detailed view) -->
@if($viewType === 'detailed' && $srns->count() > 0)
<div class="page-break"></div>

<div class="mt-4">
    <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #2563eb;">SRN Analysis</h3>

    <!-- Branch Activity Analysis -->
    @php
        $branchActivity = $srns->groupBy(function($srn) {
            return $srn->branch->name ?? 'Unknown';
        })->map(function($branchSrns, $branchName) {
            return [
                'branch' => $branchName,
                'total_srns' => $branchSrns->count(),
                'pending' => $branchSrns->where('status', 'pending')->count(),
                'approved' => $branchSrns->where('status', 'approved')->count(),
                'released' => $branchSrns->where('status', 'released')->count(),
                'total_items' => $branchSrns->sum(function($srn) {
                    return $srn->items ? $srn->items->count() : 0;
                }),
                'total_qty' => $branchSrns->sum(function($srn) {
                    return $srn->items ? $srn->items->sum('quantity') : 0;
                }),
            ];
        });
    @endphp

    @if($branchActivity->count() > 0)
    <div class="avoid-break">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Release Activity by Branch</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Branch</th>
                    <th class="text-center">Total SRNs</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Approved</th>
                    <th class="text-center">Released</th>
                    <th class="text-center">Total Items</th>
                    <th class="text-center">Total Qty</th>
                    <th class="text-center">Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchActivity as $activity)
                <tr>
                    <td class="font-semibold">{{ $activity['branch'] }}</td>
                    <td class="text-center">{{ $activity['total_srns'] }}</td>
                    <td class="text-center text-warning">{{ $activity['pending'] }}</td>
                    <td class="text-center text-primary">{{ $activity['approved'] }}</td>
                    <td class="text-center text-success">{{ $activity['released'] }}</td>
                    <td class="text-center">{{ $activity['total_items'] }}</td>
                    <td class="text-center">{{ number_format($activity['total_qty'], 2) }}</td>
                    <td class="text-center">
                        @php
                            $completionRate = $activity['total_srns'] > 0 ?
                                ($activity['released'] / $activity['total_srns']) * 100 : 0;
                        @endphp
                        <span class="font-semibold">{{ number_format($completionRate, 1) }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Department-wise Analysis -->
    @php
        $departmentActivity = $srns->groupBy('department')->map(function($deptSrns, $department) {
            return [
                'department' => $department ?: 'Not Specified',
                'total_srns' => $deptSrns->count(),
                'pending' => $deptSrns->where('status', 'pending')->count(),
                'approved' => $deptSrns->where('status', 'approved')->count(),
                'released' => $deptSrns->where('status', 'released')->count(),
                'total_qty' => $deptSrns->sum(function($srn) {
                    return $srn->items ? $srn->items->sum('quantity') : 0;
                }),
            ];
        })->sortByDesc('total_srns');
    @endphp

    @if($departmentActivity->count() > 0)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Department Usage Analysis</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th class="text-center">Total SRNs</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Approved</th>
                    <th class="text-center">Released</th>
                    <th class="text-center">Total Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentActivity as $dept)
                <tr>
                    <td class="font-semibold">{{ $dept['department'] }}</td>
                    <td class="text-center">{{ $dept['total_srns'] }}</td>
                    <td class="text-center text-warning">{{ $dept['pending'] }}</td>
                    <td class="text-center text-primary">{{ $dept['approved'] }}</td>
                    <td class="text-center text-success">{{ $dept['released'] }}</td>
                    <td class="text-center">{{ number_format($dept['total_qty'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent SRN Details -->
    @php
        $recentSrns = $srns->sortByDesc('created_at')->take(8);
    @endphp

    @if($recentSrns->count() > 0)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Recent SRN Details</h4>

        @foreach($recentSrns as $srn)
        <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 10px; background: #fafafa;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-weight: 600; color: #2563eb;">{{ $srn->srn_number }}</span>
                    <span style="margin-left: 10px; font-size: 10px; color: #6b7280;">
                        {{ \Carbon\Carbon::parse($srn->created_at)->format('M j, Y g:i A') }}
                    </span>
                </div>
                <div>
                    @php
                        $statusClass = match($srn->status) {
                            'pending' => 'status-pending',
                            'approved' => 'status-confirmed',
                            'released' => 'status-active',
                            'rejected' => 'status-out-of-stock',
                            default => 'status-badge'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($srn->status) }}
                    </span>
                </div>
            </div>

            <div style="font-size: 10px; color: #6b7280; margin-bottom: 6px;">
                <strong>Branch:</strong> {{ $srn->branch->name ?? 'N/A' }}
                @if($srn->department)
                    | <strong>Department:</strong> {{ $srn->department }}
                @endif
                | <strong>Requested by:</strong> {{ $srn->requestedBy->name ?? 'N/A' }}
            </div>

            @if($srn->items && $srn->items->count() > 0)
            <div style="font-size: 10px;">
                <strong>Items ({{ $srn->items->count() }}):</strong>
                @foreach($srn->items->take(3) as $item)
                    <span style="background: #e2e8f0; padding: 2px 6px; border-radius: 3px; margin-right: 4px; display: inline-block; margin-bottom: 2px;">
                        {{ $item->item->name ?? 'N/A' }} ({{ number_format($item->quantity, 2) }})
                    </span>
                @endforeach
                @if($srn->items->count() > 3)
                    <span style="color: #6b7280;">... and {{ $srn->items->count() - 3 }} more</span>
                @endif
            </div>
            @endif

            @if($srn->purpose)
            <div style="font-size: 10px; color: #6b7280; margin-top: 6px; font-style: italic;">
                <strong>Purpose:</strong> {{ Str::limit($srn->purpose, 100) }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

@if($srns->count() == 0)
<div style="text-align: center; padding: 40px; color: #6b7280;">
    <div style="font-size: 14px; margin-bottom: 10px;">No SRN data found</div>
    <div style="font-size: 12px;">Please adjust your filters and try again.</div>
</div>
@endif
@endsection
