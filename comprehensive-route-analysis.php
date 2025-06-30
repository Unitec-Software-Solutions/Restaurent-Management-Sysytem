<?php

/**
 * Comprehensive Route and Controller Analysis Script
 * Scans all routes and controllers to identify issues and provide fixes
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class RouteAnalyzer
{
    private array $issues = [];
    private array $fixes = [];
    private array $routeMap = [];
    private array $controllerMethods = [];
    
    public function analyze(): array
    {
        echo "🔍 Starting Comprehensive Route Analysis...\n\n";
        
        // 1. Load all routes
        $this->loadRoutes();
        
        // 2. Scan all controllers
        $this->scanControllers();
        
        // 3. Analyze route-controller mapping
        $this->analyzeRouteControllerMapping();
        
        // 4. Check for common issues
        $this->checkCommonIssues();
        
        // 5. Generate fixes
        $this->generateFixes();
        
        return [
            'issues' => $this->issues,
            'fixes' => $this->fixes,
            'routeMap' => $this->routeMap,
            'controllerMethods' => $this->controllerMethods
        ];
    }
    
    private function loadRoutes(): void
    {
        echo "📋 Loading routes...\n";
        
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            $action = $route->getAction();
            $uri = $route->uri();
            $methods = $route->methods();
            $name = $route->getName();
            
            $this->routeMap[] = [
                'uri' => $uri,
                'methods' => $methods,
                'name' => $name,
                'action' => $action,
                'controller' => $action['controller'] ?? null,
                'middleware' => $action['middleware'] ?? [],
                'parameters' => $route->parameterNames(),
                'compiled' => $route->getCompiled(),
            ];
        }
        
        echo "   Found " . count($this->routeMap) . " routes\n\n";
    }
    
    private function scanControllers(): void
    {
        echo "🎯 Scanning controllers...\n";
        
        $controllerPath = app_path('Http/Controllers');
        $files = $this->getPhpFiles($controllerPath);
        
        foreach ($files as $file) {
            $this->analyzeControllerFile($file);
        }
        
        echo "   Analyzed " . count($this->controllerMethods) . " controller classes\n\n";
    }
    
    private function getPhpFiles(string $directory): array
    {
        $files = [];
        
        if (!is_dir($directory)) {
            return $files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }
        
        return $files;
    }
    
    private function analyzeControllerFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        
        // Extract namespace and class name
        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch) &&
            preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            
            $namespace = $namespaceMatch[1];
            $className = $classMatch[1];
            $fullClassName = $namespace . '\\' . $className;
            
            try {
                if (class_exists($fullClassName)) {
                    $reflection = new ReflectionClass($fullClassName);
                    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                    
                    $this->controllerMethods[$fullClassName] = [];
                    
                    foreach ($methods as $method) {
                        if ($method->getDeclaringClass()->getName() === $fullClassName) {
                            $this->controllerMethods[$fullClassName][] = [
                                'name' => $method->getName(),
                                'parameters' => $this->getMethodParameters($method),
                                'isStatic' => $method->isStatic(),
                                'isConstructor' => $method->isConstructor(),
                            ];
                        }
                    }
                }
            } catch (Exception $e) {
                $this->issues[] = [
                    'type' => 'controller_load_error',
                    'message' => "Failed to load controller: $fullClassName",
                    'details' => $e->getMessage(),
                    'file' => $filePath
                ];
            }
        }
    }
    
    private function getMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $parameters[] = [
                'name' => $param->getName(),
                'type' => $param->getType() ? $param->getType()->getName() : null,
                'isOptional' => $param->isOptional(),
                'hasDefault' => $param->isDefaultValueAvailable(),
                'defaultValue' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
            ];
        }
        
        return $parameters;
    }
    
    private function analyzeRouteControllerMapping(): void
    {
        echo "🔗 Analyzing route-controller mapping...\n";
        
        foreach ($this->routeMap as $index => $route) {
            if (!$route['controller']) {
                continue;
            }
            
            $this->analyzeRoute($route, $index);
        }
        
        echo "   Route-controller mapping analysis complete\n\n";
    }
    
    private function analyzeRoute(array $route, int $index): void
    {
        $controller = $route['controller'];
        
        if (is_string($controller)) {
            // Handle string controller format like "ControllerClass@method"
            if (strpos($controller, '@') !== false) {
                [$className, $methodName] = explode('@', $controller);
            } else {
                $this->issues[] = [
                    'type' => 'invalid_controller_format',
                    'message' => "Invalid controller format: $controller",
                    'route' => $route['uri'],
                    'index' => $index
                ];
                return;
            }
        } elseif (is_array($controller) && count($controller) === 2) {
            // Handle array format like [ControllerClass::class, 'method']
            [$className, $methodName] = $controller;
        } else {
            $this->issues[] = [
                'type' => 'unknown_controller_format',
                'message' => "Unknown controller format",
                'route' => $route['uri'],
                'controller' => $controller,
                'index' => $index
            ];
            return;
        }
        
        // Check if controller class exists
        if (!isset($this->controllerMethods[$className])) {
            $this->issues[] = [
                'type' => 'missing_controller',
                'message' => "Controller class not found: $className",
                'route' => $route['uri'],
                'controller' => $className,
                'index' => $index
            ];
            return;
        }
        
        // Check if method exists
        $methods = array_column($this->controllerMethods[$className], 'name');
        if (!in_array($methodName, $methods)) {
            $this->issues[] = [
                'type' => 'missing_method',
                'message' => "Method $methodName not found in controller $className",
                'route' => $route['uri'],
                'controller' => $className,
                'method' => $methodName,
                'availableMethods' => $methods,
                'index' => $index
            ];
        }
        
        // Check parameter compatibility
        $this->checkParameterCompatibility($route, $className, $methodName, $index);
    }
    
    private function checkParameterCompatibility(array $route, string $className, string $methodName, int $index): void
    {
        $routeParams = $route['parameters'];
        $controllerMethods = $this->controllerMethods[$className];
        
        $method = null;
        foreach ($controllerMethods as $m) {
            if ($m['name'] === $methodName) {
                $method = $m;
                break;
            }
        }
        
        if (!$method) {
            return;
        }
        
        $methodParams = $method['parameters'];
        
        // Filter out Request and other framework parameters
        $domainParams = array_filter($methodParams, function($param) {
            return !in_array($param['type'], [
                'Illuminate\Http\Request',
                'Illuminate\Http\JsonResponse',
                'Illuminate\View\View',
                'Illuminate\Http\RedirectResponse',
                null
            ]);
        });
        
        $requiredParams = array_filter($domainParams, function($param) {
            return !$param['isOptional'];
        });
        
        if (count($routeParams) < count($requiredParams)) {
            $this->issues[] = [
                'type' => 'parameter_mismatch',
                'message' => "Route has fewer parameters than required by controller method",
                'route' => $route['uri'],
                'controller' => $className,
                'method' => $methodName,
                'routeParams' => $routeParams,
                'requiredParams' => array_column($requiredParams, 'name'),
                'index' => $index
            ];
        }
    }
    
    private function checkCommonIssues(): void
    {
        echo "⚠️  Checking for common issues...\n";
        
        $this->checkDuplicateRoutes();
        $this->checkMissingRouteNames();
        $this->checkMissingMiddleware();
        $this->checkDeprecatedSyntax();
        $this->checkResourceRoutes();
        
        echo "   Common issues check complete\n\n";
    }
    
    private function checkDuplicateRoutes(): void
    {
        $routeSignatures = [];
        
        foreach ($this->routeMap as $index => $route) {
            $signature = implode('|', $route['methods']) . ':' . $route['uri'];
            
            if (isset($routeSignatures[$signature])) {
                $this->issues[] = [
                    'type' => 'duplicate_route',
                    'message' => "Duplicate route found",
                    'route' => $route['uri'],
                    'methods' => $route['methods'],
                    'duplicateIndex' => $routeSignatures[$signature],
                    'currentIndex' => $index
                ];
            } else {
                $routeSignatures[$signature] = $index;
            }
        }
    }
    
    private function checkMissingRouteNames(): void
    {
        foreach ($this->routeMap as $index => $route) {
            if (!$route['name'] && $route['controller']) {
                $this->issues[] = [
                    'type' => 'missing_route_name',
                    'message' => "Route missing name",
                    'route' => $route['uri'],
                    'methods' => $route['methods'],
                    'index' => $index
                ];
            }
        }
    }
    
    private function checkMissingMiddleware(): void
    {
        $adminRoutes = array_filter($this->routeMap, function($route) {
            return strpos($route['uri'], 'admin/') === 0;
        });
        
        foreach ($adminRoutes as $index => $route) {
            $middleware = $route['middleware'];
            
            if (!in_array('auth:admin', $middleware) && !in_array('auth', $middleware)) {
                $this->issues[] = [
                    'type' => 'missing_auth_middleware',
                    'message' => "Admin route missing auth middleware",
                    'route' => $route['uri'],
                    'middleware' => $middleware,
                    'index' => $index
                ];
            }
        }
    }
    
    private function checkDeprecatedSyntax(): void
    {
        foreach ($this->routeMap as $index => $route) {
            $controller = $route['controller'];
            
            if (is_string($controller) && strpos($controller, '@') !== false) {
                $this->issues[] = [
                    'type' => 'deprecated_syntax',
                    'message' => "Using deprecated Controller@method syntax",
                    'route' => $route['uri'],
                    'controller' => $controller,
                    'recommendation' => "Use [Controller::class, 'method'] syntax instead",
                    'index' => $index
                ];
            }
        }
    }
    
    private function checkResourceRoutes(): void
    {
        $resourceControllers = [];
        
        foreach ($this->routeMap as $route) {
            if ($route['controller'] && is_array($route['controller'])) {
                $className = $route['controller'][0];
                $methodName = $route['controller'][1];
                
                if (in_array($methodName, ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])) {
                    if (!isset($resourceControllers[$className])) {
                        $resourceControllers[$className] = [];
                    }
                    $resourceControllers[$className][] = $methodName;
                }
            }
        }
        
        foreach ($resourceControllers as $controller => $methods) {
            $standardMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
            $missingMethods = array_diff($standardMethods, $methods);
            
            if (count($methods) >= 4 && !empty($missingMethods)) {
                $this->issues[] = [
                    'type' => 'incomplete_resource',
                    'message' => "Controller appears to be a resource but missing standard methods",
                    'controller' => $controller,
                    'presentMethods' => $methods,
                    'missingMethods' => $missingMethods,
                    'recommendation' => "Consider using Route::resource() or add missing methods"
                ];
            }
        }
    }
    
    private function generateFixes(): void
    {
        echo "🔧 Generating fixes...\n";
        
        foreach ($this->issues as $issue) {
            $fix = $this->generateFixForIssue($issue);
            if ($fix) {
                $this->fixes[] = $fix;
            }
        }
        
        echo "   Generated " . count($this->fixes) . " fixes\n\n";
    }
    
    private function generateFixForIssue(array $issue): ?array
    {
        switch ($issue['type']) {
            case 'missing_method':
                return $this->generateMissingMethodFix($issue);
            case 'missing_route_name':
                return $this->generateMissingRouteNameFix($issue);
            case 'deprecated_syntax':
                return $this->generateDeprecatedSyntaxFix($issue);
            case 'missing_auth_middleware':
                return $this->generateMissingMiddlewareFix($issue);
            case 'incomplete_resource':
                return $this->generateIncompleteResourceFix($issue);
            default:
                return null;
        }
    }
    
    private function generateMissingMethodFix(array $issue): array
    {
        $methodTemplate = $this->generateMethodTemplate($issue['method']);
        
        return [
            'type' => 'add_controller_method',
            'controller' => $issue['controller'],
            'method' => $issue['method'],
            'template' => $methodTemplate,
            'description' => "Add missing method {$issue['method']} to {$issue['controller']}"
        ];
    }
    
    private function generateMethodTemplate(string $methodName): string
    {
        $templates = [
            'index' => '
    public function index()
    {
        // TODO: Implement index method
        return view(\'admin.{resource}.index\');
    }',
            'create' => '
    public function create()
    {
        // TODO: Implement create method
        return view(\'admin.{resource}.create\');
    }',
            'store' => '
    public function store(Request $request)
    {
        // TODO: Implement store method
        // $request->validate([]);
        return redirect()->route(\'admin.{resource}.index\');
    }',
            'show' => '
    public function show($id)
    {
        // TODO: Implement show method
        return view(\'admin.{resource}.show\', compact(\'id\'));
    }',
            'edit' => '
    public function edit($id)
    {
        // TODO: Implement edit method
        return view(\'admin.{resource}.edit\', compact(\'id\'));
    }',
            'update' => '
    public function update(Request $request, $id)
    {
        // TODO: Implement update method
        // $request->validate([]);
        return redirect()->route(\'admin.{resource}.index\');
    }',
            'destroy' => '
    public function destroy($id)
    {
        // TODO: Implement destroy method
        return redirect()->route(\'admin.{resource}.index\');
    }'
        ];
        
        return $templates[$methodName] ?? "
    public function $methodName()
    {
        // TODO: Implement $methodName method
    }";
    }
    
    private function generateMissingRouteNameFix(array $issue): array
    {
        $suggestedName = $this->generateRouteName($issue['route'], $issue['methods']);
        
        return [
            'type' => 'add_route_name',
            'route' => $issue['route'],
            'suggestedName' => $suggestedName,
            'description' => "Add route name: {$suggestedName}"
        ];
    }
    
    private function generateRouteName(string $uri, array $methods): string
    {
        $parts = explode('/', trim($uri, '/'));
        $name = implode('.', array_filter($parts, function($part) {
            return !preg_match('/^{.*}$/', $part); // Remove parameter placeholders
        }));
        
        $method = strtolower($methods[0]);
        
        if ($method === 'post' && !str_ends_with($name, '.store')) {
            $name .= '.store';
        } elseif ($method === 'put' && !str_ends_with($name, '.update')) {
            $name .= '.update';
        } elseif ($method === 'delete' && !str_ends_with($name, '.destroy')) {
            $name .= '.destroy';
        }
        
        return $name;
    }
    
    private function generateDeprecatedSyntaxFix(array $issue): array
    {
        $controller = $issue['controller'];
        [$className, $methodName] = explode('@', $controller);
        
        return [
            'type' => 'update_controller_syntax',
            'oldSyntax' => $controller,
            'newSyntax' => "[$className::class, '$methodName']",
            'description' => "Update deprecated syntax to modern array format"
        ];
    }
    
    private function generateMissingMiddlewareFix(array $issue): array
    {
        return [
            'type' => 'add_middleware',
            'route' => $issue['route'],
            'middleware' => 'auth:admin',
            'description' => "Add auth:admin middleware to admin route"
        ];
    }
    
    private function generateIncompleteResourceFix(array $issue): array
    {
        return [
            'type' => 'complete_resource',
            'controller' => $issue['controller'],
            'missingMethods' => $issue['missingMethods'],
            'description' => "Add missing resource methods or convert to Route::resource()"
        ];
    }
    
    public function generateReport(): string
    {
        $report = "# Comprehensive Route Analysis Report\n\n";
        $report .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        $report .= "## Summary\n";
        $report .= "- Total Routes: " . count($this->routeMap) . "\n";
        $report .= "- Total Controllers: " . count($this->controllerMethods) . "\n";
        $report .= "- Issues Found: " . count($this->issues) . "\n";
        $report .= "- Fixes Generated: " . count($this->fixes) . "\n\n";
        
        $report .= "## Issues by Type\n";
        $issueTypes = array_count_values(array_column($this->issues, 'type'));
        foreach ($issueTypes as $type => $count) {
            $report .= "- $type: $count\n";
        }
        $report .= "\n";
        
        $report .= "## Critical Issues\n";
        $criticalIssues = array_filter($this->issues, function($issue) {
            return in_array($issue['type'], ['missing_controller', 'missing_method', 'missing_auth_middleware']);
        });
        
        foreach ($criticalIssues as $issue) {
            $report .= "### {$issue['type']}\n";
            $report .= "- Message: {$issue['message']}\n";
            if (isset($issue['route'])) {
                $report .= "- Route: {$issue['route']}\n";
            }
            if (isset($issue['controller'])) {
                $report .= "- Controller: {$issue['controller']}\n";
            }
            $report .= "\n";
        }
        
        return $report;
    }
}

// Run the analysis
$analyzer = new RouteAnalyzer();
$results = $analyzer->analyze();

// Generate and display report
echo $analyzer->generateReport();

// Save detailed results
file_put_contents('route-analysis-results.json', json_encode($results, JSON_PRETTY_PRINT));
echo "📊 Detailed results saved to route-analysis-results.json\n";

echo "✅ Route analysis complete!\n";
