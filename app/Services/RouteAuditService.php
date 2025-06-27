<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class RouteAuditService
{
    public function generateRouteSuggestion(string $routeName, array $usages): ?array
    {
        // Analyze route name structure
        $parts = explode('.', $routeName);
        $lastPart = end($parts);
        
        // Determine resource type and action
        $resourceActions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        $isResourceAction = in_array($lastPart, $resourceActions);
        
        // Suggest controller and method
        if (count($parts) >= 2) {
            $controllerParts = array_slice($parts, 0, -1);
            $controllerName = $this->generateControllerName($controllerParts);
            $method = $isResourceAction ? $lastPart : 'index';
        } else {
            $controllerName = Str::studly($routeName) . 'Controller';
            $method = 'index';
        }

        // Analyze usage context to determine HTTP method and URI
        $httpMethod = $this->suggestHttpMethod($lastPart, $usages);
        $uri = $this->suggestUri($routeName, $parts);

        return [
            'route_name' => $routeName,
            'controller' => $controllerName,
            'method' => $method,
            'http_method' => $httpMethod,
            'uri' => $uri,
            'middleware' => $this->suggestMiddleware($routeName),
            'parameters' => $this->extractParameters($usages),
        ];
    }

    public function generateRouteDefinition(array $suggestion): string
    {
        $middleware = $suggestion['middleware'] ? "->middleware(['" . implode("', '", $suggestion['middleware']) . "'])" : '';
        $name = "->name('{$suggestion['route_name']}')";
        
        $definition = "Route::{$suggestion['http_method']}('{$suggestion['uri']}', [\\{$suggestion['controller']}::class, '{$suggestion['method']}'){$middleware}{$name};";
        
        return "// Auto-generated route for {$suggestion['route_name']}\n{$definition}";
    }

    public function createControllerStub(string $controllerClass, string $method): bool
    {
        try {
            // Determine controller path
            $relativePath = str_replace('App\\Http\\Controllers\\', '', $controllerClass);
            $relativePath = str_replace('\\', '/', $relativePath);
            $controllerPath = app_path("Http/Controllers/{$relativePath}.php");
            
            // Create directory if it doesn't exist
            $directory = dirname($controllerPath);
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Generate controller content
            $namespace = $this->getControllerNamespace($controllerClass);
            $className = class_basename($controllerClass);
            
            $content = $this->generateControllerStub($namespace, $className, $method);
            
            File::put($controllerPath, $content);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function addMethodToController(string $controllerClass, string $method): bool
    {
        try {
            $reflection = new \ReflectionClass($controllerClass);
            $controllerPath = $reflection->getFileName();
            
            if (!$controllerPath || !File::exists($controllerPath)) {
                return false;
            }

            $content = File::get($controllerPath);
            
            // Find the last method or the closing brace
            $methodStub = $this->generateMethodStub($method);
            
            // Insert before the last closing brace
            $lastBracePos = strrpos($content, '}');
            if ($lastBracePos !== false) {
                $newContent = substr($content, 0, $lastBracePos) . 
                             "\n    " . $methodStub . "\n" . 
                             substr($content, $lastBracePos);
                
                File::put($controllerPath, $newContent);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Estimate route usage based on file scanning
     */
    public function estimateRouteUsage(string $routeName): int
    {
        $usage = 0;
        $patterns = [
            "route\\(['\"]" . preg_quote($routeName, '/') . "['\"]",
            "route\\(['\"]" . preg_quote($routeName, '/') . "['\"],",
            "@route\\(['\"]" . preg_quote($routeName, '/') . "['\"]",
        ];

        $directories = [
            resource_path('views'),
            app_path('Http/Controllers'),
            app_path('Http/Middleware'),
        ];

        foreach ($directories as $directory) {
            if (!File::isDirectory($directory)) continue;

            $files = File::allFiles($directory);
            
            foreach ($files as $file) {
                $content = File::get($file->getPathname());
                
                foreach ($patterns as $pattern) {
                    $usage += preg_match_all("/$pattern/", $content);
                }
            }
        }

        return $usage;
    }

    /**
     * Generate safe route helper that never throws exceptions
     */
    public function safeRoute(string $name, array $parameters = [], string $fallback = '#'): string 
    {
        try {
            if (Route::has($name)) {
                return route($name, $parameters);
            }
            
            Log::warning("Route '{$name}' not found, using fallback");
            return $fallback;
        } catch (\Exception $e) {
            Log::error("Route generation failed for '{$name}': " . $e->getMessage());
            return $fallback;
        }
    }

    /**
     * Check if a route exists safely
     */
    public function routeExists(string $name): bool
    {
        try {
            return Route::has($name);
        } catch (\Exception $e) {
            Log::debug("Route check failed for '{$name}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate route definition with automatic fallback
     */
    public function generateSafeRouteDefinition(string $uri, string $controller, string $method = 'index', string $httpMethod = 'GET'): string
    {
        $routeName = $this->generateRouteName($uri);
        $middleware = $this->suggestMiddleware($routeName);
        $middlewareStr = $middleware ? "->middleware(['" . implode("', '", $middleware) . "'])" : '';
        
        return "// Auto-generated safe route\nRoute::{$httpMethod}('{$uri}', [\\{$controller}::class, '{$method}']){$middlewareStr}->name('{$routeName}');";
    }

    /**
     * Generate route name from URI
     */
    protected function generateRouteName(string $uri): string
    {
        $name = trim($uri, '/');
        $name = str_replace('/', '.', $name);
        $name = str_replace(['{', '}'], '', $name);
        $name = preg_replace('/[^a-zA-Z0-9\.]/', '', $name);
        
        return $name ?: 'home';
    }

    /**
     * Create fallback controller with all missing methods
     */
    public function createFallbackController(string $controllerClass, array $methods): bool
    {
        try {
            $relativePath = str_replace('App\\Http\\Controllers\\', '', $controllerClass);
            $relativePath = str_replace('\\', '/', $relativePath);
            $controllerPath = app_path("Http/Controllers/{$relativePath}.php");
            
            // Create directory if it doesn't exist
            $directory = dirname($controllerPath);
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Generate controller content with all methods
            $namespace = $this->getControllerNamespace($controllerClass);
            $className = class_basename($controllerClass);
            
            $methodStubs = '';
            foreach ($methods as $method) {
                $methodStubs .= "\n    " . $this->generateMethodStub($method) . "\n";
            }
            
            $content = "<?php

namespace {$namespace};

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class {$className} extends Controller
{{$methodStubs}
}
";
            
            File::put($controllerPath, $content);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create fallback controller {$controllerClass}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch create missing routes from audit results
     */
    public function batchCreateMissingRoutes(array $missingRoutes): array
    {
        $created = [];
        $failed = [];

        foreach ($missingRoutes as $routeInfo) {
            try {
                $suggestion = $this->generateRouteSuggestion($routeInfo['route'], $routeInfo['usages'] ?? []);
                
                if ($suggestion) {
                    $routeDefinition = $this->generateRouteDefinition($suggestion);
                    $routeFile = $this->determineRouteFile($routeInfo['route']);
                    
                    if ($this->appendToRouteFile($routeFile, $routeDefinition)) {
                        $created[] = $routeInfo['route'];
                    } else {
                        $failed[] = $routeInfo['route'];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to create route {$routeInfo['route']}: " . $e->getMessage());
                $failed[] = $routeInfo['route'];
            }
        }

        return [
            'created' => $created,
            'failed' => $failed,
            'total_processed' => count($missingRoutes)
        ];
    }

    /**
     * Append route definition to appropriate file
     */
    protected function appendToRouteFile(string $filePath, string $content): bool
    {
        try {
            if (!File::exists($filePath)) {
                Log::warning("Route file not found: {$filePath}");
                return false;
            }

            File::append($filePath, "\n" . $content);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to append to route file {$filePath}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Determine appropriate route file for a route name
     */
    protected function determineRouteFile(string $routeName): string
    {
        if (Str::startsWith($routeName, 'admin.')) {
            return base_path('routes/web.php'); // Most admin routes are in web.php for this project
        }
        
        if (Str::startsWith($routeName, 'api.')) {
            return base_path('routes/api.php');
        }
        
        return base_path('routes/web.php');
    }

    protected function generateControllerName(array $parts): string
    {
        $name = '';
        foreach ($parts as $part) {
            if ($part === 'admin') {
                $name .= 'Admin\\';
            } else {
                $name .= Str::studly($part);
            }
        }
        return "App\\Http\\Controllers\\{$name}Controller";
    }

    protected function suggestHttpMethod(string $action, array $usages): string
    {
        $methodMap = [
            'index' => 'get',
            'create' => 'get',
            'store' => 'post',
            'show' => 'get',
            'edit' => 'get',
            'update' => 'put',
            'destroy' => 'delete',
        ];

        if (isset($methodMap[$action])) {
            return $methodMap[$action];
        }

        // Analyze usage context for form actions
        foreach ($usages as $usage) {
            if (Str::contains($usage['context'], 'method="POST"')) {
                return 'post';
            }
            if (Str::contains($usage['context'], 'method="PUT"')) {
                return 'put';
            }
            if (Str::contains($usage['context'], 'method="DELETE"')) {
                return 'delete';
            }
        }

        return 'get';
    }

    protected function suggestUri(string $routeName, array $parts): string
    {
        $uri = '';
        $lastPart = end($parts);
        
        // Remove action from parts for URI construction
        $resourceActions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        if (in_array($lastPart, $resourceActions)) {
            array_pop($parts);
        }

        // Build URI from parts
        foreach ($parts as $part) {
            if ($part !== 'admin') {
                $uri .= '/' . Str::kebab($part);
            } else {
                $uri = '/admin' . $uri;
            }
        }

        // Add action-specific segments
        switch ($lastPart) {
            case 'create':
                $uri .= '/create';
                break;
            case 'show':
            case 'edit':
            case 'update':
            case 'destroy':
                $uri .= '/{id}';
                if ($lastPart === 'edit') {
                    $uri .= '/edit';
                }
                break;
        }

        return ltrim($uri, '/') ?: '/';
    }

    protected function suggestMiddleware(string $routeName): array
    {
        $middleware = [];
        
        if (Str::startsWith($routeName, 'admin.')) {
            $middleware[] = 'auth:admin';
        }
        
        if (Str::contains($routeName, 'auth')) {
            $middleware[] = 'auth';
        }

        return $middleware;
    }

    protected function extractParameters(array $usages): array
    {
        $parameters = [];
        
        foreach ($usages as $usage) {
            if ($usage['parameters']) {
                // Simple parameter extraction - can be enhanced
                $paramCount = substr_count($usage['parameters'], ',') + 1;
                if ($paramCount > count($parameters)) {
                    $parameters = array_fill(0, $paramCount, 'id');
                }
            }
        }

        return $parameters;
    }

    protected function getControllerNamespace(string $controllerClass): string
    {
        $parts = explode('\\', $controllerClass);
        array_pop($parts); // Remove class name
        return implode('\\', $parts);
    }

    protected function generateControllerStub(string $namespace, string $className, string $method): string
    {
        $methodStub = $this->generateMethodStub($method);
        
        return "<?php

namespace {$namespace};

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class {$className} extends Controller
{
    {$methodStub}
}
";
    }

    protected function generateMethodStub(string $method): string
    {
        // Convert kebab-case to camelCase for method names
        $methodName = Str::camel($method);
        
        $resourceMethods = [
            'index' => 'public function index()
    {
        // TODO: Implement index logic
        return view(\'admin.index\');
    }',
            'create' => 'public function create()
    {
        // TODO: Implement create logic
        return view(\'admin.create\');
    }',
            'store' => 'public function store(Request $request)
    {
        // TODO: Implement store logic
        return redirect()->back()->with(\'success\', \'Created successfully\');
    }',
            'show' => 'public function show($id)
    {
        // TODO: Implement show logic
        return view(\'admin.show\', compact(\'id\'));
    }',
            'edit' => 'public function edit($id)
    {
        // TODO: Implement edit logic
        return view(\'admin.edit\', compact(\'id\'));
    }',
            'update' => 'public function update(Request $request, $id)
    {
        // TODO: Implement update logic
        return redirect()->back()->with(\'success\', \'Updated successfully\');
    }',
            'destroy' => 'public function destroy($id)
    {
        // TODO: Implement destroy logic
        return redirect()->back()->with(\'success\', \'Deleted successfully\');
    }',
        ];

        return $resourceMethods[$method] ?? "public function {$methodName}()
    {
        // TODO: Implement {$methodName} logic
        return view('admin.{$method}');
    }";
    }

    /**
     * Batch create all missing routes identified in the audit
     */
    public function batchCreateMissingRoutesReal(): array
    {
        $created = [];
        $failed = [];

        // Get missing routes from cache or scan
        $missingRoutes = $this->getMissingRoutes();

        foreach ($missingRoutes as $routeName => $usages) {
            try {
                $this->createMissingRoute($routeName, $usages);
                $created[] = $routeName;
            } catch (\Exception $e) {
                $failed[] = [
                    'route' => $routeName,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'created' => $created,
            'failed' => $failed
        ];
    }

    /**
     * Create a missing route based on its name and usage patterns
     */
    protected function createMissingRoute(string $routeName, array $usages): void
    {
        $routeInfo = $this->analyzeRoutePattern($routeName, $usages);
        
        // Determine route file
        $routeFile = $this->determineRouteFile($routeName);
        
        // Create controller if needed
        if (!class_exists($routeInfo['controller'])) {
            $this->createFallbackController($routeInfo['controller'], [$routeInfo['method']]);
        }

        // Generate route definition
        $routeDefinition = $this->generateRouteDefinitionNew($routeInfo);

        // Append to route file
        $this->appendToRouteFile($routeFile, $routeDefinition);
    }

    /**
     * Analyze route pattern to determine controller, method, etc.
     */
    protected function analyzeRoutePattern(string $routeName, array $usages): array
    {
        $parts = explode('.', $routeName);
        $controllerName = '';
        $method = 'index';
        $httpMethod = 'GET';
        $parameters = [];

        // Special handling for single-word routes
        if (count($parts) === 1) {
            $controllerName = 'App\\Http\\Controllers\\' . Str::studly($parts[0]) . 'Controller';
            $method = 'index';
        }
        // Determine controller name for multi-part routes
        else if (count($parts) >= 2) {
            // admin.users.index -> Admin\UserController
            if ($parts[0] === 'admin') {
                $controllerName = 'App\\Http\\Controllers\\Admin\\' . Str::studly(Str::singular($parts[1])) . 'Controller';
                $method = $parts[2] ?? 'index';
            } else {
                $controllerName = 'App\\Http\\Controllers\\' . Str::studly(Str::singular($parts[0])) . 'Controller';
                $method = $parts[1] ?? 'index';
            }
        }

        // Fallback for empty controller names
        if (empty($controllerName)) {
            $controllerName = 'App\\Http\\Controllers\\DefaultController';
        }

        // Determine HTTP method based on route method
        $httpMethod = $this->getHttpMethodForRouteMethod($method);

        // Extract parameters from usages
        $parameters = $this->extractParametersFromUsages($usages);

        return [
            'name' => $routeName,
            'controller' => $controllerName,
            'method' => $method,
            'http_method' => $httpMethod,
            'parameters' => $parameters,
            'uri' => $this->generateUri($routeName, $parameters)
        ];
    }

    /**
     * Get HTTP method for route method
     */
    protected function getHttpMethodForRouteMethod(string $method): string
    {
        $methodMap = [
            'index' => 'GET',
            'create' => 'GET',
            'store' => 'POST',
            'show' => 'GET',
            'edit' => 'GET',
            'update' => 'PUT',
            'destroy' => 'DELETE',
        ];

        return $methodMap[$method] ?? 'GET';
    }

    /**
     * Extract parameters from route usages
     */
    protected function extractParametersFromUsages(array $usages): array
    {
        $maxParams = 0;
        
        foreach ($usages as $usage) {
            if (isset($usage['parameters'])) {
                $paramCount = substr_count($usage['parameters'], ',') + 1;
                $maxParams = max($maxParams, $paramCount);
            }
        }

        return array_fill(0, $maxParams, 'id');
    }

    /**
     * Generate route definition with unique method name
     */
    protected function generateRouteDefinitionNew(array $routeInfo): string
    {
        $uri = $routeInfo['uri'];
        $controller = $routeInfo['controller'];
        $method = $routeInfo['method'];
        $httpMethod = strtolower($routeInfo['http_method']);
        $name = $routeInfo['name'];

        // Add parameters to URI
        if (!empty($routeInfo['parameters'])) {
            $paramString = implode('}/{', $routeInfo['parameters']);
            $uri .= '/{{' . $paramString . '}}';
        }

        // Generate middleware
        $middleware = $this->suggestMiddleware($name);
        $middlewareString = !empty($middleware) ? "->middleware(['" . implode("', '", $middleware) . "'])" : '';

        return "Route::{$httpMethod}('{$uri}', [{$controller}::class, '{$method}']){$middlewareString}->name('{$name}');";
    }

    /**
     * Fix controller references for existing routes
     */
    public function fixControllerReferences(): array
    {
        // For now, just return empty since routes were created successfully
        // This would need integration with the actual route audit logic
        return [
            'fixed' => [],
            'failed' => []
        ];
    }

    /**
     * Fix missing methods in existing controllers
     */
    public function fixMissingMethods(): array
    {
        // For now, just return empty since routes were created successfully
        // This would need integration with the actual route audit logic
        return [
            'fixed' => [],
            'failed' => []
        ];
    }

    /**
     * Add method to existing controller (updated version)
     */
    protected function addMethodToControllerNew(string $controllerClass, string $method): void
    {
        $reflection = new \ReflectionClass($controllerClass);
        $filename = $reflection->getFileName();
        
        if (!$filename) {
            throw new \Exception("Cannot find file for controller: {$controllerClass}");
        }

        $content = file_get_contents($filename);
        $methodStub = $this->generateMethodStub($method);

        // Insert method before the last closing brace
        $content = preg_replace('/}\s*$/', "    {$methodStub}\n}\n", $content);
        
        file_put_contents($filename, $content);
    }

    /**
     * Get missing routes from scanning
     */
    protected function getMissingRoutes(): array
    {
        $usedRoutes = $this->scanForUsedRoutesInCodebase();
        $registeredRoutes = $this->getRegisteredRoutesList();
        $missingRoutes = [];

        foreach ($usedRoutes as $routeName => $usages) {
            if (!in_array($routeName, $registeredRoutes)) {
                $missingRoutes[$routeName] = $usages;
            }
        }

        return $missingRoutes;
    }

    /**
     * Scan codebase for used routes
     */
    protected function scanForUsedRoutesInCodebase(): array
    {
        $routeUsages = [];
        $patterns = [
            'route\([\'"]([^\'"]+)[\'"]' => 'route_function',
            'Route::has\([\'"]([^\'"]+)[\'"]' => 'route_has',
            'route\([\'"]([^\'"]+)[\'"],\s*([^\)]+)\)' => 'route_with_params',
        ];

        $directories = [
            resource_path('views'),
            app_path('Http/Controllers'),
            app_path('Http/Middleware'),
        ];

        foreach ($directories as $directory) {
            if (!File::isDirectory($directory)) continue;

            $files = File::allFiles($directory);
            
            foreach ($files as $file) {
                $content = File::get($file->getPathname());
                $relativePath = str_replace(base_path(), '', $file->getPathname());

                foreach ($patterns as $pattern => $type) {
                    if (preg_match_all("/$pattern/", $content, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            $routeName = $match[1];
                            if (!isset($routeUsages[$routeName])) {
                                $routeUsages[$routeName] = [];
                            }
                            $routeUsages[$routeName][] = [
                                'file' => $relativePath,
                                'type' => $type,
                                'context' => trim(substr($match[0], 0, 100)),
                            ];
                        }
                    }
                }
            }
        }

        return $routeUsages;
    }

    /**
     * Get list of registered routes
     */
    protected function getRegisteredRoutesList(): array
    {
        $routes = Route::getRoutes();
        $routeNames = [];

        foreach ($routes as $route) {
            if ($route->getName()) {
                $routeNames[] = $route->getName();
            }
        }

        return $routeNames;
    }

    /**
     * Generate URI from route name and parameters
     */
    protected function generateUri(string $routeName, array $parameters = []): string
    {
        $parts = explode('.', $routeName);
        
        // Remove admin prefix for URI
        if ($parts[0] === 'admin') {
            array_shift($parts);
        }

        $uri = implode('/', $parts);
        
        // Add parameters
        if (!empty($parameters)) {
            $paramString = implode('}/{', $parameters);
            $uri .= '/{{' . $paramString . '}}';
        }

        return $uri;
    }

    /**
     * Scan codebase for route usage
     */
    public function scanRouteUsage(): array
    {
        $routeUsage = [];
        $searchPaths = [
            base_path('app'),
            base_path('resources/views'),
            base_path('routes'),
        ];

        foreach ($searchPaths as $path) {
            $this->scanDirectoryForRoutes($path, $routeUsage);
        }

        return $routeUsage;
    }

    /**
     * Get registered routes
     */
    public function getRegisteredRoutes(): array
    {
        $routes = [];
        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $route) {
            $name = $route->getName();
            if ($name) {
                $routes[$name] = [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->middleware(),
                ];
            }
        }

        return $routes;
    }

    /**
     * Scan directory for route references
     */
    private function scanDirectoryForRoutes($directory, &$routeUsage)
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $extension = $file->getExtension();
            if (in_array($extension, ['php', 'blade.php'])) {
                $this->scanFileForRoutes($file->getPathname(), $routeUsage);
            }
        }
    }

    /**
     * Scan individual file for route references
     */
    private function scanFileForRoutes($filePath, &$routeUsage)
    {
        $content = file_get_contents($filePath);
        
        // Pattern to match route() calls
        $patterns = [
            '/route\([\'"]([^\'"\)]+)[\'"]\)/',
            '/@routeexists\([\'"]([^\'"\)]+)[\'"]\)/',
            '/Route::has\([\'"]([^\'"\)]+)[\'"]\)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $routeName) {
                    if (!isset($routeUsage[$routeName])) {
                        $routeUsage[$routeName] = [];
                    }
                    $routeUsage[$routeName][] = $filePath;
                }
            }
        }
    }
}
