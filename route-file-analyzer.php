<?php

/**
 * Route File Analysis - Direct file parsing approach
 */

echo "=== COMPREHENSIVE ROUTE AND CONTROLLER ANALYSIS ===\n\n";

class RouteFileAnalyzer 
{
    private $issues = [];
    private $routeFiles = [];
    private $controllerFiles = [];
    private $controllers = [];
    
    public function analyze()
    {
        echo "1. SCANNING ROUTE FILES...\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $this->scanRouteFiles();
        
        echo "2. SCANNING CONTROLLER FILES...\n"; 
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $this->scanControllerFiles();
        
        echo "3. ANALYZING ROUTE-CONTROLLER MAPPING...\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $this->analyzeMapping();
        
        echo "4. GENERATING FIXES...\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $this->generateFixes();
        
        $this->generateReport();
    }
    
    private function scanRouteFiles()
    {
        $routePaths = [
            'routes/web.php',
            'routes/groups/admin.php',
            'routes/groups/auth.php', 
            'routes/groups/guest.php',
            'routes/groups/public.php'
        ];
        
        foreach ($routePaths as $path) {
            if (file_exists($path)) {
                echo "  📁 Analyzing $path\n";
                $content = file_get_contents($path);
                $this->routeFiles[$path] = $this->parseRouteFile($content, $path);
            }
        }
        
        echo "\n";
    }
    
    private function parseRouteFile($content, $filePath)
    {
        $routes = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            
            // Match Route:: calls
            if (preg_match('/Route::(get|post|put|patch|delete|any|match|resource)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $line, $matches)) {
                $method = $matches[1];
                $uri = $matches[2];
                
                // Extract controller
                $controller = null;
                if (preg_match('/\[(.*?)::(class|class,\s*[\'"](\w+)[\'"])\]/', $line, $controllerMatch)) {
                    $controller = $controllerMatch[1];
                    $methodName = $controllerMatch[3] ?? null;
                } elseif (preg_match('/[\'"]([^@\'"]*)@(\w+)[\'"]/', $line, $controllerMatch)) {
                    $controller = $controllerMatch[1];
                    $methodName = $controllerMatch[2];
                    
                    $this->issues[] = [
                        'type' => 'DEPRECATED_SYNTAX',
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'route' => "$method $uri",
                        'controller' => $controller,
                        'message' => 'Using deprecated Controller@method syntax'
                    ];
                }
                
                // Extract route name
                $routeName = null;
                if (preg_match('/->name\s*\(\s*[\'"]([^\'"]+)[\'"]/', $line, $nameMatch)) {
                    $routeName = $nameMatch[1];
                }
                
                // Check middleware
                $middleware = [];
                if (preg_match('/->middleware\s*\(\s*[\'"]([^\'"]+)[\'"]/', $line, $middlewareMatch)) {
                    $middleware[] = $middlewareMatch[1];
                } elseif (preg_match('/->middleware\s*\(\s*\[(.*?)\]/', $line, $middlewareMatch)) {
                    $middlewareStr = $middlewareMatch[1];
                    preg_match_all('/[\'"]([^\'"]+)[\'"]/', $middlewareStr, $middlewareMatches);
                    $middleware = $middlewareMatches[1];
                }
                
                $routes[] = [
                    'method' => $method,
                    'uri' => $uri,
                    'controller' => $controller,
                    'controller_method' => $methodName ?? null,
                    'name' => $routeName,
                    'middleware' => $middleware,
                    'line' => $lineNum + 1,
                    'raw' => $line
                ];
                
                // Check for issues
                if (strpos($uri, 'admin') !== false && !in_array('auth:admin', $middleware) && !in_array('auth', $middleware)) {
                    $this->issues[] = [
                        'type' => 'MISSING_AUTH_MIDDLEWARE',
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'route' => "$method $uri",
                        'message' => 'Admin route without authentication middleware'
                    ];
                }
                
                if (!$routeName && $controller) {
                    $this->issues[] = [
                        'type' => 'MISSING_ROUTE_NAME',
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'route' => "$method $uri",
                        'controller' => $controller,
                        'message' => 'Controller route without name'
                    ];
                }
            }
        }
        
        return $routes;
    }
    
    private function scanControllerFiles()
    {
        $controllerDir = 'app/Http/Controllers';
        $this->scanDirectory($controllerDir);
        
        foreach ($this->controllerFiles as $file) {
            echo "  📄 Analyzing $file\n";
            $this->parseControllerFile($file);
        }
        
        echo "\n";
    }
    
