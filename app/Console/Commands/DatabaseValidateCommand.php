<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseIntegrityService;

class DatabaseValidateCommand extends Command
{
    protected $signature = 'db:validate {--fix : Automatically fix common issues}';
    protected $description = 'Validate database integrity and check for seeder issues';

    protected DatabaseIntegrityService $integrityService;

    public function __construct(DatabaseIntegrityService $integrityService)
    {
        parent::__construct();
        $this->integrityService = $integrityService;
    }

    public function handle(): int
    {
        $this->info('🔍 Starting comprehensive database integrity validation...');
        $this->newLine();

        // Run validation
        $results = $this->integrityService->validateDatabaseIntegrity();

        // Display results
        $this->displayResults($results);

        // Auto-fix if requested
        if ($this->option('fix') && !empty($results['errors'])) {
            $this->info('🔧 Attempting to fix detected issues...');
            
            try {
                $fixes = $this->integrityService->fixIntegrityIssues();
                $this->displayFixes($fixes);
                
                // Re-run validation to confirm fixes
                $this->info('🔄 Re-validating after fixes...');
                $revalidationResults = $this->integrityService->validateDatabaseIntegrity();
                $this->displayResults($revalidationResults, true);
                
            } catch (\Exception $e) {
                $this->error('❌ Failed to apply fixes: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        return $results['status'] === 'passed' ? Command::SUCCESS : Command::FAILURE;
    }

    protected function displayResults(array $results, bool $isRevalidation = false): void
    {
        $prefix = $isRevalidation ? '🔄 Re-validation' : '📊 Validation';
        
        $this->info("{$prefix} Results:");
        $this->line("Status: " . ($results['status'] === 'passed' ? '✅ PASSED' : '❌ FAILED'));
        $this->line("Errors: " . $results['summary']['total_errors']);
        $this->line("Warnings: " . $results['summary']['total_warnings']);
        $this->newLine();

        // Display errors
        if (!empty($results['errors'])) {
            $this->error('🚨 Critical Errors:');
            foreach ($results['errors'] as $error) {
                $this->line("  • {$error}");
            }
            $this->newLine();
        }

        // Display warnings
        if (!empty($results['warnings'])) {
            $this->warn('⚠️ Warnings:');
            foreach ($results['warnings'] as $warning) {
                $this->line("  • {$warning}");
            }
            $this->newLine();
        }

        // Display recommendations
        if (!empty($results['summary']['recommendations'])) {
            $this->info('💡 Recommendations:');
            foreach ($results['summary']['recommendations'] as $recommendation) {
                $this->line("  • {$recommendation}");
            }
            $this->newLine();
        }
    }

    protected function displayFixes(array $fixes): void
    {
        if (!empty($fixes)) {
            $this->info('✅ Applied Fixes:');
            foreach ($fixes as $fix) {
                $this->line("  • {$fix}");
            }
            $this->newLine();
        }
    }
}
