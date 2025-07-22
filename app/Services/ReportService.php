<?php

namespace App\Services;

use App\Models\ItemTransaction;
use App\Models\GrnMaster;
use App\Models\GrnItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Calculate average daily loss safely with proper error handling
     */
    public function calculateAverageDailyLoss($branchId = null, $startDate = null, $endDate = null, $organizationId = null)
    {
        try {
            // Set default date range if not provided
            $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(30);
            $endDate = $endDate ? Carbon::parse($endDate) : Carbon::now();
            
            // Ensure start date is before end date
            if ($startDate->gt($endDate)) {
                throw new \InvalidArgumentException('Start date must be before end date');
            }

            // Calculate number of days
            $totalDays = $startDate->diffInDays($endDate) + 1;
            
            if ($totalDays <= 0) {
                return [
                    'average_daily_loss' => 0,
                    'total_loss' => 0,
                    'total_days' => 0,
                    'error' => null
                ];
            }

            // Query for damaged/rejected quantities from various sources
            $totalLoss = $this->calculateTotalLoss($branchId, $startDate, $endDate, $organizationId);
            
            // Safely calculate average (prevent division by zero)
            $averageDailyLoss = $totalDays > 0 ? round($totalLoss / $totalDays, 2) : 0;
            
            return [
                'average_daily_loss' => $averageDailyLoss,
                'total_loss' => $totalLoss,
                'total_days' => $totalDays,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating average daily loss', [
                'error' => $e->getMessage(),
                'branch_id' => $branchId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'organization_id' => $organizationId
            ]);

            return [
                'average_daily_loss' => 0,
                'total_loss' => 0,
                'total_days' => 0,
                'error' => 'Unable to calculate average daily loss: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate total loss from multiple sources
     */
    private function calculateTotalLoss($branchId, $startDate, $endDate, $organizationId)
    {
        $totalLoss = 0;

        try {
            // Loss from ItemTransactions (damaged_quantity and waste_quantity)
            $transactionLoss = $this->getTransactionLoss($branchId, $startDate, $endDate, $organizationId);
            $totalLoss += $transactionLoss;

            // Loss from GRN rejections
            $grnLoss = $this->getGrnRejectionLoss($branchId, $startDate, $endDate, $organizationId);
            $totalLoss += $grnLoss;

        } catch (\Exception $e) {
            Log::warning('Error calculating component of total loss', [
                'error' => $e->getMessage(),
                'branch_id' => $branchId
            ]);
        }

        return max(0, $totalLoss); // Ensure non-negative result
    }

    /**
     * Get loss from item transactions
     */
    private function getTransactionLoss($branchId, $startDate, $endDate, $organizationId)
    {
        try {
            $query = ItemTransaction::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('is_active', true);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }

            // Sum damaged and waste quantities, handling nulls
            $result = $query->selectRaw('
                COALESCE(SUM(COALESCE(damaged_quantity, 0)), 0) + 
                COALESCE(SUM(COALESCE(waste_quantity, 0)), 0) as total_loss
            ')->first();

            return $result ? (float) $result->total_loss : 0;

        } catch (\Exception $e) {
            Log::warning('Error getting transaction loss', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get loss from GRN rejections
     */
    private function getGrnRejectionLoss($branchId, $startDate, $endDate, $organizationId)
    {
        try {
            $query = GrnItem::query()
                ->join('grn_master', 'grn_items.grn_id', '=', 'grn_master.grn_id')
                ->whereBetween('grn_master.received_date', [$startDate, $endDate])
                ->where('grn_master.status', GrnMaster::STATUS_VERIFIED);

            if ($branchId) {
                $query->where('grn_master.branch_id', $branchId);
            }

            if ($organizationId) {
                $query->where('grn_master.organization_id', $organizationId);
            }

            // Sum rejected quantities, handling nulls
            $result = $query->selectRaw('
                COALESCE(SUM(COALESCE(grn_items.rejected_quantity, 0)), 0) as total_rejected
            ')->first();

            return $result ? (float) $result->total_rejected : 0;

        } catch (\Exception $e) {
            Log::warning('Error getting GRN rejection loss', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Generate SRN (Stock Received Note) report with safe calculations
     */
    public function generateSrnReport($branchId = null, $startDate = null, $endDate = null, $organizationId = null)
    {
        try {
            // Get average daily loss calculation
            $lossData = $this->calculateAverageDailyLoss($branchId, $startDate, $endDate, $organizationId);
            
            // Get other SRN statistics
            $srnStats = $this->getSrnStatistics($branchId, $startDate, $endDate, $organizationId);
            
            return [
                'loss_data' => $lossData,
                'statistics' => $srnStats,
                'generated_at' => Carbon::now(),
                'success' => true,
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error('Error generating SRN report', [
                'error' => $e->getMessage(),
                'branch_id' => $branchId,
                'organization_id' => $organizationId
            ]);

            return [
                'loss_data' => [
                    'average_daily_loss' => 0,
                    'total_loss' => 0,
                    'total_days' => 0,
                    'error' => 'Calculation failed'
                ],
                'statistics' => [],
                'generated_at' => Carbon::now(),
                'success' => false,
                'error' => 'Unable to generate SRN report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get additional SRN statistics
     */
    private function getSrnStatistics($branchId, $startDate, $endDate, $organizationId)
    {
        try {
            $stats = [];

            // GRN statistics with safe calculations
            $grnQuery = GrnMaster::query()
                ->whereBetween('received_date', [$startDate, $endDate]);

            if ($branchId) {
                $grnQuery->where('branch_id', $branchId);
            }

            if ($organizationId) {
                $grnQuery->where('organization_id', $organizationId);
            }

            $stats['total_grns'] = $grnQuery->count();
            $stats['verified_grns'] = $grnQuery->clone()->where('status', GrnMaster::STATUS_VERIFIED)->count();
            $stats['pending_grns'] = $grnQuery->clone()->where('status', GrnMaster::STATUS_PENDING)->count();
            
            // Safe average calculation
            $totalAmount = $grnQuery->clone()->sum('total_amount') ?? 0;
            $stats['average_grn_value'] = $stats['total_grns'] > 0 ? 
                round($totalAmount / $stats['total_grns'], 2) : 0;

            return $stats;

        } catch (\Exception $e) {
            Log::warning('Error getting SRN statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }
}