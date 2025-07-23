<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 403 Forbidden Error Diagnosis ===\n\n";

// 1. Check middleware configuration
echo "1. CHECKING MIDDLEWARE CONFIGURATION:\n";
$middlewareGroups = config('app.middleware_groups', []);
$routeMiddleware = config('app.route_middleware', []);

echo "   Admin middleware groups:\n";
foreach ($middlewareGroups as $group => $middlewares) {
    if (strpos($group, 'admin') !== false) {
        echo "     {$group}: " . implode(', ', $middlewares) . "\n";
    }
}

echo "   Route middleware:\n";
foreach ($routeMiddleware as $key => $middleware) {
    if (strpos($key, 'admin') !== false || strpos($key, 'permission') !== false || strpos($key, 'role') !== false) {
        echo "     {$key}: {$middleware}\n";
    }
}

// 2. Check policies
echo "\n2. CHECKING POLICIES:\n";
$policies = glob('app/Policies/*.php');
foreach ($policies as $policy) {
    $policyName = basename($policy, '.php');
    echo "   - {$policyName}\n";
    
    // Check if policy is registered
    $policyClass = "App\\Policies\\{$policyName}";
    if (class_exists($policyClass)) {
        $methods = get_class_methods($policyClass);
        $policyMethods = array_filter($methods, function($method) {
            return !in_array($method, ['__construct', 'before', 'after']) && !str_starts_with($method, '__');
        });
        echo "     Methods: " . implode(', ', $policyMethods) . "\n";
    }
}

// 3. Check route definitions and their middleware
echo "\n3. CHECKING ROUTE DEFINITIONS:\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $adminRoutes = [];
    
    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && str_starts_with($name, 'admin.')) {
            $adminRoutes[] = [
                'name' => $name,
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'middleware' => $route->middleware(),
                'action' => $route->getActionName()
            ];
        }
    }
    
    echo "   Found " . count($adminRoutes) . " admin routes\n";
    
    // Show sample routes with their middleware
    echo "   Sample admin routes and their middleware:\n";
    $sampleRoutes = array_slice($adminRoutes, 0, 10);
    foreach ($sampleRoutes as $route) {
        echo "     {$route['name']}: " . implode(', ', $route['middleware']) . "\n";
    }
    
} catch (\Exception $e) {
    echo "   Error checking routes: " . $e->getMessage() . "\n";
}

// 4. Check admin authentication and permissions
echo "\n4. CHECKING ADMIN AUTHENTICATION:\n";
try {
    // Get a sample admin user
    $admin = \App\Models\Admin::with('roles.permissions')->first();
    if ($admin) {
        echo "   Sample admin: {$admin->name} (ID: {$admin->id})\n";
        echo "   Is super admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . "\n";
        echo "   Organization ID: " . ($admin->organization_id ?? 'None') . "\n";
        echo "   Branch ID: " . ($admin->branch_id ?? 'None') . "\n";
        
        // Check roles
        $roles = $admin->roles;
        echo "   Roles: " . $roles->pluck('name')->implode(', ') . "\n";
        
        // Check permissions
        if (method_exists($admin, 'getAllPermissions')) {
            $permissions = $admin->getAllPermissions();
            echo "   Total permissions: " . $permissions->count() . "\n";
            echo "   Sample permissions: " . $permissions->take(5)->pluck('name')->implode(', ') . "\n";
        }
        
        // Test specific permission checks
        echo "   Testing permission checks:\n";
        $testPermissions = ['organizations.view', 'branches.view', 'users.view', 'menus.view'];
        foreach ($testPermissions as $permission) {
            $hasPermission = false;
            try {
                if (method_exists($admin, 'hasPermissionTo')) {
                    $hasPermission = $admin->hasPermissionTo($permission, 'admin');
                }
            } catch (\Exception $e) {
                echo "     {$permission}: ERROR - " . $e->getMessage() . "\n";
                continue;
            }
            echo "     {$permission}: " . ($hasPermission ? 'GRANTED' : 'DENIED') . "\n";
        }
        
    } else {
        echo "   No admin users found in database\n";
    }
    
} catch (\Exception $e) {
    echo "   Error checking admin authentication: " . $e->getMessage() . "\n";
}

// 5. Check guard configuration
echo "\n5. CHECKING GUARD CONFIGURATION:\n";
$guards = config('auth.guards', []);
foreach ($guards as $guard => $config) {
    if (strpos($guard, 'admin') !== false) {
        echo "   {$guard}:\n";
        echo "     Driver: {$config['driver']}\n";
        echo "     Provider: {$config['provider']}\n";
    }
}

$providers = config('auth.providers', []);
foreach ($providers as $provider => $config) {
    if (strpos($provider, 'admin') !== false) {
        echo "   Provider {$provider}:\n";
        echo "     Driver: {$config['driver']}\n";
        echo "     Model: {$config['model']}\n";
    }
}

// 6. Check permission package configuration
echo "\n6. CHECKING PERMISSION PACKAGE CONFIGURATION:\n";
$permissionConfig = config('permission', []);
echo "   Cache key: " . ($permissionConfig['cache']['key'] ?? 'Not set') . "\n";
$cacheExpiration = $permissionConfig['cache']['expiration_time'] ?? 'Not set';
echo "   Cache expiration: " . (is_object($cacheExpiration) ? get_class($cacheExpiration) : $cacheExpiration) . "\n";
echo "   Table names:\n";
$tableNames = $permissionConfig['table_names'] ?? [];
foreach ($tableNames as $key => $table) {
    echo "     {$key}: {$table}\n";
}

// 7. Check for common 403 causes
echo "\n7. COMMON 403 ERROR CAUSES:\n";

// Check if permissions table exists and has data
try {
    $permissionCount = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->count();
    echo "   ✅ Permissions table exists with {$permissionCount} admin permissions\n";
} catch (\Exception $e) {
    echo "   ❌ Permissions table issue: " . $e->getMessage() . "\n";
}

// Check if roles table exists and has data
try {
    $roleCount = \Spatie\Permission\Models\Role::where('guard_name', 'admin')->count();
    echo "   ✅ Roles table exists with {$roleCount} admin roles\n";
} catch (\Exception $e) {
    echo "   ❌ Roles table issue: " . $e->getMessage() . "\n";
}

// Check if model_has_permissions table has data
try {
    $modelPermissionCount = \Illuminate\Support\Facades\DB::table('model_has_permissions')
        ->where('model_type', 'App\\Models\\Admin')
        ->count();
    echo "   Model has permissions entries: {$modelPermissionCount}\n";
} catch (\Exception $e) {
    echo "   ❌ Model permissions table issue: " . $e->getMessage() . "\n";
}

// Check if model_has_roles table has data
try {
    $modelRoleCount = \Illuminate\Support\Facades\DB::table('model_has_roles')
        ->where('model_type', 'App\\Models\\Admin')
        ->count();
    echo "   Model has roles entries: {$modelRoleCount}\n";
} catch (\Exception $e) {
    echo "   ❌ Model roles table issue: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS FOR FIXING 403 ERRORS ===\n";
echo "1. Check if admin user has proper roles assigned\n";
echo "2. Verify permissions are properly synced to roles\n";
echo "3. Clear permission cache: php artisan permission:cache-reset\n";
echo "4. Check middleware order in routes\n";
echo "5. Verify guard name consistency ('admin' vs 'web')\n";
echo "6. Check policy methods match controller actions\n";
echo "7. Ensure proper authentication before permission checks\n";
