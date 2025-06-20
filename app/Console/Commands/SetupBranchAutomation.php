<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\KitchenStation;
use App\Services\BranchAutomationService;

class SetupBranchAutomation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:setup-automation 
                            {branch? : The ID of the specific branch to setup}
                            {--all : Setup automation for all branches without admins}
                            {--force : Force setup even if branch already has admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup automated admin and kitchen stations for branches that are missing them';

    protected $branchAutomationService;

    public function __construct(BranchAutomationService $branchAutomationService)
    {
        parent::__construct();
        $this->branchAutomationService = $branchAutomationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branchId = $this->argument('branch');
        $setupAll = $this->option('all');
        $force = $this->option('force');

        if ($branchId) {
            // Setup specific branch
            $branch = Branch::find($branchId);
            if (!$branch) {
                $this->error("Branch with ID {$branchId} not found.");
                return 1;
            }

            return $this->setupBranch($branch, $force);
        } elseif ($setupAll) {
            // Setup all branches without admins
            return $this->setupAllBranches($force);
        } else {
            // Interactive mode
            return $this->interactiveSetup();
        }
    }

    private function setupBranch(Branch $branch, bool $force = false): int
    {
        // Check if branch is head office
        if ($branch->is_head_office) {
            $this->warn("Skipping head office branch: {$branch->name}");
            return 0;
        }

        // Check if branch already has admin
        $existingAdmin = Admin::where('branch_id', $branch->id)->first();
        $existingStations = KitchenStation::where('branch_id', $branch->id)->count();

        if (($existingAdmin || $existingStations > 0) && !$force) {
            $this->warn("Branch '{$branch->name}' already has automation setup. Use --force to override.");
            return 0;
        }

        $this->info("Setting up automation for branch: {$branch->name}");

        try {
            $this->branchAutomationService->setupNewBranch($branch);
            $this->info("✅ Successfully setup automation for '{$branch->name}'");
            
            // Show results
            $admin = Admin::where('branch_id', $branch->id)->first();
            $stations = KitchenStation::where('branch_id', $branch->id)->count();
            
            if ($admin) {
                $this->line("  👤 Admin: {$admin->name} ({$admin->email})");
            }
            $this->line("  🍽️  Kitchen Stations: {$stations} created");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to setup automation for '{$branch->name}': " . $e->getMessage());
            return 1;
        }
    }

    private function setupAllBranches(bool $force = false): int
    {
        $query = Branch::where('is_head_office', false);
        
        if (!$force) {
            // Only branches without admins
            $query->whereDoesntHave('admins');
        }
        
        $branches = $query->get();

        if ($branches->isEmpty()) {
            $this->info("No branches found that need automation setup.");
            return 0;
        }

        $this->info("Found {$branches->count()} branch(es) to setup:");
        
        $errors = 0;
        foreach ($branches as $branch) {
            if ($this->setupBranch($branch, $force) !== 0) {
                $errors++;
            }
        }

        if ($errors > 0) {
            $this->error("⚠️  Completed with {$errors} error(s)");
            return 1;
        }

        $this->info("🎉 All branches setup successfully!");
        return 0;
    }

    private function interactiveSetup(): int
    {
        $branches = Branch::where('is_head_office', false)
                         ->whereDoesntHave('admins')
                         ->get();

        if ($branches->isEmpty()) {
            $this->info("✅ All branches already have automation setup.");
            return 0;
        }

        $this->info("Found {$branches->count()} branch(es) without automation setup:");
        
        foreach ($branches as $index => $branch) {
            $this->line("  " . ($index + 1) . ". {$branch->name} (Type: {$branch->type})");
        }

        if (!$this->confirm('Do you want to setup automation for all these branches?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        return $this->setupAllBranches(false);
    }
}
