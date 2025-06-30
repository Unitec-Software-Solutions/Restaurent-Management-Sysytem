<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

echo "🔍 COMPREHENSIVE ROUTE AND CONTROLLER AUDIT\n";
echo "============================================\n\n";

class RouteAuditor
{
    private $issues = [];
    private $fixes = [];
    private $registeredRoutes = [];
    private $controllers = [];
    
    public function __construct()
    {
        $this->collectRegisteredRoutes();
        $this->scanControllers();
    }
    
    public function audit()
    {
        echo "1. ANALYZING ROUTE TO CONTROLLER MAPPING\n";
        echo "========================================\n";
        $this->analyzeRouteControllerMapping();
        
        echo "\n2. CHECKING CONTROLLER METHODS\n";
        echo "===============================\n";
        $this->checkControllerMethods();
        
        echo "\n3. IDENTIFYING PARAMETER MISMATCHES\n";
        echo "===================================\n";
        $this->checkParameterMismatches();
        
        echo "\n4. FINDING DUPLICATE ROUTES\n";
        echo "===========================\n";
        $this->findDuplicateRoutes();
        
        echo "\n5. CHECKING MIDDLEWARE REQUIREMENTS\n";
        echo "===================================\n";
        $this->checkMiddlewareRequirements();
        
        echo "\n6. VALIDATING RESOURCE ROUTES\n";
        echo "=============================\n";
        $this->validateResourceRoutes();
        
        echo "\n7. SCANNING FOR MISSING ROUTES\n";
        echo "==============================\n";
        $this->scanForMissingRoutes();
        
        $this->generateReport();
        $this->generateFixes();
    }
    
    private function collectRegisteredRoutes()
    {
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            $this->registeredRoutes[] = [
                'name' => $route->getName(),
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $route->gatherMiddleware(),
                'parameters' => $route->parameterNames(),
                'controller' => $this->extractController($route->getActionName()),
                'method' => $this->extractMethod($route->getActionName()),
            ];
        }
        
