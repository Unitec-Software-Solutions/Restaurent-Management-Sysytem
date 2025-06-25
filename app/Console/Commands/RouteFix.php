<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RouteAuditService;

class RouteFix extends Command
{
    protected $signature = 'route:fix 
                            {--dry-run : Show what would be fixed without making changes}
                            {--route= : Fix specific route only}
                            {--type= : Fix specific issue type only}';

    protected $description = 'Automatically fix common route issues';

    protected $routeAuditService;

    public function __construct(RouteAuditService $routeAuditService)
    {
        parent::__construct();
        $this->routeAuditService = $routeAuditService;
    }

    public function handle()
    {
        $this->info('🔧 Route Auto-Fix Utility');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
            return $this->dryRun();
        }

        // Run audit first to identify issues
        $this->call('route:audit', [
            '--missing-only' => true,
        ]);

        $this->newLine();
        $this->info('🔧 Starting automatic fixes...');

        // Fix missing routes
        $this->info('📝 Creating missing routes...');
        $routeResults = $this->routeAuditService->batchCreateMissingRoutesReal();
        
        if (!empty($routeResults['created'])) {
            $this->info('✅ Created ' . count($routeResults['created']) . ' routes:');
            foreach ($routeResults['created'] as $route) {
                $this->line("  - {$route}");
            }
        }

        if (!empty($routeResults['failed'])) {
            $this->warn('❌ Failed to create ' . count($routeResults['failed']) . ' routes:');
            foreach ($routeResults['failed'] as $failed) {
                $this->line("  - {$failed['route']}: {$failed['error']}");
            }
        }

        // Fix missing controllers
        $this->newLine();
        $this->info('🎛️ Creating missing controllers...');
        $controllerResults = $this->routeAuditService->fixControllerReferences();
        
        if (!empty($controllerResults['fixed'])) {
            $this->info('✅ Created ' . count($controllerResults['fixed']) . ' controllers:');
            foreach ($controllerResults['fixed'] as $controller) {
                $this->line("  - {$controller}");
            }
        }

        if (!empty($controllerResults['failed'])) {
            $this->warn('❌ Failed to create ' . count($controllerResults['failed']) . ' controllers:');
            foreach ($controllerResults['failed'] as $failed) {
                $this->line("  - {$failed['controller']}: {$failed['error']}");
            }
        }

        // Fix missing methods
        $this->newLine();
        $this->info('⚙️ Adding missing methods...');
        $methodResults = $this->routeAuditService->fixMissingMethods();
        
        if (!empty($methodResults['fixed'])) {
            $this->info('✅ Added ' . count($methodResults['fixed']) . ' methods:');
            foreach ($methodResults['fixed'] as $method) {
                $this->line("  - {$method}");
            }
        }

        if (!empty($methodResults['failed'])) {
            $this->warn('❌ Failed to add ' . count($methodResults['failed']) . ' methods:');
            foreach ($methodResults['failed'] as $failed) {
                $this->line("  - {$failed['method']}: {$failed['error']}");
            }
        }

        $this->newLine();
        $this->info('🚀 Auto-fix complete!');
        $this->newLine();
        
        // Run audit again to check improvements
        $this->info('📊 Running post-fix audit...');
        $this->call('route:audit');
        
        return 0;
    }

    protected function dryRun()
    {
        $this->info('📋 Dry Run Results:');
        $this->newLine();

        // Show what would be created using findMissingRoutes
        $missingRoutes = $this->findMissingRoutes();
        
        if (!empty($missingRoutes)) {
            $this->info('📝 Would create ' . count($missingRoutes) . ' missing routes:');
            foreach (array_keys($missingRoutes) as $route) {
                $this->line("  - {$route}");
            }
        }

        $this->newLine();
        $this->info('🏃 Run without --dry-run to apply fixes');
        
        return 0;
    }

    /**
     * Find missing routes by scanning the codebase
     */
    private function findMissingRoutes()
    {
        // Get route usage from RouteAuditService
        $routeUsage = $this->scanRouteUsage();
        $registeredRoutes = $this->getRegisteredRoutes();
        
        $missingRoutes = [];
        
        foreach ($routeUsage as $routeName => $details) {
            if (!isset($registeredRoutes[$routeName])) {
                $missingRoutes[$routeName] = $details;
            }
        }
        
        return $missingRoutes;
    }

    /**
     * Scan route usage in the codebase
     */
    protected function scanRouteUsage()
    {
        $routeUsage = [];
        
        // Get routes from RouteAuditService if available
        try {
            $routeUsage = $this->routeAuditService->scanRouteUsage();
        } catch (\Exception $e) {
            // Fallback: Basic route scanning
            $this->warn('RouteAuditService not available, using basic scanning');
            
            // Simple route usage scanning from views and controllers
            $files = array_merge(
                glob(base_path('resources/views/**/*.blade.php')),
                glob(base_path('app/Http/Controllers/**/*.php'))
            );
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                // Basic regex to find route() calls
                preg_match_all('/route\([\'\"]([\w\.]+)[\'\"]/', $content, $matches);
                
                foreach ($matches[1] as $routeName) {
                    $routeUsage[$routeName] = ['files' => [$file]];
                }
            }
        }
        
        return $routeUsage;
    }

    /**
     * Get registered routes
     */
    protected function getRegisteredRoutes()
    {
        $routes = [];
        
        foreach (app('router')->getRoutes()->getRoutes() as $route) {
            $name = $route->getName();
            if ($name) {
                $routes[$name] = [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'action' => $route->getActionName()
                ];
            }
        }
        
        return $routes;
    }
}
