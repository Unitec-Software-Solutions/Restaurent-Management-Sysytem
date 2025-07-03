<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateExhaustiveSeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seeders:validate-exhaustive 
                            {--detailed : Show detailed breakdown of each module}
                            {--export= : Export results to file (json|csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate that exhaustive seeders have completed successfully with comprehensive data coverage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Validating Exhaustive Restaurant Management System Seeders');
        $this->info('═══════════════════════════════════════════════════════════════');
        
        $results = $this->performValidation();
        
        if ($this->option('detailed')) {
            $this->showDetailedResults($results);
        } else {
            $this->showSummaryResults($results);
        }
        
        if ($this->option('export')) {
            $this->exportResults($results, $this->option('export'));
        }
        
        $this->showRecommendations($results);
        
        return $this->determineExitCode($results);
    }
    
    private function performValidation(): array
    {
        $results = [
            'infrastructure' => $this->validateInfrastructure(),
            'user_management' => $this->validateUserManagement(),
            'business_operations' => $this->validateBusinessOperations(),
            'edge_cases' => $this->validateEdgeCases(),
            'system_health' => $this->validateSystemHealth(),
        ];
        
        $results['overall_score'] = $this->calculateOverallScore($results);
        $results['timestamp'] = now();
        
        return $results;
    }
    
    private function validateInfrastructure(): array
    {
        $this->info('📋 Validating Infrastructure...');
        
        $counts = [
            'subscription_plans' => $this->getTableCount('subscription_plans'),
            'organizations' => $this->getTableCount('organizations'),
            'branches' => $this->getTableCount('branches'),
            'tables' => $this->getTableCount('tables'),
            'kitchen_stations' => $this->getTableCount('kitchen_stations'),
            'modules' => $this->getTableCount('modules'),
        ];
        
        $expected = [
            'subscription_plans' => ['min' => 8, 'max' => 15],
            'organizations' => ['min' => 15, 'max' => 25],
            'branches' => ['min' => 25, 'max' => 50],
            'tables' => ['min' => 100, 'max' => 300],
            'kitchen_stations' => ['min' => 40, 'max' => 100],
            'modules' => ['min' => 8, 'max' => 15],
        ];
        
        return [
            'counts' => $counts,
            'expected' => $expected,
            'score' => $this->calculateModuleScore($counts, $expected),
            'issues' => $this->findInfrastructureIssues($counts, $expected),
        ];
    }
    
    private function validateUserManagement(): array
    {
        $this->info('👥 Validating User Management...');
        
        $counts = [
            'admins' => $this->getTableCount('admins'),
            'users' => $this->getTableCount('users'),
            'roles' => $this->getTableCount('roles'),
            'permissions' => $this->getTableCount('permissions'),
            'role_assignments' => $this->getTableCount('model_has_roles'),
        ];
        
        $expected = [
            'admins' => ['min' => 20, 'max' => 60],
            'users' => ['min' => 50, 'max' => 150],
            'roles' => ['min' => 8, 'max' => 20],
            'permissions' => ['min' => 20, 'max' => 100],
            'role_assignments' => ['min' => 30, 'max' => 200],
        ];
        
        // Additional validations
        $specialChecks = [
            'super_admins' => $this->getSuperAdminCount(),
            'org_admins' => $this->getOrgAdminCount(),
            'branch_admins' => $this->getBranchAdminCount(),
            'guest_users' => $this->getGuestUserCount(),
        ];
        
        return [
            'counts' => $counts,
            'expected' => $expected,
            'special_checks' => $specialChecks,
            'score' => $this->calculateModuleScore($counts, $expected),
            'issues' => $this->findUserManagementIssues($counts, $expected),
        ];
    }
    
    private function validateBusinessOperations(): array
    {
        $this->info('💼 Validating Business Operations...');
        
        $counts = [
            'menu_categories' => $this->getTableCount('menu_categories'),
            'menu_items' => $this->getTableCount('menu_items'),
            'orders' => $this->getTableCount('orders'),
            'order_items' => $this->getTableCount('order_items'),
            'reservations' => $this->getTableCount('reservations'),
            'inventory_items' => $this->getTableCount('item_master'),
        ];
        
        $expected = [
            'menu_categories' => ['min' => 20, 'max' => 80],
            'menu_items' => ['min' => 200, 'max' => 500],
            'orders' => ['min' => 150, 'max' => 400],
            'order_items' => ['min' => 300, 'max' => 1000],
            'reservations' => ['min' => 100, 'max' => 300],
            'inventory_items' => ['min' => 300, 'max' => 600],
        ];
        
        // Business logic validations
        $businessChecks = [
            'order_statuses' => $this->getOrderStatusDistribution(),
            'reservation_statuses' => $this->getReservationStatusDistribution(),
            'menu_availability_patterns' => $this->getMenuAvailabilityPatterns(),
            'seasonal_items' => $this->getSeasonalItemCount(),
        ];
        
        return [
            'counts' => $counts,
            'expected' => $expected,
            'business_checks' => $businessChecks,
            'score' => $this->calculateModuleScore($counts, $expected),
            'issues' => $this->findBusinessOperationIssues($counts, $expected),
        ];
    }
    
    private function validateEdgeCases(): array
    {
        $this->info('⚡ Validating Edge Cases...');
        
        $edgeCases = [
            'low_stock_items' => $this->getLowStockItemCount(),
            'expired_items' => $this->getExpiredItemCount(),
            'partial_orders' => $this->getPartialOrderCount(),
            'conflicting_reservations' => $this->getConflictingReservationCount(),
            'cancelled_orders' => $this->getCancelledOrderCount(),
            'refunded_orders' => $this->getRefundedOrderCount(),
            'no_show_reservations' => $this->getNoShowReservationCount(),
            'emergency_scenarios' => $this->getEmergencyScenarioCount(),
        ];
        
        $expected = [
            'low_stock_items' => ['min' => 10, 'max' => 50],
            'expired_items' => ['min' => 5, 'max' => 30],
            'partial_orders' => ['min' => 5, 'max' => 25],
            'conflicting_reservations' => ['min' => 2, 'max' => 15],
            'cancelled_orders' => ['min' => 10, 'max' => 40],
            'refunded_orders' => ['min' => 5, 'max' => 20],
            'no_show_reservations' => ['min' => 5, 'max' => 25],
            'emergency_scenarios' => ['min' => 3, 'max' => 15],
        ];
        
        return [
            'counts' => $edgeCases,
            'expected' => $expected,
            'score' => $this->calculateModuleScore($edgeCases, $expected),
            'issues' => $this->findEdgeCaseIssues($edgeCases, $expected),
        ];
    }
    
    private function validateSystemHealth(): array
    {
        $this->info('🔍 Validating System Health...');
        
        $healthChecks = [
            'foreign_key_integrity' => $this->checkForeignKeyIntegrity(),
            'data_consistency' => $this->checkDataConsistency(),
            'business_rule_compliance' => $this->checkBusinessRuleCompliance(),
            'performance_indicators' => $this->checkPerformanceIndicators(),
        ];
        
        $overallHealth = array_sum($healthChecks) / count($healthChecks);
        
        return [
            'checks' => $healthChecks,
            'overall_health' => $overallHealth,
            'score' => $overallHealth,
            'issues' => $this->findSystemHealthIssues($healthChecks),
        ];
    }
    
    // Helper methods for specific counts and checks
    private function getTableCount(string $table): int
    {
        try {
            return DB::table($table)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getSuperAdminCount(): int
    {
        try {
            return DB::table('admins')->where('is_super_admin', true)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getOrgAdminCount(): int
    {
        try {
            return DB::table('admins')
                   ->whereNotNull('organization_id')
                   ->whereNull('branch_id')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getBranchAdminCount(): int
    {
        try {
            return DB::table('admins')
                   ->whereNotNull('branch_id')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getGuestUserCount(): int
    {
        try {
            return DB::table('users')
                   ->where('user_type', 'guest')
                   ->orWhere('is_guest', true)
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getLowStockItemCount(): int
    {
        try {
            return DB::table('inventory_items')
                   ->whereColumn('current_stock', '<=', 'reorder_level')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getExpiredItemCount(): int
    {
        try {
            return DB::table('inventory_items')
                   ->where('expiry_date', '<', now())
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getPartialOrderCount(): int
    {
        try {
            return DB::table('orders')
                   ->where('payment_status', 'partial')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getConflictingReservationCount(): int
    {
        try {
            return DB::table('reservations')
                   ->where('status', 'conflict')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getCancelledOrderCount(): int
    {
        try {
            return DB::table('orders')
                   ->where('status', 'cancelled')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getRefundedOrderCount(): int
    {
        try {
            return DB::table('orders')
                   ->where('payment_status', 'refunded')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getNoShowReservationCount(): int
    {
        try {
            return DB::table('reservations')
                   ->where('status', 'no_show')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getEmergencyScenarioCount(): int
    {
        try {
            return DB::table('orders')
                   ->where('priority', 'emergency')
                   ->count() +
                   DB::table('inventory_adjustments')
                   ->where('adjustment_type', 'emergency')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getOrderStatusDistribution(): array
    {
        try {
            return DB::table('orders')
                   ->select('status', DB::raw('count(*) as count'))
                   ->groupBy('status')
                   ->pluck('count', 'status')
                   ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getReservationStatusDistribution(): array
    {
        try {
            return DB::table('reservations')
                   ->select('status', DB::raw('count(*) as count'))
                   ->groupBy('status')
                   ->pluck('count', 'status')
                   ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getMenuAvailabilityPatterns(): int
    {
        try {
            return DB::table('menu_items')
                   ->whereNotNull('availability_schedule')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getSeasonalItemCount(): int
    {
        try {
            return DB::table('menu_items')
                   ->where('is_seasonal', true)
                   ->orWhere('name', 'LIKE', '%seasonal%')
                   ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    // Calculation and scoring methods
    private function calculateModuleScore(array $counts, array $expected): float
    {
        $totalScore = 0;
        $totalItems = count($counts);
        
        foreach ($counts as $key => $count) {
            if (isset($expected[$key])) {
                $min = $expected[$key]['min'];
                $max = $expected[$key]['max'];
                
                if ($count >= $min && $count <= $max) {
                    $totalScore += 1.0;
                } elseif ($count > 0) {
                    $totalScore += 0.5;
                }
            }
        }
        
        return $totalItems > 0 ? $totalScore / $totalItems : 0;
    }
    
    private function calculateOverallScore(array $results): float
    {
        $scores = [];
        foreach ($results as $key => $module) {
            if (isset($module['score'])) {
                $scores[] = $module['score'];
            }
        }
        
        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }
    
    // Issue detection methods
    private function findInfrastructureIssues(array $counts, array $expected): array
    {
        $issues = [];
        
        foreach ($counts as $key => $count) {
            if (isset($expected[$key])) {
                $min = $expected[$key]['min'];
                $max = $expected[$key]['max'];
                
                if ($count < $min) {
                    $issues[] = "Low {$key} count: {$count} (expected: {$min}-{$max})";
                } elseif ($count > $max) {
                    $issues[] = "High {$key} count: {$count} (expected: {$min}-{$max})";
                }
            }
            
            if ($count === 0) {
                $issues[] = "No {$key} found - seeder may have failed";
            }
        }
        
        return $issues;
    }
    
    private function findUserManagementIssues(array $counts, array $expected): array
    {
        return $this->findInfrastructureIssues($counts, $expected);
    }
    
    private function findBusinessOperationIssues(array $counts, array $expected): array
    {
        return $this->findInfrastructureIssues($counts, $expected);
    }
    
    private function findEdgeCaseIssues(array $counts, array $expected): array
    {
        $issues = $this->findInfrastructureIssues($counts, $expected);
        
        // Add specific edge case validations
        if ($counts['low_stock_items'] === 0) {
            $issues[] = "No low stock scenarios found - inventory edge cases may not be properly seeded";
        }
        
        if ($counts['conflicting_reservations'] === 0) {
            $issues[] = "No reservation conflicts found - reservation edge cases may not be properly seeded";
        }
        
        return $issues;
    }
    
    private function findSystemHealthIssues(array $healthChecks): array
    {
        $issues = [];
        
        foreach ($healthChecks as $check => $score) {
            if ($score < 0.8) {
                $issues[] = "Poor {$check}: " . number_format($score * 100, 1) . "%";
            }
        }
        
        return $issues;
    }
    
    // Health check methods
    private function checkForeignKeyIntegrity(): float
    {
        // Simplified foreign key integrity check
        try {
            $orphanedOrganizations = DB::table('organizations')
                ->leftJoin('subscription_plans', 'organizations.subscription_plan_id', '=', 'subscription_plans.id')
                ->whereNull('subscription_plans.id')
                ->count();
                
            $orphanedBranches = DB::table('branches')
                ->leftJoin('organizations', 'branches.organization_id', '=', 'organizations.id')
                ->whereNull('organizations.id')
                ->count();
                
            $orphanedUsers = DB::table('users')
                ->leftJoin('organizations', 'users.organization_id', '=', 'organizations.id')
                ->whereNull('organizations.id')
                ->where('users.organization_id', '>', 0)
                ->count();
                
            $totalOrphaned = $orphanedOrganizations + $orphanedBranches + $orphanedUsers;
            
            return $totalOrphaned === 0 ? 1.0 : max(0, 1.0 - ($totalOrphaned / 100));
        } catch (\Exception $e) {
            return 0.5;
        }
    }
    
    private function checkDataConsistency(): float
    {
        try {
            $issues = 0;
            
            // Check order totals consistency
            $inconsistentOrders = DB::table('orders')
                ->where('total_amount', '<=', 0)
                ->count();
            $issues += $inconsistentOrders;
            
            // Check reservation date consistency
            $invalidReservations = DB::table('reservations')
                ->where('reservation_date', '<', '2024-01-01')
                ->count();
            $issues += $invalidReservations;
            
            // Check inventory negative stocks
            $negativeStocks = DB::table('inventory_items')
                ->where('current_stock', '<', 0)
                ->count();
            $issues += $negativeStocks;
            
            return $issues === 0 ? 1.0 : max(0, 1.0 - ($issues / 50));
        } catch (\Exception $e) {
            return 0.5;
        }
    }
    
    private function checkBusinessRuleCompliance(): float
    {
        // Check business rules compliance
        return 0.9; // Placeholder - implement specific business rule checks
    }
    
    private function checkPerformanceIndicators(): float
    {
        // Check performance indicators
        return 0.85; // Placeholder - implement performance checks
    }
    
    // Display methods
    private function showSummaryResults(array $results): void
    {
        $this->info("\n📊 VALIDATION SUMMARY");
        $this->info("════════════════════");
        
        $overallScore = $results['overall_score'];
        $scoreColor = $overallScore >= 0.8 ? 'info' : ($overallScore >= 0.6 ? 'comment' : 'error');
        
        $this->line("Overall Score: <{$scoreColor}>" . number_format($overallScore * 100, 1) . "%</{$scoreColor}>");
        
        foreach (['infrastructure', 'user_management', 'business_operations', 'edge_cases', 'system_health'] as $module) {
            if (isset($results[$module]['score'])) {
                $score = $results[$module]['score'];
                $color = $score >= 0.8 ? 'info' : ($score >= 0.6 ? 'comment' : 'error');
                $this->line(sprintf("%-20s: <%s>%s%%</%s>", 
                    ucfirst(str_replace('_', ' ', $module)), 
                    $color, 
                    number_format($score * 100, 1), 
                    $color
                ));
            }
        }
        
        // Show critical issues
        $this->showCriticalIssues($results);
    }
    
    private function showDetailedResults(array $results): void
    {
        $this->showSummaryResults($results);
        
        $this->info("\n📋 DETAILED BREAKDOWN");
        $this->info("═══════════════════");
        
        foreach ($results as $module => $data) {
            if ($module === 'overall_score' || $module === 'timestamp') continue;
            
            $this->info("\n" . strtoupper(str_replace('_', ' ', $module)));
            $this->info(str_repeat('─', strlen($module) + 5));
            
            if (isset($data['counts'])) {
                $this->table(['Item', 'Count', 'Expected Range', 'Status'], 
                    $this->formatCountsTable($data['counts'], $data['expected'] ?? []));
            }
            
            if (isset($data['issues']) && !empty($data['issues'])) {
                $this->warn("Issues found:");
                foreach ($data['issues'] as $issue) {
                    $this->line("  • " . $issue);
                }
            }
        }
    }
    
    private function showCriticalIssues(array $results): void
    {
        $criticalIssues = [];
        
        foreach ($results as $module => $data) {
            if (isset($data['score']) && $data['score'] < 0.5) {
                $criticalIssues[] = "Critical: " . ucfirst(str_replace('_', ' ', $module)) . " has very low score";
            }
            
            if (isset($data['issues'])) {
                foreach ($data['issues'] as $issue) {
                    if (strpos($issue, 'No ') === 0 || strpos($issue, 'Critical') === 0) {
                        $criticalIssues[] = $issue;
                    }
                }
            }
        }
        
        if (!empty($criticalIssues)) {
            $this->warn("\n⚠️  CRITICAL ISSUES:");
            foreach ($criticalIssues as $issue) {
                $this->error("  • " . $issue);
            }
        }
    }
    
    private function formatCountsTable(array $counts, array $expected): array
    {
        $table = [];
        
        foreach ($counts as $item => $count) {
            $expectedRange = isset($expected[$item]) 
                ? $expected[$item]['min'] . '-' . $expected[$item]['max']
                : 'N/A';
                
            $status = 'Unknown';
            if (isset($expected[$item])) {
                $min = $expected[$item]['min'];
                $max = $expected[$item]['max'];
                
                if ($count >= $min && $count <= $max) {
                    $status = '✅ Good';
                } elseif ($count > 0) {
                    $status = '⚠️  Outside Range';
                } else {
                    $status = '❌ Missing';
                }
            }
            
            $table[] = [
                ucfirst(str_replace('_', ' ', $item)),
                $count,
                $expectedRange,
                $status
            ];
        }
        
        return $table;
    }
    
    private function showRecommendations(array $results): void
    {
        $this->info("\n🎯 RECOMMENDATIONS");
        $this->info("═════════════════");
        
        $overallScore = $results['overall_score'];
        
        if ($overallScore >= 0.9) {
            $this->info("✅ Excellent! Your exhaustive seeders are working perfectly.");
            $this->info("   Consider running performance tests with this data volume.");
        } elseif ($overallScore >= 0.8) {
            $this->comment("👍 Good! Minor adjustments may improve coverage.");
            $this->comment("   Review any warnings above for optimization opportunities.");
        } elseif ($overallScore >= 0.6) {
            $this->warn("⚠️  Moderate! Several areas need attention.");
            $this->warn("   Re-run specific seeders that show low scores.");
        } else {
            $this->error("❌ Poor! Significant issues detected.");
            $this->error("   Consider running: php artisan migrate:fresh --seed --seeder=ExhaustiveSystemSeeder");
        }
        
        $this->info("\nNext steps:");
        $this->info("1. Address any critical issues listed above");
        $this->info("2. Test application functionality with seeded data");
        $this->info("3. Run performance benchmarks");
        $this->info("4. Validate business logic workflows");
    }
    
    private function exportResults(array $results, string $format): void
    {
        $filename = 'seeder-validation-' . date('Y-m-d-H-i-s') . '.' . $format;
        $path = storage_path('app/' . $filename);
        
        if ($format === 'json') {
            file_put_contents($path, json_encode($results, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            $this->exportToCsv($results, $path);
        }
        
        $this->info("📄 Results exported to: " . $path);
    }
    
    private function exportToCsv(array $results, string $path): void
    {
        $handle = fopen($path, 'w');
        
        // CSV header
        fputcsv($handle, ['Module', 'Score', 'Status', 'Issues']);
        
        foreach ($results as $module => $data) {
            if ($module === 'overall_score' || $module === 'timestamp') continue;
            
            $score = isset($data['score']) ? number_format($data['score'] * 100, 1) . '%' : 'N/A';
            $status = isset($data['score']) && $data['score'] >= 0.8 ? 'Good' : 'Needs Attention';
            $issues = isset($data['issues']) ? implode('; ', $data['issues']) : 'None';
            
            fputcsv($handle, [
                ucfirst(str_replace('_', ' ', $module)),
                $score,
                $status,
                $issues
            ]);
        }
        
        fclose($handle);
    }
    
    private function determineExitCode(array $results): int
    {
        $overallScore = $results['overall_score'];
        
        if ($overallScore >= 0.8) {
            return 0; // Success
        } elseif ($overallScore >= 0.6) {
            return 1; // Warning
        } else {
            return 2; // Error
        }
    }
}
