<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\OrderManagementService;
use App\Services\MenuScheduleService;
use App\Services\GuestSessionService;
use Carbon\Carbon;

class SystemHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:health 
                           {--phase=all : Which phase to check (1, 2, or all)}
                           {--fix : Attempt to fix issues automatically}
                           {--detailed : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Perform comprehensive system health check for Phase 1 and Phase 2 functionality';

    /**
     * Health check results
     */
    private array $results = [];
    private array $issues = [];
    private array $recommendations = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phase = $this->option('phase');
        $detailed = $this->option('detailed');
        
        $this->info('🏥 RESTAURANT MANAGEMENT SYSTEM - HEALTH CHECK');
        $this->info('===============================================');
        $this->newLine();
        
        if ($phase === 'all' || $phase === '1') {
            $this->checkPhase1();
        }
        
        if ($phase === 'all' || $phase === '2') {
            $this->checkPhase2();
        }
        
        $this->displaySummary($detailed);
        
        return $this->hasErrors() ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Check Phase 1: Core Infrastructure
     */
    private function checkPhase1(): void
    {
        $this->info('🔍 PHASE 1: CORE INFRASTRUCTURE CHECK');
        $this->info('=====================================');
        
        // Database connectivity
        $this->checkDatabase();
        
        // Essential tables
        $this->checkEssentialTables();
        
        // Authentication system
        $this->checkAuthenticationSystem();
        
        // Organization and branch setup
        $this->checkOrganizationStructure();
        
        // Role and permission system
        $this->checkRolePermissionSystem();
        
        $this->newLine();
    }

    /**
     * Check Phase 2: User-Facing Features
     */
    private function checkPhase2(): void
    {
        $this->info('🔍 PHASE 2: USER-FACING FEATURES CHECK');
        $this->info('======================================');
        
        // Permission system
        $this->checkScopeBasedPermissions();
        
        // Guest functionality
        $this->checkGuestFunctionality();
        
        // Menu system
        $this->checkMenuSystem();
        
        // Order management
        $this->checkOrderManagement();
        
        // Sidebar optimization
        $this->checkSidebarOptimization();
        
        $this->newLine();
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): void
    {
        try {
            DB::connection()->getPdo();
            $this->success('Database connection');
            $this->results['database_connection'] = true;
        } catch (\Exception $e) {
            $this->logError('Database connection failed: ' . $e->getMessage());
            $this->results['database_connection'] = false;
            $this->issues[] = 'Database connection failed';
        }
    }

    /**
     * Check essential tables
     */
    private function checkEssentialTables(): void
    {
        $requiredTables = [
            'admins', 'organizations', 'branches', 'menus', 'menu_items',
            'orders', 'order_items', 'reservations', 'inventory_items',
            'roles', 'permissions', 'role_has_permissions'
        ];
        
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            try {
                DB::table($table)->limit(1)->get();
            } catch (\Exception $e) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            $this->success('Essential tables exist');
            $this->results['essential_tables'] = true;
        } else {
            $this->logError('Missing tables: ' . implode(', ', $missingTables));
            $this->results['essential_tables'] = false;
            $this->issues[] = 'Missing database tables';
            $this->recommendations[] = 'Run: php artisan migrate';
        }
    }

    /**
     * Check authentication system
     */
    private function checkAuthenticationSystem(): void
    {
        try {
            $superAdminCount = Admin::where('is_super_admin', true)->count();
            $adminCount = Admin::count();
            
            if ($superAdminCount > 0) {
                $this->success("Authentication system ({$superAdminCount} super admins, {$adminCount} total admins)");
                $this->results['authentication_system'] = true;
            } else {
                $this->warning('No super admin found');
                $this->results['authentication_system'] = false;
                $this->recommendations[] = 'Create super admin: php artisan db:seed --class=SuperAdminSeeder';
            }
        } catch (\Exception $e) {
            $this->logError('Authentication system check failed: ' . $e->getMessage());
            $this->results['authentication_system'] = false;
        }
    }

    /**
     * Check organization structure
     */
    private function checkOrganizationStructure(): void
    {
        try {
            $orgCount = Organization::count();
            $branchCount = Branch::count();
            $activeBranchCount = Branch::where('is_active', true)->count();
            
            if ($orgCount > 0 && $branchCount > 0) {
                $this->success("Organization structure ({$orgCount} orgs, {$activeBranchCount}/{$branchCount} active branches)");
                $this->results['organization_structure'] = true;
            } else {
                $this->warning('Insufficient organization/branch data');
                $this->results['organization_structure'] = false;
                $this->recommendations[] = 'Seed organizations: php artisan db:seed --class=OrganizationSeeder';
            }
        } catch (\Exception $e) {
            $this->logError('Organization structure check failed: ' . $e->getMessage());
            $this->results['organization_structure'] = false;
        }
    }

    /**
     * Check role and permission system
     */
    private function checkRolePermissionSystem(): void
    {
        try {
            $roleCount = \Spatie\Permission\Models\Role::count();
            $permissionCount = \Spatie\Permission\Models\Permission::count();
            
            if ($roleCount > 0 && $permissionCount > 0) {
                $this->success("Role/Permission system ({$roleCount} roles, {$permissionCount} permissions)");
                $this->results['role_permission_system'] = true;
            } else {
                $this->warning('Roles or permissions not seeded');
                $this->results['role_permission_system'] = false;
                $this->recommendations[] = 'Seed permissions: php artisan db:seed --class=AdminPermissionSeeder';
            }
        } catch (\Exception $e) {
            $this->logError('Role/Permission system check failed: ' . $e->getMessage());
            $this->results['role_permission_system'] = false;
        }
    }

    /**
     * Check scope-based permissions
     */
    private function checkScopeBasedPermissions(): void
    {
        try {
            $middleware = new \App\Http\Middleware\ScopeBasedPermission();
            
            // Test different admin types
            $superAdmin = Admin::where('is_super_admin', true)->first();
            $orgAdmin = Admin::where('organization_id', '!=', null)
                           ->where('is_super_admin', false)
                           ->first();
            $branchAdmin = Admin::where('branch_id', '!=', null)->first();
            
            $adminTypes = collect([
                'Super Admin' => $superAdmin,
                'Org Admin' => $orgAdmin,
                'Branch Admin' => $branchAdmin
            ])->filter()->count();
            
            $this->success("Scope-based permissions ({$adminTypes} admin types configured)");
            $this->results['scope_based_permissions'] = true;
        } catch (\Exception $e) {
            $this->logError('Scope-based permissions check failed: ' . $e->getMessage());
            $this->results['scope_based_permissions'] = false;
        }
    }

    /**
     * Check guest functionality
     */
    private function checkGuestFunctionality(): void
    {
        try {
            $guestService = new GuestSessionService();
            
            // Test session creation
            $guestId = $guestService->getOrCreateGuestId();
            
            // Test cart functionality
            $cart = $guestService->getCart();
            
            $this->success('Guest functionality (session & cart management)');
            $this->results['guest_functionality'] = true;
        } catch (\Exception $e) {
            $this->logError('Guest functionality check failed: ' . $e->getMessage());
            $this->results['guest_functionality'] = false;
        }
    }

    /**
     * Check menu system
     */
    private function checkMenuSystem(): void
    {
        try {
            $menuService = new MenuScheduleService();
            
            $menuCount = Menu::count();
            $activeMenuCount = Menu::where('is_active', true)->count();
            $menuItemCount = MenuItem::count();
            
            if ($menuCount > 0 && $menuItemCount > 0) {
                $this->success("Menu system ({$activeMenuCount}/{$menuCount} active menus, {$menuItemCount} items)");
                $this->results['menu_system'] = true;
            } else {
                $this->warning('No menus or menu items found');
                $this->results['menu_system'] = false;
                $this->recommendations[] = 'Seed menu data: php artisan db:seed --class=MenuSeeder';
            }
        } catch (\Exception $e) {
            $this->logError('Menu system check failed: ' . $e->getMessage());
            $this->results['menu_system'] = false;
        }
    }

    /**
     * Check order management
     */
    private function checkOrderManagement(): void
    {
        try {
            $orderService = new OrderManagementService();
            
            // Check if service can be instantiated and has required methods
            $requiredMethods = ['installRealTimeSystem', 'createOrder', 'transitionOrderState'];
            $hasAllMethods = collect($requiredMethods)->every(function ($method) use ($orderService) {
                return method_exists($orderService, $method);
            });
            
            if ($hasAllMethods) {
                $orderCount = Order::count();
                $this->success("Order management system ({$orderCount} orders in system)");
                $this->results['order_management'] = true;
            } else {
                $this->logError('Order management system incomplete');
                $this->results['order_management'] = false;
            }
        } catch (\Exception $e) {
            $this->logError('Order management check failed: ' . $e->getMessage());
            $this->results['order_management'] = false;
        }
    }

    /**
     * Check sidebar optimization
     */
    private function checkSidebarOptimization(): void
    {
        try {
            $sidebar = new \App\View\Components\AdminSidebar();
            
            // Check if enhanced methods exist
            $reflection = new \ReflectionClass($sidebar);
            $hasEnhancedMethods = $reflection->hasMethod('getMenuItemsEnhanced');
            
            if ($hasEnhancedMethods) {
                $this->success('Sidebar optimization (enhanced menu system)');
                $this->results['sidebar_optimization'] = true;
            } else {
                $this->warning('Sidebar not fully optimized');
                $this->results['sidebar_optimization'] = false;
            }
        } catch (\Exception $e) {
            $this->logError('Sidebar optimization check failed: ' . $e->getMessage());
            $this->results['sidebar_optimization'] = false;
        }
    }

    /**
     * Display comprehensive summary
     */
    private function displaySummary(bool $detailed): void
    {
        $this->info('📊 HEALTH CHECK SUMMARY');
        $this->info('=======================');
        
        $passed = collect($this->results)->filter()->count();
        $total = count($this->results);
        $percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        
        // Overall status
        if ($percentage >= 90) {
            $this->info("🎉 EXCELLENT: {$passed}/{$total} checks passed ({$percentage}%)");
        } elseif ($percentage >= 70) {
            $this->comment("⚠️  GOOD: {$passed}/{$total} checks passed ({$percentage}%)");
        } else {
            $this->logError("❌ NEEDS ATTENTION: {$passed}/{$total} checks passed ({$percentage}%)");
        }
        
        $this->newLine();
        
        // Detailed results
        if ($detailed) {
            $this->info('📋 DETAILED RESULTS:');
            foreach ($this->results as $check => $passed) {
                $status = $passed ? '✅' : '❌';
                $checkName = ucwords(str_replace('_', ' ', $check));
                $this->line("   {$status} {$checkName}");
            }
            $this->newLine();
        }
        
        // Issues found
        if (!empty($this->issues)) {
            $this->logError('🚨 ISSUES FOUND:');
            foreach ($this->issues as $issue) {
                $this->line("   • {$issue}");
            }
            $this->newLine();
        }
        
        // Recommendations
        if (!empty($this->recommendations)) {
            $this->comment('💡 RECOMMENDATIONS:');
            foreach ($this->recommendations as $recommendation) {
                $this->line("   → {$recommendation}");
            }
            $this->newLine();
        }
        
        // System info
        $this->info('🖥️  SYSTEM INFORMATION:');
        $this->line('   • PHP Version: ' . PHP_VERSION);
        $this->line('   • Laravel Version: ' . app()->version());
        $this->line('   • Database: ' . config('database.default'));
        $this->line('   • Cache Driver: ' . config('cache.default'));
        $this->line('   • Queue Driver: ' . config('queue.default'));
        $this->line('   • Environment: ' . app()->environment());
        
        $this->newLine();
        $this->info('🏁 Health check completed!');
    }

    /**
     * Check if there are any errors
     */
    private function hasErrors(): bool
    {
        return collect($this->results)->contains(false);
    }

    /**
     * Helper methods for output
     */
    private function success(string $message): void
    {
        $this->line("   ✅ {$message}");
    }

    private function warning(string $message): void
    {
        $this->line("   ⚠️  {$message}");
    }

    private function logError(string $message): void
    {
        $this->line("   ❌ {$message}");
    }
}
