<?php

namespace App\Console\Commands;

use App\Services\MenuScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActivateScheduledMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:activate-scheduled 
                           {--branch= : Activate menus for specific branch only}
                           {--dry-run : Show what would be activated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate menus that are scheduled to be active at current time';

    protected MenuScheduleService $menuScheduleService;

    /**
     * Create a new command instance.
     */
    public function __construct(MenuScheduleService $menuScheduleService)
    {
        parent::__construct();
        $this->menuScheduleService = $menuScheduleService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting menu activation process...');
        
        $branchId = $this->option('branch');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        try {
            if ($branchId) {
                $this->info("Activating menus for branch ID: {$branchId}");
                $results = [$branchId => $this->menuScheduleService->activateMenuForBranch((int)$branchId)];
            } else {
                $this->info('Activating menus for all branches...');
                $results = $this->menuScheduleService->activateScheduledMenus();
            }
            
            $this->displayResults($results);
            
            if (!$dryRun) {
                Log::info('Menu activation command completed', [
                    'branch_id' => $branchId,
                    'results_count' => count($results)
                ]);
            }
            
            $this->info('Menu activation process completed successfully.');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Error during menu activation: {$e->getMessage()}");
            Log::error('Menu activation command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Display the results in a formatted table
     */
    protected function displayResults(array $results): void
    {
        if (empty($results)) {
            $this->warn('No branches found to process.');
            return;
        }
        
        $tableData = [];
        $totalActivated = 0;
        $totalDeactivated = 0;
        $totalSwitched = 0;
        $totalErrors = 0;
        
        foreach ($results as $branchId => $result) {
            $status = $result['success'] ? 
                ($result['action'] === 'none' ? '✓ No change' : "✓ {$result['action']}") : 
                '✗ Error';
            
            $tableData[] = [
                $branchId,
                $result['previous_menu'] ?? 'None',
                $result['new_menu'] ?? 'None',
                $result['action'],
                $status,
                $result['message']
            ];
            
            // Count actions
            if ($result['success']) {
                switch ($result['action']) {
                    case 'activated':
                        $totalActivated++;
                        break;
                    case 'deactivated':
                        $totalDeactivated++;
                        break;
                    case 'switched':
                        $totalSwitched++;
                        break;
                }
            } else {
                $totalErrors++;
            }
        }
        
        $this->table([
            'Branch ID',
            'Previous Menu',
            'New Menu',
            'Action',
            'Status',
            'Message'
        ], $tableData);
        
        // Summary
        $this->info("\n--- Summary ---");
        $this->line("Activated: {$totalActivated}");
        $this->line("Deactivated: {$totalDeactivated}");
        $this->line("Switched: {$totalSwitched}");
        if ($totalErrors > 0) {
            $this->error("Errors: {$totalErrors}");
        }
    }
}
