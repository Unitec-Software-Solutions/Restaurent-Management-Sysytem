<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class RepairSidebarRoutes extends Command
{
    protected $signature = 'sidebar:repair 
                            {--dry-run : Show what would be fixed without making changes}
                            {--check-only : Only check for issues}';

    protected $description = 'Repair admin sidebar routes and navigation issues';

    private $issues = [];
    private $fixes = [];

    public function handle()
    {
        $this->info('🔧 Admin Sidebar Route Repair Tool');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $checkOnly = $this->option('check-only');

        // Step 1: Analyze current routes
        $this->analyzeRoutes();

        // Step 2: Check sidebar configuration
        $this->analyzeSidebar();

        // Step 3: Validate authentication setup
        $this->validateAuthentication();

        // Step 4: Show results
        $this->showResults();

        // Step 5: Apply fixes if not dry run
        if (!$dryRun && !$checkOnly && count($this->fixes) > 0) {
            if ($this->confirm('Apply these fixes?')) {
                $this->applyFixes();
            }
        }

        return 0;
    }

    private function analyzeRoutes()
    {
        $this->info('📋 Analyzing Admin Routes...');

        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->getName() ?? '', 'admin.');
        });

        $this->line("Found {$adminRoutes->count()} admin routes");

        // Check for essential routes
        $essentialRoutes = [
            'admin.dashboard',
            'admin.inventory.index',
            'admin.inventory.dashboard',
            'admin.orders.index',
            'admin.reservations.index',
            'admin.suppliers.index',
            'admin.customers.index',
            'admin.users.index',
            'admin.menus.index',
            'admin.reports.index'
        ];

        $missing = [];
        foreach ($essentialRoutes as $route) {
            if (!Route::has($route)) {
                $missing[] = $route;
                $this->issues[] = "Missing essential route: {$route}";
            }
        }

        if (count($missing) > 0) {
            $this->warn("❌ Missing routes: " . implode(', ', $missing));
        } else {
            $this->info("✅ All essential routes found");
        }

        // Check for duplicate routes (same URL pattern)
        $urlPatterns = [];
        $duplicates = [];
        
        foreach ($adminRoutes as $route) {
            $pattern = $route->uri();
            if (isset($urlPatterns[$pattern])) {
                $duplicates[] = $pattern;
                $this->issues[] = "Duplicate route pattern: {$pattern}";
            }
            $urlPatterns[$pattern] = $route->getName();
        }

        if (count($duplicates) > 0) {
            $this->warn("❌ Duplicate route patterns found: " . count($duplicates));
        }
    }

    private function analyzeSidebar()
    {
        $this->info('🔍 Analyzing Sidebar Configuration...');

        $sidebarPath = resource_path('views/partials/sidebar/admin-sidebar.blade.php');
        
        if (!File::exists($sidebarPath)) {
            $this->error("❌ Sidebar file not found: {$sidebarPath}");
            $this->issues[] = "Sidebar file missing";
            return;
        }

        $content = File::get($sidebarPath);

        // Check for common issues
        $patterns = [
            '/\[\s*\n\s*\'title\'\s*=>\s*[\'"][^\'"]*[\'"],\s*\n\s*\'title\'\s*=>\s*[\'"][^\'"]*[\'"]/' => 'Duplicate title entries',
            '/\]\s*,\s*\[/' => 'Array structure',
            '/admin\.inventory\.dashb[^o]/' => 'Truncated route names',
        ];

        foreach ($patterns as $pattern => $description) {
            if (preg_match($pattern, $content)) {
                $this->issues[] = "Sidebar issue: {$description}";
                $this->warn("❌ Found issue: {$description}");
            }
        }

        // Suggest using component
        if (!str_contains($content, '<x-admin-sidebar')) {
            $this->fixes[] = [
                'type' => 'replace_sidebar',
                'description' => 'Replace sidebar with safety component',
                'file' => $sidebarPath
            ];
        }
    }

    private function validateAuthentication()
    {
        $this->info('🔐 Validating Authentication Configuration...');

        // Check auth config
        $defaultGuard = config('auth.defaults.guard');
        if ($defaultGuard !== 'admin') {
            $this->issues[] = "Default guard should be 'admin', currently: {$defaultGuard}";
            $this->fixes[] = [
                'type' => 'fix_default_guard',
                'description' => 'Set default guard to admin'
            ];
        }

        // Check admin guard exists
        $adminGuard = config('auth.guards.admin');
        if (!$adminGuard) {
            $this->issues[] = "Admin guard not configured";
        } else {
            if ($adminGuard['driver'] !== 'session') {
                $this->issues[] = "Admin guard should use session driver";
            }
            if ($adminGuard['provider'] !== 'admins') {
                $this->issues[] = "Admin guard should use admins provider";
            }
        }

        // Check session config
        $sessionDriver = config('session.driver');
        if ($sessionDriver !== 'database') {
            $this->issues[] = "Session driver should be 'database' for multi-user support";
        }

        // Check for admin model
        try {
            $adminModel = config('auth.providers.admins.model');
            if (!class_exists($adminModel)) {
                $this->issues[] = "Admin model class not found: {$adminModel}";
            }
        } catch (\Exception $e) {
            $this->issues[] = "Error checking admin model: " . $e->getMessage();
        }
    }

    private function showResults()
    {
        $this->newLine();
        $this->info('📊 Analysis Results');
        
        if (count($this->issues) === 0) {
            $this->info('✅ No issues found! Sidebar configuration looks good.');
            return;
        }

        $this->error("❌ Found " . count($this->issues) . " issues:");
        foreach ($this->issues as $issue) {
            $this->line("  • {$issue}");
        }

        $this->newLine();
        if (count($this->fixes) > 0) {
            $this->info("🔧 Available fixes:");
            foreach ($this->fixes as $fix) {
                $this->line("  • {$fix['description']}");
            }
        } else {
            $this->warn("No automated fixes available. Manual intervention required.");
        }
    }

    private function applyFixes()
    {
        $this->info('🚀 Applying fixes...');

        foreach ($this->fixes as $fix) {
            switch ($fix['type']) {
                case 'replace_sidebar':
                    $this->replaceSidebarWithComponent($fix['file']);
                    break;
                case 'fix_default_guard':
                    $this->fixDefaultGuard();
                    break;
            }
        }

        $this->info('✅ Fixes applied successfully!');
        $this->newLine();
        $this->info('📝 Next steps:');
        $this->line('1. Clear caches: php artisan config:clear');
        $this->line('2. Test sidebar navigation');
        $this->line('3. Check authentication flows');
        $this->line('4. Run automated tests');
    }

    private function replaceSidebarWithComponent($file)
    {
        $this->info("Replacing sidebar file with component...");
        
        $componentUsage = <<<'BLADE'
{{-- Use the new safety-enhanced sidebar component --}}
<x-admin-sidebar />

{{-- Legacy sidebar for reference (remove after testing) --}}
{{-- Original sidebar content moved to admin-sidebar-legacy.blade.php --}}
BLADE;

        // Backup original
        $backupFile = str_replace('.blade.php', '-legacy.blade.php', $file);
        File::copy($file, $backupFile);
        
        // Replace with component usage
        File::put($file, $componentUsage);
        
        $this->line("✅ Sidebar replaced with component");
        $this->line("   Original backed up to: {$backupFile}");
    }

    private function fixDefaultGuard()
    {
        $this->info("Fixing default authentication guard...");
        
        $configPath = config_path('auth.php');
        $content = File::get($configPath);
        
        $content = str_replace(
            "'guard' => 'web',",
            "'guard' => 'admin',",
            $content
        );
        
        File::put($configPath, $content);
        $this->line("✅ Default guard set to 'admin'");
    }
}
