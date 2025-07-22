<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AverageDailyLossCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = new ReportService();
    }

    /** @test */
    public function it_handles_zero_days_without_error()
    {
        $result = $this->reportService->calculateAverageDailyLoss(
            branchId: 1,
            startDate: '2024-01-01',
            endDate: '2023-12-31' // Invalid date range
        );

        $this->assertEquals(0, $result['average_daily_loss']);
        $this->assertEquals(0, $result['total_loss']);
        $this->assertEquals(0, $result['total_days']);
        $this->assertNull($result['error']);
    }

    /** @test */
    public function it_handles_same_start_and_end_date()
    {
        $result = $this->reportService->calculateAverageDailyLoss(
            branchId: 1,
            startDate: '2024-01-01',
            endDate: '2024-01-01'
        );

        $this->assertEquals(1, $result['total_days']);
        $this->assertIsNumeric($result['average_daily_loss']);
        $this->assertNull($result['error']);
    }

    /** @test */
    public function it_prevents_division_by_zero()
    {
        // Test with null dates (should use defaults)
        $result = $this->reportService->calculateAverageDailyLoss(
            branchId: 1,
            startDate: null,
            endDate: null
        );

        $this->assertIsNumeric($result['average_daily_loss']);
        $this->assertGreaterThan(0, $result['total_days']);
        $this->assertNull($result['error']);
    }

    /** @test */
    public function it_calculates_average_correctly_with_valid_data()
    {
        $startDate = Carbon::now()->subDays(10);
        $endDate = Carbon::now();
        
        $result = $this->reportService->calculateAverageDailyLoss(
            branchId: 1,
            startDate: $startDate->format('Y-m-d'),
            endDate: $endDate->format('Y-m-d')
        );

        $expectedDays = $startDate->diffInDays($endDate) + 1;
        
        $this->assertEquals($expectedDays, $result['total_days']);
        $this->assertIsNumeric($result['average_daily_loss']);
        $this->assertGreaterThanOrEqual(0, $result['average_daily_loss']);
        $this->assertNull($result['error']);
    }

    /** @test */
    public function it_handles_exceptions_gracefully()
    {
        // Test with invalid branch ID that might cause DB errors
        $result = $this->reportService->calculateAverageDailyLoss(
            branchId: 'invalid_id', // This should be handled gracefully
            startDate: '2024-01-01',
            endDate: '2024-01-31'
        );

        // Should not crash and should return safe defaults
        $this->assertIsArray($result);
        $this->assertArrayHasKey('average_daily_loss', $result);
        $this->assertArrayHasKey('total_loss', $result);
        $this->assertArrayHasKey('total_days', $result);
        $this->assertArrayHasKey('error', $result);
    }

    /** @test */
    public function it_generates_srn_report_without_crashing()
    {
        $report = $this->reportService->generateSrnReport(
            branchId: 1,
            startDate: '2024-01-01',
            endDate: '2024-01-31'
        );

        $this->assertIsArray($report);
        $this->assertArrayHasKey('loss_data', $report);
        $this->assertArrayHasKey('statistics', $report);
        $this->assertArrayHasKey('success', $report);
        $this->assertArrayHasKey('error', $report);

        // Loss data should have required structure
        $this->assertArrayHasKey('average_daily_loss', $report['loss_data']);
        $this->assertArrayHasKey('total_loss', $report['loss_data']);
        $this->assertArrayHasKey('total_days', $report['loss_data']);
    }

    /** @test */
    public function it_handles_null_values_in_database_safely()
    {
        // This tests that our COALESCE SQL functions work correctly
        $result = $this->reportService->calculateAverageDailyLoss(
            branchId: 9999, // Non-existent branch
            startDate: '2024-01-01',
            endDate: '2024-01-31',
            organizationId: 9999 // Non-existent organization
        );

        // Should return zero values without errors
        $this->assertEquals(0, $result['average_daily_loss']);
        $this->assertEquals(0, $result['total_loss']);
        $this->assertNull($result['error']);
    }
}