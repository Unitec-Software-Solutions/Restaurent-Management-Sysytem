<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RouteDebugController extends Controller
{
    /**
     * Show route debugging interface
     */
    public function index(Request $request)
    {
        if (!config('app.debug')) {
            abort(404);
        }

        $routes = Route::getRoutes()->getRoutes();
        $routeData = [];

        foreach ($routes as $route) {
            $routeData[] = [
                'name' => $route->getName(),
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'parameters' => $route->parameterNames(),
                'controller_exists' => $this->checkControllerExists($route->getActionName()),
                'method_exists' => $this->checkMethodExists($route->getActionName()),
            ];
        }

        // Filter routes if search provided
        if ($search = $request->get('search')) {
            $routeData = array_filter($routeData, function ($route) use ($search) {
                return Str::contains($route['name'], $search) ||
                       Str::contains($route['uri'], $search) ||
                       Str::contains($route['action'], $search);
            });
        }

        // Sort routes
        $sortBy = $request->get('sort', 'name');
        usort($routeData, function ($a, $b) use ($sortBy) {
            return ($a[$sortBy] ?? '') <=> ($b[$sortBy] ?? '');
        });

        return view('admin.debug.routes', [
            'routes' => $routeData,
            'search' => $search,
            'sort' => $sortBy,
            'stats' => $this->getRouteStats($routeData),
        ]);
    }

    /**
     * Test a specific route
     */
    public function testRoute(Request $request)
    {
        $routeName = $request->get('route');
        
        if (!Route::has($routeName)) {
            return response()->json([
                'status' => 'error',
                'message' => "Route '{$routeName}' not found"
            ]);
        }

        try {
            $route = Route::getRoutes()->getByName($routeName);
            $url = route($routeName);
            
            return response()->json([
                'status' => 'success',
                'route_name' => $routeName,
                'url' => $url,
                'methods' => $route->methods(),
                'controller' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate route definition
     */
    public function generateRoute(Request $request)
    {
        $uri = $request->get('uri');
        $method = $request->get('method', 'GET');
        $name = $request->get('name');
        $controller = $request->get('controller');
        $action = $request->get('action');

        $definition = $this->buildRouteDefinition($uri, $method, $name, $controller, $action);

        return response()->json([
            'definition' => $definition,
            'file_suggestion' => $this->suggestRouteFile($name)
        ]);
    }

    /**
     * Export route list
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'json');
        $routes = Route::getRoutes()->getRoutes();
        $data = [];

        foreach ($routes as $route) {
            $data[] = [
                'name' => $route->getName(),
                'uri' => $route->uri(),
                'methods' => implode('|', $route->methods()),
                'action' => $route->getActionName(),
                'middleware' => implode('|', $route->middleware()),
            ];
        }

        switch ($format) {
            case 'csv':
                return $this->exportCsv($data);
            case 'json':
            default:
                return response()->json($data);
        }
    }

    protected function checkControllerExists(string $action): bool
    {
        if ($action === 'Closure') {
            return true;
        }

        if (str_contains($action, '@') || str_contains($action, '::')) {
            $controllerClass = str_contains($action, '@') 
                ? explode('@', $action)[0] 
                : explode('::', $action)[0];
            
            return class_exists($controllerClass);
        }

        return false;
    }

    protected function checkMethodExists(string $action): bool
    {
        if ($action === 'Closure') {
            return true;
        }

        if (str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            return class_exists($controller) && method_exists($controller, $method);
        }

        if (str_contains($action, '::')) {
            [$controller, $method] = explode('::', $action);
            return class_exists($controller) && method_exists($controller, $method);
        }

        return false;
    }

    protected function getRouteStats(array $routes): array
    {
        $stats = [
            'total' => count($routes),
            'named' => 0,
            'unnamed' => 0,
            'valid_controllers' => 0,
            'invalid_controllers' => 0,
            'methods' => [],
            'middleware' => [],
        ];

        foreach ($routes as $route) {
            if ($route['name']) {
                $stats['named']++;
            } else {
                $stats['unnamed']++;
            }

            if ($route['controller_exists']) {
                $stats['valid_controllers']++;
            } else {
                $stats['invalid_controllers']++;
            }

            foreach ($route['methods'] as $method) {
                $stats['methods'][$method] = ($stats['methods'][$method] ?? 0) + 1;
            }

            foreach ($route['middleware'] as $middleware) {
                $stats['middleware'][$middleware] = ($stats['middleware'][$middleware] ?? 0) + 1;
            }
        }

        return $stats;
    }

    protected function buildRouteDefinition(string $uri, string $method, string $name, string $controller, string $action): string
    {
        $method = strtolower($method);
        $definition = "Route::{$method}('{$uri}', [{$controller}::class, '{$action}'])";
        
        if ($name) {
            $definition .= "->name('{$name}')";
        }

        return $definition . ';';
    }

    protected function suggestRouteFile(string $routeName): string
    {
        if (Str::startsWith($routeName, 'admin.')) {
            return 'routes/web.php (admin section)';
        }

        if (Str::startsWith($routeName, 'api.')) {
            return 'routes/api.php';
        }

        return 'routes/web.php';
    }

    protected function exportCsv(array $data): \Illuminate\Http\Response
    {
        $csv = "Name,URI,Methods,Action,Middleware\n";
        
        foreach ($data as $row) {
            $csv .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field ?? '') . '"';
            }, $row)) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="routes-' . date('Y-m-d') . '.csv"');
    }
}
