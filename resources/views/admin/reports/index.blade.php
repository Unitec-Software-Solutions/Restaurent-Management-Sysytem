@extends('layouts.admin')
@section('header-title', 'Reports')
@section('content')
<div>
    <div class="p-4 rounded-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Reports Dashboard</h1>
        
        {{-- SRN Report Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- SRN Report Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
                    SRN Report Overview
                </h2>
                
                @if(isset($srnReport) && $srnReport['success'])
                    <div class="space-y-4">
                        <!-- Average Daily Loss -->
                        <div class="p-4 bg-gradient-to-r from-orange-50 to-red-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-600">Avg. Daily Loss</h3>
                                    <p class="text-2xl font-bold text-red-600">
                                        {{ number_format($srnReport['loss_data']['average_daily_loss'], 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Total: {{ number_format($srnReport['loss_data']['total_loss'], 2) }} 
                                        over {{ $srnReport['loss_data']['total_days'] }} days
                                    </p>
                                </div>
                                <div class="text-red-400">
                                    <i class="fas fa-chart-line text-3xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- SRN Statistics -->
                        @if(isset($srnReport['statistics']) && count($srnReport['statistics']) > 0)
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Total GRNs</p>
                                    <p class="text-lg font-semibold text-blue-600">
                                        {{ $srnReport['statistics']['total_grns'] ?? 0 }}
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-green-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Verified</p>
                                    <p class="text-lg font-semibold text-green-600">
                                        {{ $srnReport['statistics']['verified_grns'] ?? 0 }}
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Pending</p>
                                    <p class="text-lg font-semibold text-yellow-600">
                                        {{ $srnReport['statistics']['pending_grns'] ?? 0 }}
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-purple-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Avg. Value</p>
                                    <p class="text-lg font-semibold text-purple-600">
                                        {{ number_format($srnReport['statistics']['average_grn_value'] ?? 0, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Report Error</h3>
                                <p class="text-sm text-red-600">
                                    {{ $srnReport['error'] ?? 'Unable to generate SRN report data' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Inventory Report Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-boxes mr-2 text-green-600"></i>
                    Inventory Report
                </h2>
                <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
                    <!-- Placeholder for chart -->
                    <p class="text-gray-500">Inventory trends will be displayed here</p>
                </div>
            </div>
        </div>
        
        <!-- Report Generation Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Generate Custom SRN Report</h2>
            <form action="{{ route('admin.reports.srn.generate') }}" method="GET" id="reportForm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                        <select name="report_type" class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="srn">SRN Report</option>
                            <option value="inventory">Inventory Report</option>
                            <option value="loss_analysis">Loss Analysis</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="start_date" class="w-full rounded-md border-gray-300 shadow-sm" 
                               value="{{ now()->subDays(30)->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="end_date" class="w-full rounded-md border-gray-300 shadow-sm"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
                        <button type="submit" class="w-full bg-[#515DEF] text-white px-4 py-2 rounded-lg hover:bg-[#6A71F0] transition">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Current Day Loss -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Today's Loss</h3>
                        <p class="text-lg font-semibold text-gray-900" id="todayLoss">Loading...</p>
                    </div>
                </div>
            </div>

            <!-- This Week Loss -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-calendar-week text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">This Week's Loss</h3>
                        <p class="text-lg font-semibold text-gray-900" id="weekLoss">Loading...</p>
                    </div>
                </div>
            </div>

            <!-- This Month Loss -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">This Month's Loss</h3>
                        <p class="text-lg font-semibold text-gray-900" id="monthLoss">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load quick stats via AJAX
    loadQuickStats();
    
    // Auto-refresh every 5 minutes
    setInterval(loadQuickStats, 300000);
});

function loadQuickStats() {
    // Today's loss
    fetch('/admin/api/average-daily-loss?start_date={{ now()->format("Y-m-d") }}&end_date={{ now()->format("Y-m-d") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('todayLoss').textContent = data.data.total_loss.toFixed(2);
            } else {
                document.getElementById('todayLoss').textContent = 'Error';
            }
        })
        .catch(() => {
            document.getElementById('todayLoss').textContent = 'Error';
        });

    // This week's loss
    fetch('/admin/api/average-daily-loss?start_date={{ now()->startOfWeek()->format("Y-m-d") }}&end_date={{ now()->format("Y-m-d") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('weekLoss').textContent = data.data.total_loss.toFixed(2);
            } else {
                document.getElementById('weekLoss').textContent = 'Error';
            }
        })
        .catch(() => {
            document.getElementById('weekLoss').textContent = 'Error';
        });

    // This month's loss
    fetch('/admin/api/average-daily-loss?start_date={{ now()->startOfMonth()->format("Y-m-d") }}&end_date={{ now()->format("Y-m-d") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('monthLoss').textContent = data.data.total_loss.toFixed(2);
            } else {
                document.getElementById('monthLoss').textContent = 'Error';
            }
        })
        .catch(() => {
            document.getElementById('monthLoss').textContent = 'Error';
        });
}
</script>
@endsection