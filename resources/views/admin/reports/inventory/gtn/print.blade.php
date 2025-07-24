<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }

        /* Print Toolbar */
        .print-toolbar {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .toolbar-title {
            font-size: 18px;
            font-weight: bold;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-print {
            background: #3498db;
            color: white;
        }

        .btn-print:hover {
            background: #2980b9;
        }

        .btn-close {
            background: #95a5a6;
            color: white;
        }

        .btn-close:hover {
            background: #7f8c8d;
        }

        /* Report Content */
        .report-content {
            padding: 30px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 20px;
            color: #34495e;
            margin-bottom: 10px;
        }

        .report-date {
            font-size: 14px;
            color: #7f8c8d;
        }

        /* Filters Section */
        .filters-section {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }

        .filters-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .filter-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #bdc3c7;
        }

        .filter-label {
            font-weight: 500;
            color: #34495e;
        }

        .filter-value {
            color: #2c3e50;
        }

        /* Summary Cards */
        .summary-section {
            margin-bottom: 30px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-card.success {
            background: linear-gradient(135deg, #27ae60, #229954);
        }

        .summary-card.warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .summary-card.danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .card-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-label {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        th, td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e8f4f8;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Status badges */
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Print Styles */
        @media print {
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .print-toolbar {
                display: none !important;
            }

            .container {
                box-shadow: none !important;
                max-width: none !important;
                margin: 0 !important;
            }

            .report-content {
                padding: 15px !important;
            }

            @page {
                size: A4;
                margin: 0.5in;
            }

            table {
                page-break-inside: avoid;
                font-size: 10px;
            }

            th, td {
                padding: 4px 3px;
            }

            .summary-cards {
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
            }

            .summary-card {
                padding: 10px;
                margin-bottom: 10px;
            }

            .card-value {
                font-size: 18px;
            }

            .filters-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media screen and (max-width: 768px) {
            .container {
                margin: 0;
                box-shadow: none;
            }

            .report-content {
                padding: 15px;
            }

            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 10px;
            }

            th, td {
                padding: 4px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Toolbar -->
        <div class="print-toolbar">
            <div class="toolbar-title">
                <i class="fas fa-print"></i> Print Preview - {{ $reportTitle }}
            </div>
            <div class="toolbar-actions">
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn btn-close" onclick="window.close()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>

        <!-- Report Content -->
        <div class="report-content">
            <!-- Report Header -->
            <div class="report-header">
                <div class="company-name">Restaurant Management System</div>
                <div class="report-title">{{ $reportTitle }}</div>
                <div class="report-date">Generated on: {{ $generated_at }}</div>
                @if($dateFrom || $dateTo)
                    <div class="report-date">
                        Period: {{ $dateFrom ? date('d/m/Y', strtotime($dateFrom)) : 'Start' }} to {{ $dateTo ? date('d/m/Y', strtotime($dateTo)) : 'End' }}
                    </div>
                @endif
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <div class="filters-title">Report Filters</div>
                <div class="filters-grid">
                    <div class="filter-item">
                        <span class="filter-label">Status:</span>
                        <span class="filter-value">{{ $filters['status'] }}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">From Branch:</span>
                        <span class="filter-value">{{ $filters['from_branch'] }}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">To Branch:</span>
                        <span class="filter-value">{{ $filters['to_branch'] }}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Date Range:</span>
                        <span class="filter-value">{{ $filters['date_range'] }}</span>
                    </div>
                </div>
            </div>

            @if($viewType === 'summary' || $viewType === 'detailed')
                <!-- Summary Section -->
                <div class="summary-section">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">
                        <i class="fas fa-chart-bar"></i> GTN Summary
                    </h3>
                    <div class="summary-cards">
                        <div class="summary-card">
                            <div class="card-value">{{ $reportData['gtns']->count() }}</div>
                            <div class="card-label">Total GTNs</div>
                        </div>
                        <div class="summary-card success">
                            <div class="card-value">{{ $reportData['gtns']->where('origin_status', 'sent')->count() }}</div>
                            <div class="card-label">Sent</div>
                        </div>
                        <div class="summary-card warning">
                            <div class="card-value">{{ $reportData['gtns']->where('origin_status', 'pending')->count() }}</div>
                            <div class="card-label">Pending</div>
                        </div>
                        <div class="summary-card info">
                            <div class="card-value">{{ $reportData['gtns']->where('receiver_status', 'received')->count() }}</div>
                            <div class="card-label">Received</div>
                        </div>
                    </div>
                </div>
            @endif

            @if($viewType === 'detailed')
                <!-- Detailed GTN Report -->
                <div class="table-container">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">
                        <i class="fas fa-list-alt"></i> Detailed GTN Report
                    </h3>
                    <table>
                        <thead>
                            <tr>
                                <th>GTN No.</th>
                                <th>Date</th>
                                <th>From Branch</th>
                                <th>To Branch</th>
                                <th class="text-center">Origin Status</th>
                                <th class="text-center">Receiver Status</th>
                                <th class="text-right">Total Items</th>
                                <th class="text-right">Total Qty</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData['gtns'] as $gtn)
                                <tr>
                                    <td>{{ $gtn->gtn_number ?? 'N/A' }}</td>
                                    <td>{{ $gtn->transfer_date ? date('d/m/Y', strtotime($gtn->transfer_date)) : 'N/A' }}</td>
                                    <td>{{ $gtn->fromBranch->name ?? 'N/A' }}</td>
                                    <td>{{ $gtn->toBranch->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if($gtn->origin_status === 'sent')
                                            <span class="badge badge-success">Sent</span>
                                        @elseif($gtn->origin_status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-info">{{ ucfirst($gtn->origin_status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($gtn->receiver_status === 'received')
                                            <span class="badge badge-success">Received</span>
                                        @elseif($gtn->receiver_status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-info">{{ ucfirst($gtn->receiver_status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $gtn->items->count() ?? 0 }}</td>
                                    <td class="text-right">{{ $gtn->items->sum('transfer_quantity') ?? 0 }}</td>
                                    <td>{{ $gtn->notes ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center" style="padding: 20px; color: #7f8c8d;">
                                        <i class="fas fa-inbox"></i> No GTN data available for the selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($reportData['gtns']->isNotEmpty())
                            <tfoot>
                                <tr style="background-color: #34495e; color: white; font-weight: bold;">
                                    <td colspan="6" class="text-right">Totals:</td>
                                    <td class="text-right">{{ $reportData['gtns']->sum(function($gtn) { return $gtn->items->count(); }) }}</td>
                                    <td class="text-right">{{ $reportData['gtns']->sum(function($gtn) { return $gtn->items->sum('transfer_quantity'); }) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @elseif($viewType === 'summary')
                <!-- Summary Table -->
                <div class="table-container">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">
                        <i class="fas fa-table"></i> GTN Summary by Branch
                    </h3>
                    <table>
                        <thead>
                            <tr>
                                <th>From Branch</th>
                                <th>To Branch</th>
                                <th class="text-right">Total GTNs</th>
                                <th class="text-right">Sent</th>
                                <th class="text-right">Pending</th>
                                <th class="text-right">Received</th>
                                <th class="text-right">Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $branchStats = $reportData['gtns']->groupBy(function($gtn) {
                                    return ($gtn->fromBranch->name ?? 'Unknown') . ' → ' . ($gtn->toBranch->name ?? 'Unknown');
                                });
                            @endphp
                            @forelse($branchStats as $branches => $gtns)
                                @php
                                    list($fromBranch, $toBranch) = explode(' → ', $branches, 2);
                                @endphp
                                <tr>
                                    <td>{{ $fromBranch }}</td>
                                    <td>{{ $toBranch }}</td>
                                    <td class="text-right">{{ $gtns->count() }}</td>
                                    <td class="text-right">{{ $gtns->where('origin_status', 'sent')->count() }}</td>
                                    <td class="text-right">{{ $gtns->where('origin_status', 'pending')->count() }}</td>
                                    <td class="text-right">{{ $gtns->where('receiver_status', 'received')->count() }}</td>
                                    <td class="text-right">{{ $gtns->sum(function($gtn) { return $gtn->items->sum('transfer_quantity'); }) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 20px; color: #7f8c8d;">
                                        <i class="fas fa-inbox"></i> No GTN data available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif($viewType === 'master_only')
                <!-- Master Branches Only -->
                <div class="table-container">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">
                        <i class="fas fa-list"></i> Master Branches List
                    </h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Branch Name</th>
                                <th>Manager</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData['branches'] as $branch)
                                <tr>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ $branch->manager_name ?? 'N/A' }}</td>
                                    <td>{{ $branch->phone ?? 'N/A' }}</td>
                                    <td>{{ $branch->email ?? 'N/A' }}</td>
                                    <td>{{ $branch->address ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if($branch->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 20px; color: #7f8c8d;">
                                        <i class="fas fa-inbox"></i> No branches available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Footer -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #34495e; text-align: center; color: #7f8c8d; font-size: 12px;">
                <p>This report was automatically generated by the Restaurant Management System</p>
                <p>Generated on {{ $generated_at }}</p>
            </div>
        </div>
    </div>
</body>
</html>
