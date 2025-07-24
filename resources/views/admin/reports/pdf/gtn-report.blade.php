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
        @if(isset($filters['from_branch']) && $filters['from_branch'])
            <div class="filter-item">
                <span class="filter-label">From Branch:</span> {{ $filters['from_branch'] }}
            </div>
        @endif
        @if(isset($filters['to_branch']) && $filters['to_branch'])
            <div class="filter-item">
                <span class="filter-label">To Branch:</span> {{ $filters['to_branch'] }}
            </div>
        @endif
    </div>
</div>
@endif

<!-- Summary Statistics -->
@php
    $totalGtns = is_countable($gtns) ? $gtns->count() : count($gtns);
    $pendingGtns = collect($gtns)->where('origin_status', 'draft')->count();
    $confirmedGtns = collect($gtns)->where('origin_status', 'confirmed')->count();
    $deliveredGtns = collect($gtns)->where('origin_status', 'delivered')->count();
    $totalValue = collect($gtns)->sum('total_transfer_value');
    $acceptedValue = collect($gtns)->sum('total_accepted_value');
    $rejectedValue = collect($gtns)->sum('total_rejected_value');
    $avgAcceptanceRate = collect($gtns)->avg('acceptance_rate');
@endphp

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalGtns) }}</div>
            <div class="summary-label">Total GTNs</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($pendingGtns) }}</div>
            <div class="summary-label">Draft</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-primary">{{ number_format($confirmedGtns) }}</div>
            <div class="summary-label">Confirmed</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($deliveredGtns) }}</div>
            <div class="summary-label">Delivered</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">Rs. {{ number_format($totalValue, 2) }}</div>
            <div class="summary-label">Total Value</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">Rs. {{ number_format($acceptedValue, 2) }}</div>
            <div class="summary-label">Accepted</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-danger">Rs. {{ number_format($rejectedValue, 2) }}</div>
            <div class="summary-label">Rejected</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($avgAcceptanceRate, 1) }}%</div>
            <div class="summary-label">Avg Accept Rate</div>
        </div>
    </div>
</div>