        echo "Found " . count($this->registeredRoutes) . " registered routes\n";
    }
    
    private function scanControllers()
    {
        $controllerDirs = [
            app_path('Http/Controllers'),
            app_path('Http/Controllers/Admin'),
            app_path('Http/Controllers/Guest'),
        ];
        
        foreach ($controllerDirs as $dir) {
            if (File::exists($dir)) {
                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $this->analyzeController($file);
                    }
                }
            }
        }
        
        echo "Scanned " . count($this->controllers) . " controller files\n";
    }
    
    private function analyzeController($file)
    {
        $content = File::get($file);
        
        // Extract namespace and class name
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches);
        preg_match('/class\s+(\w+)/', $content, $classMatches);
        
        if (!empty($namespaceMatches[1]) && !empty($classMatches[1])) {
            $fullClassName = $namespaceMatches[1] . '\\' . $classMatches[1];
            
            $methods = [];
            preg_match_all('/public\s+function\s+(\w+)\s*\(([^)]*)\)/', $content, $methodMatches, PREG_SET_ORDER);
            
            foreach ($methodMatches as $match) {
                $methods[] = [
                    'name' => $match[1],
                    'parameters' => $this->parseMethodParameters($match[2]),
                ];
            }
            
            $this->controllers[$fullClassName] = [
                'file' => $file->getRealPath(),
                'methods' => $methods,
                'exists' => true,
            ];
        }
    }
    
    private function parseMethodParameters($paramString)
    {
        $params = [];
        if (trim($paramString)) {
            $rawParams = explode(',', $paramString);
            foreach ($rawParams as $param) {
                $param = trim($param);
                if (preg_match('/\$(\w+)/', $param, $matches)) {
                    $params[] = $matches[1];
                }
            }
        }
        return $params;
    }
    
    private function extractController($action)
    {
        if ($action === 'Closure') {
            return 'Closure';
        }
        
        if (strpos($action, '@') !== false) {
            return explode('@', $action)[0];
        }
        
        if (strpos($action, '::') !== false) {
            return explode('::', $action)[0];
        }
        
        return $action;
    }
    
    private function extractMethod($action)
    {
        if ($action === 'Closure') {
            return 'Closure';
        }
        
        if (strpos($action, '@') !== false) {
            return explode('@', $action)[1];
        }
        
        if (strpos($action, '::') !== false) {
            return explode('::', $action)[1];
        }
        
        return 'index';
    }
    
    private function analyzeRouteControllerMapping()
    {
        $mappingIssues = 0;
        
        foreach ($this->registeredRoutes as $route) {
            if ($route['controller'] === 'Closure') {
                continue;
            }
            
            // Check if controller exists
            if (!isset($this->controllers[$route['controller']])) {
                $this->addIssue('missing_controller', $route, 'high', 
                    "Controller '{$route['controller']}' not found");
                $mappingIssues++;
                echo "❌ Missing Controller: {$route['controller']} for route {$route['name']}\n";
            } else {
                echo "✅ Controller exists: {$route['controller']}\n";
            }
        }
        
        echo "\nMapping issues found: $mappingIssues\n";
    }
    
    private function checkControllerMethods()
    {
        $methodIssues = 0;
        
        foreach ($this->registeredRoutes as $route) {
            if ($route['controller'] === 'Closure') {
                continue;
            }
            
            if (isset($this->controllers[$route['controller']])) {
                $controllerMethods = array_column($this->controllers[$route['controller']]['methods'], 'name');
                
                if (!in_array($route['method'], $controllerMethods)) {
                    $this->addIssue('missing_method', $route, 'high',
                        "Method '{$route['method']}' not found in controller '{$route['controller']}'");
                    $methodIssues++;
                    echo "❌ Missing Method: {$route['controller']}@{$route['method']} for route {$route['name']}\n";
                } else {
                    echo "✅ Method exists: {$route['controller']}@{$route['method']}\n";
                }
            }
        }
        
        echo "\nMethod issues found: $methodIssues\n";
    }
    
    private function checkParameterMismatches()
    {
        $parameterIssues = 0;
        
        foreach ($this->registeredRoutes as $route) {
            if ($route['controller'] === 'Closure' || !isset($this->controllers[$route['controller']])) {
                continue;
            }
            
            $controllerMethods = $this->controllers[$route['controller']]['methods'];
            $method = array_filter($controllerMethods, function($m) use ($route) {
                return $m['name'] === $route['method'];
            });
            
            if (!empty($method)) {
                $method = array_values($method)[0];
                $routeParams = $route['parameters'];
                $methodParams = array_filter($method['parameters'], function($p) {
                    return !in_array($p, ['request', 'Request']);
                });
                
                if (count($routeParams) !== count($methodParams)) {
                    $this->addIssue('parameter_mismatch', $route, 'medium',
                        "Parameter count mismatch: Route has " . count($routeParams) . 
                        " parameters, method has " . count($methodParams));
                    $parameterIssues++;
                    echo "❌ Parameter mismatch: {$route['name']} - Route: [" . 
                         implode(', ', $routeParams) . "] vs Method: [" . 
                         implode(', ', $methodParams) . "]\n";
                }
            }
        }
        
        echo "\nParameter issues found: $parameterIssues\n";
    }
    
    private function findDuplicateRoutes()
    {
        $uriMap = [];
        $duplicates = 0;
        
        foreach ($this->registeredRoutes as $route) {
            $key = implode('|', $route['methods']) . ':' . $route['uri'];
            
            if (isset($uriMap[$key])) {
                $this->addIssue('duplicate_route', $route, 'medium',
                    "Duplicate route definition for URI: {$route['uri']}");
                $duplicates++;
                echo "❌ Duplicate route: {$route['uri']} ({$route['name']}) duplicates {$uriMap[$key]}\n";
            } else {
                $uriMap[$key] = $route['name'];
            }
        }
        
        echo "\nDuplicate routes found: $duplicates\n";
    }
    
    private function checkMiddlewareRequirements()
    {
        $middlewareIssues = 0;
        
        $requiredMiddleware = [
            'admin' => ['auth:admin'],
            'guest' => [],
            'api' => ['auth:admin'],
        ];
        
        foreach ($this->registeredRoutes as $route) {
            if (strpos($route['uri'], 'admin') !== false && 
                !in_array('auth:admin', $route['middleware']) &&
                !strpos($route['uri'], 'admin/login') &&
                !strpos($route['uri'], 'admin/auth')) {
                
                $this->addIssue('missing_middleware', $route, 'high',
                    "Admin route missing 'auth:admin' middleware");
                $middlewareIssues++;
                echo "❌ Missing middleware: {$route['name']} needs 'auth:admin'\n";
            }
        }
        
        echo "\nMiddleware issues found: $middlewareIssues\n";
    }
    
    private function validateResourceRoutes()
    {
        $resourceIssues = 0;
        $standardMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        
        // Group routes by resource prefix
        $resources = [];
        foreach ($this->registeredRoutes as $route) {
            if ($route['name'] && preg_match('/^(.+)\.(\w+)$/', $route['name'], $matches)) {
                $resourceName = $matches[1];
                $method = $matches[2];
                
                if (!isset($resources[$resourceName])) {
                    $resources[$resourceName] = [];
                }
                $resources[$resourceName][] = $method;
            }
        }
        
        foreach ($resources as $resourceName => $methods) {
            $missing = array_diff($standardMethods, $methods);
            if (!empty($missing) && count($methods) > 2) { // Only check if it seems like a resource
                $this->addIssue('incomplete_resource', null, 'low',
                    "Resource '$resourceName' missing methods: " . implode(', ', $missing));
                $resourceIssues++;
                echo "⚠️  Incomplete resource: $resourceName missing " . implode(', ', $missing) . "\n";
            }
        }
        
        echo "\nResource issues found: $resourceIssues\n";
    }
    
    private function scanForMissingRoutes()
    {
        // Scan blade files for route() calls
        $viewFiles = File::allFiles(resource_path('views'));
        $routeReferences = [];
        
        foreach ($viewFiles as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file);
                preg_match_all('/route\([\'"]([^\'"]+)[\'"]/', $content, $matches);
                foreach ($matches[1] as $routeName) {
                    $routeReferences[] = $routeName;
                }
            }
        }
        
        $registeredRouteNames = array_filter(array_column($this->registeredRoutes, 'name'));
        $missingRoutes = array_diff(array_unique($routeReferences), $registeredRouteNames);
        
        echo "Found " . count($missingRoutes) . " potentially missing routes:\n";
        foreach ($missingRoutes as $route) {
            echo "❌ Missing route referenced in views: $route\n";
            $this->addIssue('missing_route', null, 'medium', "Route '$route' referenced in views but not defined");
        }
    }
    
    private function addIssue($type, $route, $severity, $message)
    {
        $this->issues[] = [
            'type' => $type,
            'route' => $route,
            'severity' => $severity,
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
    
    private function generateReport()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "AUDIT SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        
        $severityCounts = array_count_values(array_column($this->issues, 'severity'));
        $typeCounts = array_count_values(array_column($this->issues, 'type'));
        
        echo "Total Issues: " . count($this->issues) . "\n";
        echo "High Priority: " . ($severityCounts['high'] ?? 0) . "\n";
        echo "Medium Priority: " . ($severityCounts['medium'] ?? 0) . "\n";
        echo "Low Priority: " . ($severityCounts['low'] ?? 0) . "\n\n";
        
        echo "Issue Breakdown:\n";
        foreach ($typeCounts as $type => $count) {
            echo "- " . str_replace('_', ' ', ucwords($type)) . ": $count\n";
        }
        
        // Save detailed report
        $reportData = [
            'timestamp' => now()->toDateTimeString(),
            'summary' => [
                'total_issues' => count($this->issues),
                'severity_breakdown' => $severityCounts,
                'type_breakdown' => $typeCounts,
            ],
            'issues' => $this->issues,
            'registered_routes' => $this->registeredRoutes,
            'controllers' => array_keys($this->controllers),
        ];
        
        File::put('route-audit-report.json', json_encode($reportData, JSON_PRETTY_PRINT));
        echo "\nDetailed report saved to: route-audit-report.json\n";
    }
    
    private function generateFixes()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "GENERATING FIXES\n";
        echo str_repeat("=", 50) . "\n";
        
        $this->generateMissingControllers();
        $this->generateMissingMethods();
        $this->generateRouteCleanup();
        $this->generateTestFiles();
    }
    
    private function generateMissingControllers()
    {
        $missingControllers = array_filter($this->issues, function($issue) {
            return $issue['type'] === 'missing_controller';
        });
        
        foreach ($missingControllers as $issue) {
            $controllerClass = $issue['route']['controller'];
            $this->createMissingController($controllerClass);
        }
    }
    
    private function createMissingController($controllerClass)
    {
        $relativePath = str_replace('App\\Http\\Controllers\\', '', $controllerClass);
        $relativePath = str_replace('\\', '/', $relativePath);
        $controllerPath = app_path("Http/Controllers/{$relativePath}.php");
        
        // Create directory if it doesn't exist
        $directory = dirname($controllerPath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        $namespace = $this->getControllerNamespace($controllerClass);
        $className = class_basename($controllerClass);
        
        $template = $this->getControllerTemplate($namespace, $className);
        
        if (!File::exists($controllerPath)) {
            File::put($controllerPath, $template);
            echo "✅ Created controller: $controllerPath\n";
        }
    }
    
    private function getControllerNamespace($controllerClass)
    {
        $parts = explode('\\', $controllerClass);
        array_pop($parts);
        return implode('\\', $parts);
    }
    
    private function getControllerTemplate($namespace, $className)
    {
        return "<?php

namespace {$namespace};

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class {$className} extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Implement index logic
        return view('admin.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO: Implement create logic
        return view('admin.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request \$request)
    {
        // TODO: Implement store logic
        return redirect()->back()->with('success', 'Created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(\$id)
    {
        // TODO: Implement show logic
        return view('admin.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\$id)
    {
        // TODO: Implement edit logic
        return view('admin.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request \$request, \$id)
    {
        // TODO: Implement update logic
        return redirect()->back()->with('success', 'Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\$id)
    {
        // TODO: Implement destroy logic
        return redirect()->back()->with('success', 'Deleted successfully');
    }
}
";
    }
    
    private function generateMissingMethods()
    {
        $missingMethods = array_filter($this->issues, function($issue) {
            return $issue['type'] === 'missing_method';
        });
        
        $methodsByController = [];
        foreach ($missingMethods as $issue) {
            $controller = $issue['route']['controller'];
            $method = $issue['route']['method'];
            
            if (!isset($methodsByController[$controller])) {
                $methodsByController[$controller] = [];
            }
            $methodsByController[$controller][] = $method;
        }
        
        foreach ($methodsByController as $controller => $methods) {
            $this->addMethodsToController($controller, $methods);
        }
    }
    
    private function addMethodsToController($controllerClass, $methods)
    {
        if (!isset($this->controllers[$controllerClass])) {
            return;
        }
        
        $controllerFile = $this->controllers[$controllerClass]['file'];
        $content = File::get($controllerFile);
        
        $methodStubs = '';
        foreach ($methods as $method) {
            $methodStub = $this->generateMethodStub($method);
            $methodStubs .= "\n    " . $methodStub . "\n";
        }
        
        // Insert methods before the last closing brace
        $lastBracePos = strrpos($content, '}');
        if ($lastBracePos !== false) {
            $newContent = substr($content, 0, $lastBracePos) . $methodStubs . "\n}";
            File::put($controllerFile, $newContent);
            echo "✅ Added methods to controller: $controllerClass\n";
        }
    }
    
    private function generateMethodStub($methodName)
    {
        $commonMethods = [
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
        
        return $commonMethods[$methodName] ?? "public function {$methodName}()
    {
        // TODO: Implement {$methodName} logic
        return view('admin.{$methodName}');
    }";
    }
    
    private function generateRouteCleanup()
    {
        echo "\n🧹 ROUTE CLEANUP RECOMMENDATIONS\n";
        echo "================================\n";
        
        // Generate cleaned up routes file
        $cleanRoutes = $this->generateCleanRoutesFile();
        File::put('routes/web-cleaned.php', $cleanRoutes);
        echo "✅ Generated cleaned routes file: routes/web-cleaned.php\n";
    }
    
    private function generateCleanRoutesFile()
    {
        $routesByPrefix = [];
        
        foreach ($this->registeredRoutes as $route) {
            $prefix = $this->extractRoutePrefix($route['uri']);
            if (!isset($routesByPrefix[$prefix])) {
                $routesByPrefix[$prefix] = [];
            }
            $routesByPrefix[$prefix][] = $route;
        }
        
        $output = "<?php\n\nuse Illuminate\Support\Facades\Route;\n";
        $output .= "// Auto-generated cleaned routes file\n";
        $output .= "// Generated on: " . now()->toDateTimeString() . "\n\n";
        
        foreach ($routesByPrefix as $prefix => $routes) {
            $output .= $this->generateRouteGroup($prefix, $routes);
        }
        
        return $output;
    }
    
    private function extractRoutePrefix($uri)
    {
        $parts = explode('/', $uri);
        return $parts[0] ?: 'root';
    }
    
    private function generateRouteGroup($prefix, $routes)
    {
        $output = "\n/*\n | {$prefix} Routes\n */\n";
        
        if ($prefix === 'admin') {
            $output .= "Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {\n";
        } elseif ($prefix === 'guest') {
            $output .= "Route::prefix('guest')->name('guest.')->group(function () {\n";
        } else {
            $output .= "// {$prefix} routes\n";
        }
        
        foreach ($routes as $route) {
            $output .= $this->generateRouteDefinition($route);
        }
        
        if (in_array($prefix, ['admin', 'guest'])) {
            $output .= "});\n";
        }
        
        return $output;
    }
    
    private function generateRouteDefinition($route)
    {
        $method = strtolower($route['methods'][0]);
        $uri = str_replace($this->extractRoutePrefix($route['uri']) . '/', '', $route['uri']);
        $name = $route['name'] ? "->name('{$route['name']}')" : '';
        
        return "    Route::{$method}('{$uri}', [{$route['controller']}::class, '{$route['method']}']){$name};\n";
    }
    
    private function generateTestFiles()
    {
        echo "\n🧪 GENERATING TEST FILES\n";
        echo "=======================\n";
        
        $testContent = $this->generateFeatureTest();
        $testPath = base_path('tests/Feature/RouteValidationTest.php');
        
        if (!File::exists(dirname($testPath))) {
            File::makeDirectory(dirname($testPath), 0755, true);
        }
        
        File::put($testPath, $testContent);
        echo "✅ Generated test file: tests/Feature/RouteValidationTest.php\n";
    }
    
    private function generateFeatureTest()
    {
        $routes = array_filter($this->registeredRoutes, function($route) {
            return $route['name'] !== null;
        });
        
        $testMethods = '';
        foreach ($routes as $route) {
            $testMethods .= $this->generateTestMethod($route);
        }
        
        return "<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class RouteValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test admin user
        \$this->admin = Admin::factory()->create([
            'email' => 'test@admin.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Test all routes are accessible
     */
    public function test_all_routes_accessible()
    {
        \$this->actingAs(\$this->admin, 'admin');
        
        \$routes = [
{$this->generateRouteTestData()}
        ];
        
        foreach (\$routes as \$routeData) {
            \$response = \$this->get(route(\$routeData['name'], \$routeData['params'] ?? []));
            
            \$this->assertNotEquals(404, \$response->getStatusCode(), 
                \"Route {\$routeData['name']} returned 404\");
        }
    }
{$testMethods}
}
";
    }
    
    private function generateRouteTestData()
    {
        $testData = '';
        foreach ($this->registeredRoutes as $route) {
            if ($route['name'] && in_array('GET', $route['methods'])) {
                $params = $this->generateTestParameters($route['parameters']);
                $testData .= "            ['name' => '{$route['name']}', 'params' => {$params}],\n";
            }
        }
        return rtrim($testData, ",\n");
    }
    
    private function generateTestParameters($parameters)
    {
        if (empty($parameters)) {
            return '[]';
        }
        
        $params = [];
        foreach ($parameters as $param) {
            $params[] = "'{$param}' => 1";
        }
        
        return '[' . implode(', ', $params) . ']';
    }
    
    private function generateTestMethod($route)
    {
        $methodName = 'test_' . str_replace('.', '_', $route['name'] ?? 'unnamed') . '_route';
        
        return "
    public function {$methodName}()
    {
        \$this->actingAs(\$this->admin, 'admin');
        
        \$response = \$this->get(route('{$route['name']}'));
        
        \$this->assertNotEquals(404, \$response->getStatusCode());
    }
";
    }
}

// Run the audit
$auditor = new RouteAuditor();
$auditor->audit();

echo "\n✅ Comprehensive route audit completed!\n";
echo "Check the generated files:\n";
echo "- route-audit-report.json (detailed report)\n";
echo "- routes/web-cleaned.php (cleaned routes)\n";
echo "- tests/Feature/RouteValidationTest.php (automated tests)\n";