    private function scanDirectory($dir)
    {
        if (!is_dir($dir)) return;
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->scanDirectory($path);
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $this->controllerFiles[] = $path;
            }
        }
    }
    
    private function parseControllerFile($filePath)
    {
        $content = file_get_contents($filePath);
        
        // Extract namespace and class name
        $namespace = '';
        $className = '';
        
        if (preg_match('/namespace\s+([^;]+);/', $content, $match)) {
            $namespace = $match[1];
        }
        
        if (preg_match('/class\s+(\w+)/', $content, $match)) {
            $className = $match[1];
        }
        
        if (!$className) return;
        
        $fullClassName = $namespace ? $namespace . '\\' . $className : $className;
        
        // Extract methods
        $methods = [];
        if (preg_match_all('/public\s+function\s+(\w+)\s*\([^)]*\)/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $methodName = $match[1];
                if (!in_array($methodName, ['__construct', '__destruct'])) {
                    $methods[] = $methodName;
                }
            }
        }
        
        $this->controllers[$fullClassName] = [
            'file' => $filePath,
            'class' => $className,
            'namespace' => $namespace,
            'methods' => $methods
        ];
    }
    
    private function analyzeMapping()
    {
        $routeControllerMap = [];
        
        foreach ($this->routeFiles as $file => $routes) {
            foreach ($routes as $route) {
                if ($route['controller']) {
                    $controllerKey = $route['controller'];
                    if (!isset($routeControllerMap[$controllerKey])) {
                        $routeControllerMap[$controllerKey] = [];
                    }
                    $routeControllerMap[$controllerKey][] = $route;
                }
            }
        }
        
        // Check for missing controllers and methods
        foreach ($routeControllerMap as $controllerName => $routes) {
            $found = false;
            $controllerData = null;
            
            // Try to find controller by different name patterns
            foreach ($this->controllers as $fullName => $data) {
                if (str_ends_with($fullName, $controllerName) || str_ends_with($fullName, '\\' . $controllerName)) {
                    $found = true;
                    $controllerData = $data;
                    break;
                }
            }
            
            if (!$found) {
                $this->issues[] = [
                    'type' => 'MISSING_CONTROLLER',
                    'controller' => $controllerName,
                    'routes_affected' => count($routes),
                    'message' => "Controller $controllerName not found"
                ];
                continue;
            }
            
            // Check methods
            foreach ($routes as $route) {
                if ($route['controller_method'] && !in_array($route['controller_method'], $controllerData['methods'])) {
                    $this->issues[] = [
                        'type' => 'MISSING_METHOD',
                        'controller' => $controllerName,
                        'method' => $route['controller_method'],
                        'route' => $route['method'] . ' ' . $route['uri'],
                        'message' => "Method {$route['controller_method']} not found in $controllerName"
                    ];
                }
            }
        }
    }
    
    private function generateFixes()
    {
        echo "Found " . count($this->issues) . " issues\n\n";
        
        $this->generateRouteFixFile();
        $this->generateControllerFixFiles();
        $this->generateTestFiles();
    }
    
    private function generateRouteFixFile()
    {
        $routeFixContent = "<?php\n\n";
        $routeFixContent .= "/**\n * ROUTE FIXES - Apply these changes to fix route issues\n */\n\n";
        
        $deprecatedSyntaxIssues = array_filter($this->issues, fn($issue) => $issue['type'] === 'DEPRECATED_SYNTAX');
        
        if (!empty($deprecatedSyntaxIssues)) {
            $routeFixContent .= "// 1. UPDATE DEPRECATED SYNTAX\n";
            $routeFixContent .= "// Replace the following lines:\n\n";
            
            foreach ($deprecatedSyntaxIssues as $issue) {
                $routeFixContent .= "// File: {$issue['file']}, Line: {$issue['line']}\n";
                $routeFixContent .= "// OLD: " . trim($issue['raw']) . "\n";
                
                // Generate modern syntax
                $controller = $issue['controller'];
                $parts = explode('@', $controller);
                if (count($parts) === 2) {
                    $newSyntax = str_replace($controller, $parts[0] . "::class, '{$parts[1]}'", $issue['raw']);
                    $routeFixContent .= "// NEW: " . trim($newSyntax) . "\n\n";
                }
            }
        }
        
        file_put_contents('route-fixes.php', $routeFixContent);
        echo "  📄 Generated route-fixes.php\n";
    }
    
    private function generateControllerFixFiles()
    {
        $missingMethods = array_filter($this->issues, fn($issue) => $issue['type'] === 'MISSING_METHOD');
        
        if (!empty($missingMethods)) {
            $methodsByController = [];
            foreach ($missingMethods as $issue) {
                $controller = $issue['controller'];
                if (!isset($methodsByController[$controller])) {
                    $methodsByController[$controller] = [];
                }
                $methodsByController[$controller][] = $issue['method'];
            }
            
            foreach ($methodsByController as $controller => $methods) {
                $this->generateControllerMethodFixes($controller, array_unique($methods));
            }
        }
    }
    
    private function generateControllerMethodFixes($controller, $methods)
    {
        $content = "<?php\n\n";
        $content .= "/**\n * MISSING METHODS FOR $controller\n * Add these methods to your controller\n */\n\n";
        
        foreach ($methods as $method) {
            $content .= $this->generateMethodTemplate($method) . "\n\n";
        }
        
        $fileName = 'controller-fixes-' . str_replace(['\\', '/'], '-', $controller) . '.php';
        file_put_contents($fileName, $content);
        echo "  📄 Generated $fileName\n";
    }
    
    private function generateMethodTemplate($methodName)
    {
        $templates = [
            'index' => 'public function index()
{
    // TODO: Implement index method
    return view(\'admin.' . strtolower(str_replace('Controller', '', $methodName)) . '.index\');
}',
            'create' => 'public function create()
{
    // TODO: Implement create method
    return view(\'admin.' . strtolower(str_replace('Controller', '', $methodName)) . '.create\');
}',
            'store' => 'public function store(Request $request)
{
    // TODO: Implement store method
    $request->validate([
        // Add validation rules
    ]);
    
    // Add store logic
    
    return redirect()->route(\'admin.' . strtolower(str_replace('Controller', '', $methodName)) . '.index\');
}',
            'show' => 'public function show($id)
{
    // TODO: Implement show method
    return view(\'admin.' . strtolower(str_replace('Controller', '', $methodName)) . '.show\', compact(\'id\'));
}',
            'edit' => 'public function edit($id)
{
    // TODO: Implement edit method
    return view(\'admin.' . strtolower(str_replace('Controller', '', $methodName)) . '.edit\', compact(\'id\'));
}',
            'update' => 'public function update(Request $request, $id)
{
    // TODO: Implement update method
    $request->validate([
        // Add validation rules
    ]);
    
    // Add update logic
    
    return redirect()->route(\'admin.' . strtolower(str_replace('Controller', '', $methodName)) . '.index\');
}',
            'destroy' => 'public function destroy($id)
{
    // TODO: Implement destroy method
    
    return redirect()->route(\'admin.' . strtolower(str_replace('Controller', '', $methodName)) . '.index\');
}'
        ];
        
        return $templates[$methodName] ?? "public function $methodName()
{
    // TODO: Implement $methodName method
}";
    }
    
    private function generateTestFiles()
    {
        $testContent = "<?php\n\n";
        $testContent .= "namespace Tests\\Feature;\n\n";
        $testContent .= "use Tests\\TestCase;\n";
        $testContent .= "use Illuminate\\Foundation\\Testing\\RefreshDatabase;\n\n";
        $testContent .= "class RouteFixesTest extends TestCase\n";
        $testContent .= "{\n";
        $testContent .= "    use RefreshDatabase;\n\n";
        
        // Generate tests for each route
        $allRoutes = [];
        foreach ($this->routeFiles as $routes) {
            $allRoutes = array_merge($allRoutes, $routes);
        }
        
        $testContent .= "    /** @test */\n";
        $testContent .= "    public function all_routes_are_accessible()\n";
        $testContent .= "    {\n";
        $testContent .= "        // TODO: Add authentication setup\n";
        $testContent .= "        // \$admin = Admin::factory()->create();\n";
        $testContent .= "        // \$this->actingAs(\$admin, 'admin');\n\n";
        
        foreach (array_slice($allRoutes, 0, 10) as $route) { // Test first 10 routes
            if ($route['name'] && $route['method'] === 'get') {
                $testContent .= "        \$this->get(route('{$route['name']}'))->assertSuccessful();\n";
            }
        }
        
        $testContent .= "    }\n";
        $testContent .= "}\n";
        
        file_put_contents('tests/Feature/RouteFixesTest.php', $testContent);
        echo "  📄 Generated tests/Feature/RouteFixesTest.php\n";
    }
    
    private function generateReport()
    {
        echo "\n5. SUMMARY REPORT\n";
        echo "━━━━━━━━━━━━━━━━━\n\n";
        
        $totalRoutes = 0;
        foreach ($this->routeFiles as $routes) {
            $totalRoutes += count($routes);
        }
        
        echo "📊 STATISTICS:\n";
        echo "  Total route files: " . count($this->routeFiles) . "\n";
        echo "  Total routes: $totalRoutes\n";
        echo "  Total controllers: " . count($this->controllers) . "\n";
        echo "  Total issues: " . count($this->issues) . "\n\n";
        
        $issuesByType = [];
        foreach ($this->issues as $issue) {
            $type = $issue['type'];
            $issuesByType[$type] = ($issuesByType[$type] ?? 0) + 1;
        }
        
        echo "🔴 ISSUES BY TYPE:\n";
        foreach ($issuesByType as $type => $count) {
            echo "  $type: $count\n";
        }
        
        echo "\n📝 RECOMMENDED ACTIONS:\n";
        echo "1. Review and apply route-fixes.php\n";
        echo "2. Add missing controller methods from controller-fixes-*.php files\n";
        echo "3. Run the generated tests to verify fixes\n";
        echo "4. Add proper middleware to admin routes\n";
        echo "5. Add route names to unnamed controller routes\n\n";
        
        echo "✅ Analysis complete! Check the generated fix files.\n";
    }
}

// Run the analysis
$analyzer = new RouteFileAnalyzer();
$analyzer->analyze();