@if($viewType === 'summary')
    <!-- Summary View - Branch-wise Transfer Analysis -->
    <div class="avoid-break">
        <h3 class="mb-2" style="font-size: 14px; font-weight: bold; color: #374151;">Branch-wise Transfer Summary</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%">From Branch</th>
                    <th style="width: 20%">To Branch</th>
                    <th style="width: 10%">GTNs</th>
                    <th style="width: 15%">Total Value</th>
                    <th style="width: 15%">Accepted Value</th>
                    <th style="width: 10%">Accept Rate</th>
                    <th style="width: 10%">Avg Value</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $branchStats = collect($gtns)->groupBy(function($gtn) {
                        return ($gtn['from_branch'] ?? 'Unknown') . ' → ' . ($gtn['to_branch'] ?? 'Unknown');
                    })->map(function($items, $transfer) {
                        $totalValue = $items->sum('total_transfer_value');
                        $acceptedValue = $items->sum('total_accepted_value');
                        $acceptanceRate = $totalValue > 0 ? ($acceptedValue / $totalValue) * 100 : 0;
                        $parts = explode(' → ', $transfer);

                        return [
                            'from_branch' => $parts[0] ?? 'Unknown',
                            'to_branch' => $parts[1] ?? 'Unknown',
                            'count' => $items->count(),
                            'total_value' => $totalValue,
                            'accepted_value' => $acceptedValue,
                            'acceptance_rate' => $acceptanceRate,
                            'avg_value' => $items->avg('total_transfer_value')
                        ];
                    })->sortByDesc('total_value');
                @endphp
                @foreach($branchStats as $stat)
                <tr>
                    <td class="font-semibold">{{ $stat['from_branch'] }}</td>
                    <td class="font-semibold">{{ $stat['to_branch'] }}</td>
                    <td class="text-center">{{ number_format($stat['count']) }}</td>
                    <td class="text-right">Rs. {{ number_format($stat['total_value'], 2) }}</td>
                    <td class="text-right text-success">Rs. {{ number_format($stat['accepted_value'], 2) }}</td>
                    <td class="text-center">
                        <span class="@if($stat['acceptance_rate'] >= 90) text-success @elseif($stat['acceptance_rate'] >= 70) text-warning @else text-danger @endif">
                            {{ number_format($stat['acceptance_rate'], 1) }}%
                        </span>
                    </td>
                    <td class="text-right">Rs. {{ number_format($stat['avg_value'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($viewType === 'master_only')
    <!-- Master Only View - Basic GTN Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%">#</th>
                    <th style="width: 15%">GTN Number</th>
                    <th style="width: 12%">Date</th>
                    <th style="width: 15%">From Branch</th>
                    <th style="width: 15%">To Branch</th>
                    <th style="width: 12%">Transfer Value</th>
                    <th style="width: 10%">Origin Status</th>
                    <th style="width: 15%">Receiver Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gtns as $index => $gtn)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $gtn['gtn_number'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ isset($gtn['transfer_date']) ? \Carbon\Carbon::parse($gtn['transfer_date'])->format('M j, Y') : 'N/A' }}</td>
                    <td>{{ $gtn['from_branch'] ?? 'N/A' }}</td>
                    <td>{{ $gtn['to_branch'] ?? 'N/A' }}</td>
                    <td class="text-right font-semibold">Rs. {{ number_format($gtn['total_transfer_value'] ?? 0, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($gtn['origin_status'] ?? '') === 'delivered') status-active
                            @elseif(($gtn['origin_status'] ?? '') === 'confirmed') status-confirmed
                            @else status-pending
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $gtn['origin_status'] ?? 'draft')) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($gtn['receiver_status'] ?? '') === 'accepted') status-active
                            @elseif(($gtn['receiver_status'] ?? '') === 'partially_accepted') status-pending
                            @else status-out-of-stock
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $gtn['receiver_status'] ?? 'pending')) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <!-- Detailed View - Complete GTN Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 12%">GTN Number</th>
                    <th style="width: 8%">Date</th>
                    <th style="width: 12%">From → To</th>
                    <th style="width: 8%">Items</th>
                    <th style="width: 10%">Transfer Value</th>
                    <th style="width: 10%">Accepted</th>
                    <th style="width: 10%">Rejected</th>
                    <th style="width: 8%">Accept Rate</th>
                    <th style="width: 8%">Origin Status</th>
                    <th style="width: 9%">Receiver Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gtns as $index => $gtn)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $gtn['gtn_number'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ isset($gtn['transfer_date']) ? \Carbon\Carbon::parse($gtn['transfer_date'])->format('M j, Y') : 'N/A' }}</td>
                    <td class="text-sm">
                        <span class="font-medium">{{ $gtn['from_branch'] ?? 'N/A' }}</span>
                        <br>↓<br>
                        <span class="font-medium">{{ $gtn['to_branch'] ?? 'N/A' }}</span>
                    </td>
                    <td class="text-center">{{ $gtn['items_count'] ?? 0 }}</td>
                    <td class="text-right font-semibold">Rs. {{ number_format($gtn['total_transfer_value'] ?? 0, 2) }}</td>
                    <td class="text-right text-success">Rs. {{ number_format($gtn['total_accepted_value'] ?? 0, 2) }}</td>
                    <td class="text-right text-danger">Rs. {{ number_format($gtn['total_rejected_value'] ?? 0, 2) }}</td>
                    <td class="text-center">
                        @php
                            $acceptanceRate = $gtn['acceptance_rate'] ?? 0;
                        @endphp
                        <span class="@if($acceptanceRate >= 90) text-success @elseif($acceptanceRate >= 70) text-warning @else text-danger @endif">
                            {{ number_format($acceptanceRate, 1) }}%
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($gtn['origin_status'] ?? '') === 'delivered') status-active
                            @elseif(($gtn['origin_status'] ?? '') === 'confirmed') status-confirmed
                            @else status-pending
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $gtn['origin_status'] ?? 'draft')) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($gtn['receiver_status'] ?? '') === 'accepted') status-active
                            @elseif(($gtn['receiver_status'] ?? '') === 'partially_accepted') status-pending
                            @else status-out-of-stock
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $gtn['receiver_status'] ?? 'pending')) }}
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
        <h3 class="mb-4" style="font-size: 14px; font-weight: bold; color: #374151;">Transfer Performance Analysis</h3>

        <!-- Low Acceptance Rate GTNs -->
        @php
            $lowAcceptanceGTNs = collect($gtns)->where('acceptance_rate', '<', 70)->sortBy('acceptance_rate');
        @endphp

        @if($lowAcceptanceGTNs->count() > 0)
        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #dc2626;">GTNs with Low Acceptance Rate (&lt;70%) - {{ $lowAcceptanceGTNs->count() }}</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>GTN Number</th>
                        <th>From → To</th>
                        <th>Date</th>
                        <th>Transfer Value</th>
                        <th>Accepted</th>
                        <th>Rejected</th>
                        <th>Accept Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowAcceptanceGTNs->take(15) as $gtn)
                    <tr>
                        <td class="font-semibold">{{ $gtn['gtn_number'] ?? 'N/A' }}</td>
                        <td class="text-sm">{{ ($gtn['from_branch'] ?? 'N/A') . ' → ' . ($gtn['to_branch'] ?? 'N/A') }}</td>
                        <td class="text-sm">{{ isset($gtn['transfer_date']) ? \Carbon\Carbon::parse($gtn['transfer_date'])->format('M j, Y') : 'N/A' }}</td>
                        <td class="text-right">Rs. {{ number_format($gtn['total_transfer_value'] ?? 0, 2) }}</td>
                        <td class="text-right text-success">Rs. {{ number_format($gtn['total_accepted_value'] ?? 0, 2) }}</td>
                        <td class="text-right text-danger">Rs. {{ number_format($gtn['total_rejected_value'] ?? 0, 2) }}</td>
                        <td class="text-center text-danger font-semibold">{{ number_format($gtn['acceptance_rate'] ?? 0, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Top Transfer Routes -->
        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #4b5563;">Top Transfer Routes by Volume</h4>
            @php
                $topRoutes = collect($gtns)->groupBy(function($gtn) {
                    return ($gtn['from_branch'] ?? 'Unknown') . ' → ' . ($gtn['to_branch'] ?? 'Unknown');
                })->map(function($items, $route) {
                    return [
                        'route' => $route,
                        'count' => $items->count(),
                        'total_value' => $items->sum('total_transfer_value'),
                        'avg_acceptance' => $items->avg('acceptance_rate')
                    ];
                })->sortByDesc('total_value')->take(10);
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transfer Route</th>
                        <th>GTNs Count</th>
                        <th>Total Transfer Value</th>
                        <th>Avg Acceptance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topRoutes as $route)
                    <tr>
                        <td class="font-semibold">{{ $route['route'] }}</td>
                        <td class="text-center">{{ number_format($route['count']) }}</td>
                        <td class="text-right font-semibold">Rs. {{ number_format($route['total_value'], 2) }}</td>
                        <td class="text-center">
                            <span class="@if($route['avg_acceptance'] >= 90) text-success @elseif($route['avg_acceptance'] >= 70) text-warning @else text-danger @endif">
                                {{ number_format($route['avg_acceptance'], 1) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalGtns) }}</div>
            <div class="summary-label">Total GTNs</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($pendingGtns) }}</div>
            <div class="summary-label">Pending</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-primary">{{ number_format($confirmedGtns) }}</div>
            <div class="summary-label">Confirmed</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($receivedGtns) }}</div>
            <div class="summary-label">Received</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalItems) }}</div>
            <div class="summary-label">Total Items</div>
        </div>
    </div>
