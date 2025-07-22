@extends('layouts.admin')
@section('header-title', 'SRN Report')
@section('content')
<div>
    <div class="p-4 rounded-lg">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">SRN (Stock Received Note) Report</h1>
                <p class="text-gray-600 mt-1">Comprehensive analysis of stock received and daily loss calculations</p>
            </div>
            <a href="{{ route('admin.reports.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to Reports
            </a>
        </div>

        @if(isset($report) && $report['success'])
            <!-- Success Report Display -->
            <div class="space-y-6">
                <!-- Report Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                        Report Summary
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Average Daily Loss -->
                        <div class="text-center p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg border border-red-200">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                            <h3 class="text-sm font-medium text-gray-600">Avg. Daily Loss</h3>
                            <p class="text-2xl font-bold text-red-600">
                                {{ number_format($report['loss_data']['average_daily_loss'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $report['loss_data']['total_days'] }} day period
                            </p>
                        </div>

                        <!-- Total Loss -->
                        <div class="text-center p-4 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-lg border border-orange-200">
                            <i class="fas fa-calculator text-orange-500 text-2xl mb-2"></i>
                            <h3 class="text-sm font-medium text-gray-600">Total Loss</h3>
                            <p class="text-2xl font-bold text-orange-600">
                                {{ number_format($report['loss_data']['total_loss'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                From {{ $report['loss_data']['start_date'] ?? 'N/A' }} to {{ $report['loss_data']['end_date'] ?? 'N/A' }}
                            </p>
                        </div>

                        <!-- Total GRNs -->
                        <div class="text-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                            <i class="fas fa-clipboard-list text-blue-500 text-2xl mb-2"></i>
                            <h3 class="text-sm font-medium text-gray-600">Total GRNs</h3>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ $report['statistics']['total_grns'] ?? 0 }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                In selected period
                            </p>
                        </div>

                        <!-- Average GRN Value -->
                        <div class="text-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                            <i class="fas fa-dollar-sign text-green-500 text-2xl mb-2"></i>
                            <h3 class="text-sm font-medium text-gray-600">Avg. GRN Value</h3>
                            <p class="text-2xl font-bold text-green-600">
                                {{ number_format($report['statistics']['average_grn_value'] ?? 0, 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Per GRN
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- GRN Status Breakdown -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                            GRN Status Breakdown
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                                <span class="text-sm font-medium text-gray-700">Verified GRNs</span>
                                <span class="text-lg font-bold text-green-600">
                                    {{ $report['statistics']['verified_grns'] ?? 0 }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded">
                                <span class="text-sm font-medium text-gray-700">Pending GRNs</span>
                                <span class="text-lg font-bold text-yellow-600">
                                    {{ $report['statistics']['pending_grns'] ?? 0 }}
                                </span>
                            </div>
                            
                            @php
                                $totalGrns = $report['statistics']['total_grns'] ?? 0;
                                $verifiedGrns = $report['statistics']['verified_grns'] ?? 0;
                                $pendingGrns = $report['statistics']['pending_grns'] ?? 0;
                                $verificationRate = $totalGrns > 0 ? round(($verifiedGrns / $totalGrns) * 100, 1) : 0;
                            @endphp
                            
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                                <span class="text-sm font-medium text-gray-700">Verification Rate</span>
                                <span class="text-lg font-bold text-blue-600">
                                    {{ $verificationRate }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Loss Analysis -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-search mr-2 text-red-600"></i>
                            Loss Analysis
                        </h3>
                        
                        @if($report['loss_data']['total_loss'] > 0)
                            <div class="space-y-3">
                                <div class="p-3 bg-red-50 rounded">
                                    <p class="text-sm text-gray-600">Daily Loss Rate</p>
                                    <p class="text-lg font-semibold text-red-600">
                                        {{ number_format($report['loss_data']['average_daily_loss'], 2) }} units/day
                                    </p>
                                </div>
                                
                                @php
                                    $daysAnalyzed = $report['loss_data']['total_days'] ?? 0;
                                    $projectedMonthlyLoss = $daysAnalyzed > 0 ? $report['loss_data']['average_daily_loss'] * 30 : 0;
                                @endphp
                                
                                <div class="p-3 bg-orange-50 rounded">
                                    <p class="text-sm text-gray-600">Projected Monthly Loss</p>
                                    <p class="text-lg font-semibold text-orange-600">
                                        {{ number_format($projectedMonthlyLoss, 2) }} units
                                    </p>
                                </div>
                                
                                <div class="p-3 bg-yellow-50 rounded">
                                    <p class="text-sm text-gray-600">Report Period</p>
                                    <p class="text-lg font-semibold text-yellow-600">
                                        {{ $daysAnalyzed }} days
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                                <p class="text-green-600 font-semibold">No losses recorded</p>
                                <p class="text-sm text-gray-500">in the selected period</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Report Metadata -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-center text-sm text-gray-600">
                        <span>Report generated on: {{ $report['generated_at']->format('M d, Y H:i:s') }}</span>
                        <span>Period: {{ $report['loss_data']['start_date'] ?? 'N/A' }} to {{ $report['loss_data']['end_date'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

        @else
            <!-- Error State -->
            <div class="bg-white rounded-lg shadow p-8">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-6xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Report Generation Failed</h2>
                    <p class="text-gray-600 mb-4">
                        {{ $report['error'] ?? 'An unexpected error occurred while generating the SRN report.' }}
                    </p>
                    
                    <div class="space-x-4">
                        <button onclick="window.location.reload()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-refresh mr-2"></i>Retry
                        </button>
                        <a href="{{ route('admin.reports.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Reports
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection