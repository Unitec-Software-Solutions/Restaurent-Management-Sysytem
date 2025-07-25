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
