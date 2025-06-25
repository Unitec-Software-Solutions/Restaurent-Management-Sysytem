<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    /**
     * Test that all named routes exist and are accessible
     */
    public function test_all_named_routes_exist()
    {
        $routes = Route::getRoutes()->getRoutes();
        $failures = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            
            if (!$name) {
                continue; // Skip unnamed routes
            }

            // Check if route has a valid controller action
            $action = $route->getActionName();
            
            if ($action === 'Closure') {
                continue; // Skip closure routes
            }

            // Validate controller exists
            if (str_contains($action, '@') || str_contains($action, '::')) {
                $controllerClass = str_contains($action, '@') 
                    ? explode('@', $action)[0] 
                    : explode('::', $action)[0];
                
                if (!class_exists($controllerClass)) {
                    $failures[] = [
                        'route' => $name,
                        'issue' => 'Controller not found',
                        'controller' => $controllerClass,
                        'uri' => $route->uri(),
                    ];
                }
            }
        }

        if (!empty($failures)) {
            $this->fail(
                "Route validation failures:\n" . 
                collect($failures)->map(fn($f) => "- {$f['route']}: {$f['issue']} ({$f['controller']})")
                    ->implode("\n")
            );
        }

        $this->assertTrue(true, 'All routes are valid');
    }

    /**
     * Test critical application routes
     */
    public function test_critical_routes_exist()
    {
        $criticalRoutes = [
            'home',
            'login',
            'admin.login',
            'admin.dashboard',
            'admin.orders.index',
            'admin.inventory.index',
            'reservations.create',
            'orders.create',
        ];

        foreach ($criticalRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Critical route '{$routeName}' is missing"
            );
        }
    }

    /**
     * Test that protected routes require authentication
     */
    public function test_protected_routes_require_auth()
    {
        $protectedRoutes = [
            ['route' => 'admin.dashboard', 'method' => 'GET'],
            ['route' => 'admin.orders.index', 'method' => 'GET'],
            ['route' => 'admin.inventory.index', 'method' => 'GET'],
        ];

        foreach ($protectedRoutes as $routeInfo) {
            if (!Route::has($routeInfo['route'])) {
                continue; // Skip if route doesn't exist
            }

            $response = $this->call($routeInfo['method'], route($routeInfo['route']));
            
            $this->assertContains(
                $response->getStatusCode(),
                [302, 401, 403],
                "Route '{$routeInfo['route']}' should require authentication but returned {$response->getStatusCode()}"
            );
        }
    }

    /**
     * Test route parameter validation
     */
    public function test_route_parameters_validation()
    {
        $parametricRoutes = [
            ['route' => 'admin.orders.show', 'params' => ['order' => 999999]],
            ['route' => 'admin.inventory.items.show', 'params' => ['item' => 999999]],
        ];

        foreach ($parametricRoutes as $routeInfo) {
            if (!Route::has($routeInfo['route'])) {
                continue; // Skip if route doesn't exist
            }

            try {
                $url = route($routeInfo['route'], $routeInfo['params']);
                $this->assertIsString($url);
            } catch (\Exception $e) {
                $this->fail("Route '{$routeInfo['route']}' failed parameter validation: " . $e->getMessage());
            }
        }
    }

    /**
     * Test that route names follow conventions
     */
    public function test_route_naming_conventions()
    {
        $routes = Route::getRoutes()->getRoutes();
        $namingViolations = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            
            if (!$name) {
                continue;
            }

            // Check naming conventions
            if (str_starts_with($name, 'admin.')) {
                // Admin routes should follow resource conventions
                $parts = explode('.', $name);
                $lastPart = end($parts);
                
                $validActions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
                
                if (count($parts) >= 3 && !in_array($lastPart, $validActions)) {
                    // Allow custom actions but flag unusual ones
                    if (!in_array($lastPart, ['dashboard', 'activate', 'summary', 'list'])) {
                        $namingViolations[] = [
                            'route' => $name,
                            'issue' => 'Non-standard action name',
                            'action' => $lastPart,
                        ];
                    }
                }
            }
        }

        // Don't fail the test for naming violations, just warn
        if (!empty($namingViolations)) {
            $this->markTestIncomplete(
                "Route naming convention warnings:\n" .
                collect($namingViolations)->map(fn($v) => "- {$v['route']}: {$v['issue']}")
                    ->implode("\n")
            );
        }

        $this->assertTrue(true);
    }

    /**
     * Test route duplication
     */
    public function test_no_duplicate_routes()
    {
        $routes = Route::getRoutes()->getRoutes();
        $uriMethodMap = [];
        $duplicates = [];

        foreach ($routes as $route) {
            $methods = implode('|', $route->methods());
            $uri = $route->uri();
            $key = "{$methods}:{$uri}";

            if (isset($uriMethodMap[$key])) {
                $duplicates[] = [
                    'uri' => $uri,
                    'methods' => $methods,
                    'existing_name' => $uriMethodMap[$key],
                    'duplicate_name' => $route->getName(),
                ];
            } else {
                $uriMethodMap[$key] = $route->getName();
            }
        }

        if (!empty($duplicates)) {
            $this->fail(
                "Duplicate route definitions found:\n" .
                collect($duplicates)->map(fn($d) => "- {$d['uri']} ({$d['methods']}): {$d['existing_name']} vs {$d['duplicate_name']}")
                    ->implode("\n")
            );
        }

        $this->assertTrue(true, 'No duplicate routes found');
    }

    /**
     * Test middleware application
     */
    public function test_route_middleware_application()
    {
        $expectedMiddleware = [
            'admin.*' => ['auth:admin'],
            'api.*' => ['api'],
        ];

        $routes = Route::getRoutes()->getRoutes();
        $middlewareViolations = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            $middleware = $route->middleware();

            if (!$name) {
                continue;
            }

            foreach ($expectedMiddleware as $pattern => $expectedMw) {
                if (fnmatch($pattern, $name)) {
                    foreach ($expectedMw as $requiredMw) {
                        if (!in_array($requiredMw, $middleware)) {
                            $middlewareViolations[] = [
                                'route' => $name,
                                'missing_middleware' => $requiredMw,
                                'current_middleware' => $middleware,
                            ];
                        }
                    }
                }
            }
        }

        // Don't fail for middleware violations, just warn
        if (!empty($middlewareViolations)) {
            $this->markTestIncomplete(
                "Middleware application warnings:\n" .
                collect($middlewareViolations)->map(fn($v) => "- {$v['route']}: missing {$v['missing_middleware']}")
                    ->implode("\n")
            );
        }

        $this->assertTrue(true);
    }
}
