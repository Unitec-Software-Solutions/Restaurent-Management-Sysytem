<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SidebarHealthCheck extends Command
{
    protected $signature = 'sidebar:health-check
                            {--json : Output results in JSON format}
                            {--fix : Automatically fix minor issues}';

    protected $description = 'Comprehensive health check for admin sidebar and authentication system';

    private $results = [
        'overall_status' => 'unknown',
        'checks' => [],
        'recommendations' => [],
        'fixed_issues' => []
    ];

    public function handle()
    {
        $this->info('🏥 Admin Sidebar Health Check');
        $this->newLine();

        $this->checkRouteAvailability();
        $this->checkAuthenticationConfig();
        $this->checkSessionConfiguration();
        $this->checkDatabaseTables();
        $this->checkMiddlewareRegistration();
        $this->checkSidebarComponent();
        $this->checkPermissionSystem();

        $this->calculateOverallStatus();
        
        if ($this->option('json')) {
            $this->outputJson();
        } else {
            $this->outputReport();
        }

        return $this->results['overall_status'] === 'healthy' ? 0 : 1;
    }

    private function checkRouteAvailability()
    {
        $this->info('🛣️ Checking Route Availability...');

        $essentialRoutes = [
            'admin.dashboard' => 'Admin Dashboard',
            'admin.inventory.index' => 'Inventory List',
            'admin.inventory.dashboard' => 'Inventory Dashboard',
            'admin.orders.index' => 'Orders List',
            'admin.reservations.index' => 'Reservations List',
            'admin.suppliers.index' => 'Suppliers List',
            'admin.customers.index' => 'Customers List',
            'admin.users.index' => 'Users List',
            'admin.menus.index' => 'Menus List',
            'admin.reports.index' => 'Reports',
            'admin.login' => 'Admin Login',
            'admin.logout.action' => 'Admin Logout'
        ];

        $missing = [];
        $available = [];

        foreach ($essentialRoutes as $route => $description) {
            if (Route::has($route)) {
                $available[] = $route;
                $this->line("  ✅ {$description} ({$route})");
            } else {
                $missing[] = $route;
                $this->line("  ❌ {$description} ({$route})");
            }
        }

        $this->results['checks']['routes'] = [
            'status' => count($missing) === 0 ? 'pass' : 'fail',
            'available_count' => count($available),
            'missing_count' => count($missing),
            'missing_routes' => $missing,
            'message' => count($missing) === 0 
                ? 'All essential routes are available'
                : count($missing) . ' essential routes are missing'
        ];

        if (count($missing) > 0) {
            $this->results['recommendations'][] = 'Register missing routes in routes/web.php';
        }
    }

    private function checkAuthenticationConfig()
    {
        $this->info('🔐 Checking Authentication Configuration...');

        $checks = [];

        // Default guard
        $defaultGuard = config('auth.defaults.guard');
        $checks['default_guard'] = [
            'expected' => 'admin',
            'actual' => $defaultGuard,
            'status' => $defaultGuard === 'admin' ? 'pass' : 'fail'
        ];

        // Admin guard configuration
        $adminGuard = config('auth.guards.admin');
        $checks['admin_guard'] = [
            'exists' => $adminGuard !== null,
            'driver' => $adminGuard['driver'] ?? null,
            'provider' => $adminGuard['provider'] ?? null,
            'status' => ($adminGuard && $adminGuard['driver'] === 'session' && $adminGuard['provider'] === 'admins') ? 'pass' : 'fail'
        ];

        // Admin provider configuration
        $adminProvider = config('auth.providers.admins');
        $checks['admin_provider'] = [
            'exists' => $adminProvider !== null,
            'driver' => $adminProvider['driver'] ?? null,
            'model' => $adminProvider['model'] ?? null,
            'status' => ($adminProvider && $adminProvider['driver'] === 'eloquent') ? 'pass' : 'fail'
        ];

        $this->results['checks']['authentication'] = $checks;

        foreach ($checks as $check => $data) {
            $status = $data['status'] === 'pass' ? '✅' : '❌';
            $this->line("  {$status} " . ucfirst(str_replace('_', ' ', $check)));
        }

        if ($checks['default_guard']['status'] === 'fail') {
            $this->results['recommendations'][] = 'Set default auth guard to "admin" in config/auth.php';
        }
    }

    private function checkSessionConfiguration()
    {
        $this->info('🍪 Checking Session Configuration...');

        $sessionDriver = config('session.driver');
        $sessionTable = config('session.table');
        $sessionLifetime = config('session.lifetime');

        $checks = [
            'driver' => [
                'value' => $sessionDriver,
                'status' => $sessionDriver === 'database' ? 'pass' : 'warning',
                'message' => $sessionDriver === 'database' ? 'Using database driver' : 'Consider using database driver for multi-user support'
            ],
            'table' => [
                'value' => $sessionTable,
                'status' => !empty($sessionTable) ? 'pass' : 'fail',
                'message' => !empty($sessionTable) ? 'Session table configured' : 'Session table not configured'
            ],
            'lifetime' => [
                'value' => $sessionLifetime,
                'status' => $sessionLifetime >= 120 ? 'pass' : 'warning',
                'message' => $sessionLifetime >= 120 ? 'Adequate session lifetime' : 'Session lifetime may be too short'
            ]
        ];

        $this->results['checks']['session'] = $checks;

        foreach ($checks as $check => $data) {
            $icon = $data['status'] === 'pass' ? '✅' : ($data['status'] === 'warning' ? '⚠️' : '❌');
            $this->line("  {$icon} " . ucfirst($check) . ": {$data['value']} - {$data['message']}");
        }
    }

    private function checkDatabaseTables()
    {
        $this->info('🗄️ Checking Database Tables...');

        $requiredTables = ['admins', 'sessions'];
        $checks = [];

        foreach ($requiredTables as $table) {
            $exists = Schema::hasTable($table);
            $checks[$table] = [
                'exists' => $exists,
                'status' => $exists ? 'pass' : 'fail'
            ];

            if ($exists && $table === 'admins') {
                $count = DB::table($table)->count();
                $checks[$table]['record_count'] = $count;
                $checks[$table]['has_records'] = $count > 0;
            }

            if ($exists && $table === 'sessions') {
                $count = DB::table($table)->count();
                $activeCount = DB::table($table)
                    ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120))->timestamp)
                    ->count();
                $checks[$table]['total_sessions'] = $count;
                $checks[$table]['active_sessions'] = $activeCount;
            }

            $status = $exists ? '✅' : '❌';
            $this->line("  {$status} Table '{$table}'" . ($exists ? '' : ' (missing)'));
        }

        $this->results['checks']['database'] = $checks;
    }

    private function checkMiddlewareRegistration()
    {
        $this->info('🛡️ Checking Middleware Registration...');

        $requiredMiddleware = [
            'auth' => \App\Http\Middleware\Authenticate::class,
            'enhanced.admin.auth' => \App\Http\Middleware\EnhancedAdminAuth::class,
        ];

        $checks = [];
        $kernel = app(\Illuminate\Foundation\Http\Kernel::class);
        $registeredMiddleware = $kernel->getRouteMiddleware();

        foreach ($requiredMiddleware as $alias => $class) {
            $isRegistered = isset($registeredMiddleware[$alias]);
            $checks[$alias] = [
                'registered' => $isRegistered,
                'class' => $class,
                'status' => $isRegistered ? 'pass' : 'fail'
            ];

            $status = $isRegistered ? '✅' : '❌';
            $this->line("  {$status} {$alias} middleware");
        }

        $this->results['checks']['middleware'] = $checks;
    }

    private function checkSidebarComponent()
    {
        $this->info('📱 Checking Sidebar Component...');

        $componentPath = app_path('View/Components/AdminSidebar.php');
        $viewPath = resource_path('views/components/admin-sidebar.blade.php');

        $checks = [
            'component_class' => [
                'exists' => file_exists($componentPath),
                'status' => file_exists($componentPath) ? 'pass' : 'fail'
            ],
            'component_view' => [
                'exists' => file_exists($viewPath),
                'status' => file_exists($viewPath) ? 'pass' : 'fail'
            ]
        ];

        // Check if old sidebar is still being used
        $oldSidebarPath = resource_path('views/partials/sidebar/admin-sidebar.blade.php');
        if (file_exists($oldSidebarPath)) {
            $content = file_get_contents($oldSidebarPath);
            $usingComponent = str_contains($content, '<x-admin-sidebar');
            $checks['migration_status'] = [
                'migrated_to_component' => $usingComponent,
                'status' => $usingComponent ? 'pass' : 'warning'
            ];
        }

        $this->results['checks']['sidebar_component'] = $checks;

        foreach ($checks as $check => $data) {
            $status = $data['status'] === 'pass' ? '✅' : ($data['status'] === 'warning' ? '⚠️' : '❌');
            $this->line("  {$status} " . ucfirst(str_replace('_', ' ', $check)));
        }
    }

    private function checkPermissionSystem()
    {
        $this->info('👤 Checking Permission System...');

        $checks = [];

        // Check if Admin model exists and has required methods
        try {
            if (class_exists(\App\Models\Admin::class)) {
                $adminModel = new \App\Models\Admin();
                $checks['admin_model'] = [
                    'exists' => true,
                    'status' => 'pass'
                ];

                // Check for permission-related methods (if they exist)
                $methods = ['hasPermission', 'can', 'cannot'];
                foreach ($methods as $method) {
                    $checks["admin_method_{$method}"] = [
                        'exists' => method_exists($adminModel, $method),
                        'status' => method_exists($adminModel, $method) ? 'pass' : 'info'
                    ];
                }
            } else {
                $checks['admin_model'] = [
                    'exists' => false,
                    'status' => 'fail'
                ];
            }
        } catch (\Exception $e) {
            $checks['admin_model'] = [
                'exists' => false,
                'error' => $e->getMessage(),
                'status' => 'fail'
            ];
        }

        $this->results['checks']['permissions'] = $checks;

        foreach ($checks as $check => $data) {
            if ($data['status'] === 'info') continue; // Skip optional methods
            
            $status = $data['status'] === 'pass' ? '✅' : '❌';
            $this->line("  {$status} " . ucfirst(str_replace('_', ' ', $check)));
        }
    }

    private function calculateOverallStatus()
    {
        $allChecks = collect($this->results['checks'])->flatten(1);
        $failures = $allChecks->where('status', 'fail')->count();
        $warnings = $allChecks->where('status', 'warning')->count();

        if ($failures > 0) {
            $this->results['overall_status'] = 'unhealthy';
        } elseif ($warnings > 0) {
            $this->results['overall_status'] = 'degraded';
        } else {
            $this->results['overall_status'] = 'healthy';
        }
    }

    private function outputReport()
    {
        $this->newLine();
        $status = $this->results['overall_status'];
        $icon = $status === 'healthy' ? '🟢' : ($status === 'degraded' ? '🟡' : '🔴');
        
        $this->info("📊 Overall System Status: {$icon} " . strtoupper($status));

        if (count($this->results['recommendations']) > 0) {
            $this->newLine();
            $this->warn('💡 Recommendations:');
            foreach ($this->results['recommendations'] as $recommendation) {
                $this->line("  • {$recommendation}");
            }
        }

        if (count($this->results['fixed_issues']) > 0) {
            $this->newLine();
            $this->info('🔧 Auto-fixed Issues:');
            foreach ($this->results['fixed_issues'] as $fix) {
                $this->line("  • {$fix}");
            }
        }

        $this->newLine();
        $this->info('🏁 Health check complete!');
    }

    private function outputJson()
    {
        $this->line(json_encode($this->results, JSON_PRETTY_PRINT));
    }
}
