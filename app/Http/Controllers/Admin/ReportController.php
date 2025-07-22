<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    protected function getOrganizationId()
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        // For super admin, return null to allow access to all organizations
        if ($user->is_super_admin) {
            return null;
        }

        if (!$user->organization_id) {
            abort(403, 'No organization assigned');
        }

        return $user->organization_id;
    }

    public function index()
    {
        try {
            $user = Auth::guard('admin')->user();
            $organizationId = $this->getOrganizationId();
            $branchId = $user->branch_id ?? null;

            // Generate sample SRN report data to display on the reports page
            $srnReport = $this->reportService->generateSrnReport($branchId, null, null, $organizationId);
            
            return view('admin.reports.index', compact('srnReport'));
        } catch (\Exception $e) {
            Log::error('Error loading reports index', ['error' => $e->getMessage()]);
            
            // Return view with error state
            $srnReport = [
                'success' => false,
                'error' => 'Unable to load reports data',
                'loss_data' => ['average_daily_loss' => 0, 'total_loss' => 0, 'total_days' => 0],
                'statistics' => []
            ];
            
            return view('admin.reports.index', compact('srnReport'));
        }
    }

    /**
     * Generate SRN Report
     */
    public function generateSrnReport(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();
            $organizationId = $this->getOrganizationId();
            $branchId = $user->branch_id ?? $request->input('branch_id');

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $report = $this->reportService->generateSrnReport($branchId, $startDate, $endDate, $organizationId);

            if ($request->expectsJson()) {
                return response()->json($report);
            }

            return view('admin.reports.srn', compact('report'));
            
        } catch (\Exception $e) {
            Log::error('Error generating SRN report', [
                'error' => $e->getMessage(),
                'user_id' => Auth::guard('admin')->id()
            ]);

            $errorReport = [
                'success' => false,
                'error' => 'Unable to generate SRN report: ' . $e->getMessage(),
                'loss_data' => ['average_daily_loss' => 0, 'total_loss' => 0, 'total_days' => 0],
                'statistics' => []
            ];

            if ($request->expectsJson()) {
                return response()->json($errorReport, 500);
            }

            return view('admin.reports.srn', ['report' => $errorReport]);
        }
    }

    /**
     * Get Average Daily Loss data via API
     */
    public function getAverageDailyLoss(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();
            $organizationId = $this->getOrganizationId();
            $branchId = $user->branch_id ?? $request->input('branch_id');

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $lossData = $this->reportService->calculateAverageDailyLoss($branchId, $startDate, $endDate, $organizationId);

            return response()->json([
                'success' => true,
                'data' => $lossData
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting average daily loss', [
                'error' => $e->getMessage(),
                'user_id' => Auth::guard('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to calculate average daily loss',
                'data' => [
                    'average_daily_loss' => 0,
                    'total_loss' => 0,
                    'total_days' => 0,
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