</div>

<!-- GTN Data Table -->
<div class="avoid-break">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th style="width: 12%">GTN Number</th>
                <th style="width: 10%">Date</th>
                <th style="width: 15%">From Branch</th>
                <th style="width: 15%">To Branch</th>
                <th style="width: 8%">Items</th>
                <th style="width: 10%">Total Qty</th>
                <th style="width: 10%">Status</th>
                <th style="width: 14%">Created By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gtns as $index => $gtn)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-semibold">{{ $gtn->gtn_number }}</td>
                <td class="text-sm">{{ \Carbon\Carbon::parse($gtn->created_at)->format('M j, Y') }}</td>
                <td class="text-sm">{{ $gtn->fromBranch->name ?? 'N/A' }}</td>
                <td class="text-sm">{{ $gtn->toBranch->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $gtn->items ? $gtn->items->count() : 0 }}</td>
                <td class="text-center font-semibold">
                    {{ $gtn->items ? number_format($gtn->items->sum('quantity'), 2) : '0.00' }}
                </td>
                <td class="text-center">
                    @php
                        $statusClass = match($gtn->status) {
                            'pending' => 'status-pending',
                            'confirmed' => 'status-confirmed',
                            'received' => 'status-active',
                            'rejected' => 'status-out-of-stock',
                            default => 'status-badge'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($gtn->status) }}
                    </span>
                </td>
                <td class="text-sm">{{ $gtn->createdBy->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Additional Analysis (if detailed view) -->
@if($viewType === 'detailed' && $gtns->count() > 0)
<div class="page-break"></div>

<div class="mt-4">
    <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #2563eb;">GTN Analysis</h3>

    <!-- Branch Transfer Analysis -->
    @php
        $branchTransfers = $gtns->groupBy(function($gtn) {
            return $gtn->fromBranch->name ?? 'Unknown';
        })->map(function($branchGtns, $fromBranch) {
            return [
                'from_branch' => $fromBranch,
                'total_gtns' => $branchGtns->count(),
                'pending' => $branchGtns->where('status', 'pending')->count(),
                'confirmed' => $branchGtns->where('status', 'confirmed')->count(),
                'received' => $branchGtns->where('status', 'received')->count(),
                'total_items' => $branchGtns->sum(function($gtn) {
                    return $gtn->items ? $gtn->items->count() : 0;
                }),
            ];
        });
    @endphp

    @if($branchTransfers->count() > 0)
    <div class="avoid-break">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Transfer Activity by Source Branch</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>From Branch</th>
                    <th class="text-center">Total GTNs</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Confirmed</th>
                    <th class="text-center">Received</th>
                    <th class="text-center">Total Items</th>
                    <th class="text-center">Success Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchTransfers as $transfer)
                <tr>
                    <td class="font-semibold">{{ $transfer['from_branch'] }}</td>
                    <td class="text-center">{{ $transfer['total_gtns'] }}</td>
                    <td class="text-center text-warning">{{ $transfer['pending'] }}</td>
                    <td class="text-center text-primary">{{ $transfer['confirmed'] }}</td>
                    <td class="text-center text-success">{{ $transfer['received'] }}</td>
                    <td class="text-center">{{ $transfer['total_items'] }}</td>
                    <td class="text-center">
                        @php
                            $successRate = $transfer['total_gtns'] > 0 ?
                                (($transfer['confirmed'] + $transfer['received']) / $transfer['total_gtns']) * 100 : 0;
                        @endphp
                        <span class="font-semibold">{{ number_format($successRate, 1) }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent GTN Details -->
    @php
        $recentGtns = $gtns->sortByDesc('created_at')->take(10);
    @endphp

    @if($recentGtns->count() > 0)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Recent GTN Details</h4>

        @foreach($recentGtns as $gtn)
        <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 10px; background: #fafafa;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-weight: 600; color: #2563eb;">{{ $gtn->gtn_number }}</span>
                    <span style="margin-left: 10px; font-size: 10px; color: #6b7280;">
                        {{ \Carbon\Carbon::parse($gtn->created_at)->format('M j, Y g:i A') }}
                    </span>
                </div>
                <div>
                    @php
                        $statusClass = match($gtn->status) {
                            'pending' => 'status-pending',
                            'confirmed' => 'status-confirmed',
                            'received' => 'status-active',
                            'rejected' => 'status-out-of-stock',
                            default => 'status-badge'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($gtn->status) }}
                    </span>
                </div>
            </div>

            <div style="font-size: 10px; color: #6b7280; margin-bottom: 6px;">
                <strong>Transfer:</strong> {{ $gtn->fromBranch->name ?? 'N/A' }} → {{ $gtn->toBranch->name ?? 'N/A' }}
            </div>

            @if($gtn->items && $gtn->items->count() > 0)
            <div style="font-size: 10px;">
                <strong>Items ({{ $gtn->items->count() }}):</strong>
                @foreach($gtn->items->take(3) as $item)
                    <span style="background: #e2e8f0; padding: 2px 6px; border-radius: 3px; margin-right: 4px; display: inline-block; margin-bottom: 2px;">
                        {{ $item->item->name ?? 'N/A' }} ({{ number_format($item->quantity, 2) }})
                    </span>
                @endforeach
                @if($gtn->items->count() > 3)
                    <span style="color: #6b7280;">... and {{ $gtn->items->count() - 3 }} more</span>
                @endif
            </div>
            @endif

            @if($gtn->notes)
            <div style="font-size: 10px; color: #6b7280; margin-top: 6px; font-style: italic;">
                <strong>Notes:</strong> {{ Str::limit($gtn->notes, 100) }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

@if($gtns->count() == 0)
<div style="text-align: center; padding: 40px; color: #6b7280;">
    <div style="font-size: 14px; margin-bottom: 10px;">No GTN data found</div>
    <div style="font-size: 12px;">Please adjust your filters and try again.</div>
</div>
@endif
@endsection
