<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteGroupService
{
    /**
     * Register route macros for organized route groups
     */
    public function registerRouteMacros(): void
    {
        // Admin route group macro
        Route::macro('adminGroup', function ($callback) {
            Route::group([
                'prefix' => 'admin',
                'as' => 'admin.',
                'middleware' => ['auth:admin', 'verified'],
            ], $callback);
        });

        // Organization route group macro
        Route::macro('organizationGroup', function ($callback) {
            Route::group([
                'prefix' => 'organization',
                'as' => 'organization.',
                'middleware' => ['auth', 'organization.active'],
            ], $callback);
        });

        // Branch route group macro
        Route::macro('branchGroup', function ($callback) {
            Route::group([
                'prefix' => 'branch',
                'as' => 'branch.',
                'middleware' => ['auth', 'branch.permission'],
            ], $callback);
        });

        // API route group macro
        Route::macro('apiGroup', function ($version, $callback) {
            Route::group([
                'prefix' => "api/{$version}",
                'as' => "api.{$version}.",
                'middleware' => ['api', 'throttle:60,1'],
            ], $callback);
        });

        // Guest route group macro
        Route::macro('guestGroup', function ($callback) {
            Route::group([
                'prefix' => 'guest',
                'as' => 'guest.',
                'middleware' => ['web'],
            ], $callback);
        });

        // Resource with common patterns
        Route::macro('adminResource', function ($name, $controller, $options = []) {
            $defaultOptions = [
                'middleware' => ['auth:admin'],
                'names' => [
                    'index' => "admin.{$name}.index",
                    'create' => "admin.{$name}.create",
                    'store' => "admin.{$name}.store",
                    'show' => "admin.{$name}.show",
                    'edit' => "admin.{$name}.edit",
                    'update' => "admin.{$name}.update",
                    'destroy' => "admin.{$name}.destroy",
                ],
            ];

            $options = array_merge($defaultOptions, $options);
            return Route::resource("admin/{$name}", $controller, $options);
        });
    }

    /**
     * Organize routes into logical groups
     */
    public function organizeRoutes(): array
    {
        $routes = Route::getRoutes();
        $organized = [
            'admin' => [],
            'organization' => [],
            'branch' => [],
            'api' => [],
            'guest' => [],
            'public' => [],
            'auth' => [],
        ];

        foreach ($routes as $route) {
            $name = $route->getName();
            $prefix = $this->getRoutePrefix($route);
            
            if (!$name) {
                continue;
            }

            $group = $this->categorizeRoute($name, $prefix);
            $organized[$group][] = [
                'name' => $name,
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ];
        }

        return $organized;
    }

    /**
     * Get route prefix
     */
    protected function getRoutePrefix($route): string
    {
        $uri = $route->uri();
        $parts = explode('/', $uri);
        return $parts[0] ?? '';
    }

    /**
     * Categorize route into logical group
     */
    protected function categorizeRoute(string $routeName, string $prefix): string
    {
        if (Str::startsWith($routeName, 'admin.')) {
            return 'admin';
        }

        if (Str::startsWith($routeName, 'organization.')) {
            return 'organization';
        }

        if (Str::startsWith($routeName, 'branch.')) {
            return 'branch';
        }

        if (Str::startsWith($routeName, 'api.')) {
            return 'api';
        }

        if (Str::startsWith($routeName, 'guest.')) {
            return 'guest';
        }

        if (in_array($routeName, ['login', 'logout', 'register', 'password.request', 'password.email', 'password.reset', 'password.update', 'verification.notice', 'verification.verify', 'verification.resend'])) {
            return 'auth';
        }

        return 'public';
    }

    /**
     * Generate route health report by group
     */
    public function generateGroupHealthReport(): array
    {
        $organized = $this->organizeRoutes();
        $report = [];

        foreach ($organized as $group => $routes) {
            $report[$group] = [
                'total_routes' => count($routes),
                'routes' => $routes,
                'middleware_coverage' => $this->calculateMiddlewareCoverage($routes),
                'naming_consistency' => $this->checkNamingConsistency($routes, $group),
            ];
        }

        return $report;
    }

    /**
     * Calculate middleware coverage for routes
     */
    protected function calculateMiddlewareCoverage(array $routes): array
    {
        $total = count($routes);
        if ($total === 0) {
            return ['percentage' => 0, 'details' => []];
        }

        $withMiddleware = 0;
        $middlewareUsage = [];

        foreach ($routes as $route) {
            if (!empty($route['middleware'])) {
                $withMiddleware++;
                foreach ($route['middleware'] as $middleware) {
                    $middlewareUsage[$middleware] = ($middlewareUsage[$middleware] ?? 0) + 1;
                }
            }
        }

        return [
            'percentage' => round(($withMiddleware / $total) * 100, 2),
            'details' => $middlewareUsage,
        ];
    }

    /**
     * Check naming consistency within group
     */
    protected function checkNamingConsistency(array $routes, string $group): array
    {
        $consistent = 0;
        $total = count($routes);
        $issues = [];

        foreach ($routes as $route) {
            $name = $route['name'];
            $expectedPrefix = $group === 'public' ? '' : "{$group}.";
            
            if ($group === 'public' || Str::startsWith($name, $expectedPrefix)) {
                $consistent++;
            } else {
                $issues[] = [
                    'route' => $name,
                    'expected_prefix' => $expectedPrefix,
                ];
            }
        }

        return [
            'percentage' => $total > 0 ? round(($consistent / $total) * 100, 2) : 100,
            'issues' => $issues,
        ];
    }

    /**
     * Suggest route reorganization
     */
    public function suggestReorganization(): array
    {
        $healthReport = $this->generateGroupHealthReport();
        $suggestions = [];

        foreach ($healthReport as $group => $data) {
            if ($data['naming_consistency']['percentage'] < 90) {
                $suggestions[] = [
                    'type' => 'naming_consistency',
                    'group' => $group,
                    'severity' => 'medium',
                    'message' => "Group '{$group}' has inconsistent route naming ({$data['naming_consistency']['percentage']}% consistent)",
                    'issues' => $data['naming_consistency']['issues'],
                ];
            }

            if ($data['middleware_coverage']['percentage'] < 80 && $group !== 'public') {
                $suggestions[] = [
                    'type' => 'middleware_coverage',
                    'group' => $group,
                    'severity' => 'high',
                    'message' => "Group '{$group}' has low middleware coverage ({$data['middleware_coverage']['percentage']}%)",
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Export route groups to files
     */
    public function exportRouteGroups(string $outputPath = null): array
    {
        $outputPath = $outputPath ?: base_path('routes/groups');
        $organized = $this->organizeRoutes();
        $exported = [];

        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        foreach ($organized as $group => $routes) {
            if (empty($routes)) {
                continue;
            }

            $filename = "{$outputPath}/{$group}.php";
            $content = $this->generateRouteGroupFile($group, $routes);
            
            file_put_contents($filename, $content);
            $exported[] = $filename;
        }

        return $exported;
    }

    /**
     * Generate route group file content
     */
    protected function generateRouteGroupFile(string $group, array $routes): string
    {
        $header = "<?php\n\n// {$group} routes - Generated by RouteGroupService\n";
        $header .= "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $header .= "use Illuminate\Support\Facades\Route;\n\n";

        $routeDefinitions = [];
        
        foreach ($routes as $route) {
            $method = strtolower($route['methods'][0]);
            $uri = $route['uri'];
            $action = $route['action'];
            $name = $route['name'];
            $middleware = $route['middleware'];

            $middlewareStr = !empty($middleware) ? "->middleware(['" . implode("', '", $middleware) . "'])" : '';
            $nameStr = "->name('{$name}')";

            $routeDefinitions[] = "Route::{$method}('{$uri}', {$action}){$middlewareStr}{$nameStr};";
        }

        return $header . implode("\n", $routeDefinitions) . "\n";
    }
}
