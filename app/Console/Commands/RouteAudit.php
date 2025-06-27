<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Services\RouteAuditService;

class RouteAudit extends Command
{
    protected $signature = 'route:audit 
                            {--fix : Automatically fix found issues}
                            {--report : Generate detailed report}
                            {--file= : Specific file to audit}
                            {--missing-only : Only show missing routes}';

    protected $description = 'Comprehensive route system audit and repair';

    protected $routeAuditService;
    protected $issues = [];
    protected $fixedIssues = [];

    public function __construct(RouteAuditService $routeAuditService)
    {
        parent::__construct();
        $this->routeAuditService = $routeAuditService;
    }

    public function handle()
    {
        $this->info('🔍 Starting Route System Audit...');
        $this->newLine();

        // Step 1: Scan codebase
        $this->info('📂 Scanning codebase for route usage...');
        $routeUsages = $this->scanCodebase();

        // Step 2: Compare with registered routes
        $this->info('🔗 Comparing with registered routes...');
        $registeredRoutes = $this->getRegisteredRoutes();

        // Step 3: Identify issues
        $this->info('🚨 Identifying issues...');
        $this->identifyIssues($routeUsages, $registeredRoutes);

        // Step 4: Generate report
        if ($this->option('report')) {
            $this->generateReport();
        }

        // Step 5: Auto-fix if requested
        if ($this->option('fix')) {
            $this->autoFix();
        }

        // Step 6: Display summary
        $this->displaySummary();

        return 0;
    }

    protected function scanCodebase(): array
    {
        $routeUsages = [];
        $patterns = [
            'route\([\'"]([^\'"]+)[\'"]' => 'route_function',
            'Route::has\([\'"]([^\'"]+)[\'"]' => 'route_has',
            'route\([\'"]([^\'"]+)[\'"],\s*([^\)]+)\)' => 'route_with_params',
            '@routeexists\([\'"]?([^\'"]+)[\'"]?\)' => 'blade_directive',
        ];

        $directories = [
            resource_path('views'),
            app_path('Http/Controllers'),
            app_path('Http/Middleware'),
            base_path('routes'),
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
                            $routeUsages[] = [
                                'route' => $routeName,
                                'file' => $relativePath,
                                'type' => $type,
                                'line' => $this->getLineNumber($content, $match[0]),
                                'context' => trim(substr($match[0], 0, 100)),
                                'parameters' => isset($match[2]) ? $match[2] : null,
                            ];
                        }
                    }
                }
            }
        }

        return $routeUsages;
    }

    protected function getRegisteredRoutes(): array
    {
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if ($name) {
                $routes[$name] = [
                    'name' => $name,
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->middleware(),
                    'parameters' => $route->parameterNames(),
                ];
            }
        }
        return $routes;
    }

    protected function identifyIssues(array $routeUsages, array $registeredRoutes): void
    {
        $usedRoutes = array_unique(array_column($routeUsages, 'route'));
        
        foreach ($usedRoutes as $routeName) {
            if (!isset($registeredRoutes[$routeName])) {
                $this->issues[] = [
                    'type' => 'missing_route',
                    'route' => $routeName,
                    'severity' => 'high',
                    'message' => "Route '{$routeName}' is used but not defined",
                    'usages' => array_filter($routeUsages, fn($usage) => $usage['route'] === $routeName),
                ];
            }
        }

        // Check for broken controller references
        foreach ($registeredRoutes as $route) {
            if (Str::contains($route['action'], '@') || Str::contains($route['action'], '::')) {
                $this->validateControllerAction($route);
            }
        }

        // Check for duplicate routes
        $this->checkDuplicateRoutes($registeredRoutes);

        // Check parameter mismatches
        $this->checkParameterMismatches($routeUsages, $registeredRoutes);
    }

    protected function validateControllerAction(array $route): void
    {
        $action = $route['action'];
        
        if (Str::contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
        } elseif (Str::contains($action, '::')) {
            [$controller, $method] = explode('::', $action);
        } else {
            return;
        }

        if (!class_exists($controller)) {
            $this->issues[] = [
                'type' => 'missing_controller',
                'route' => $route['name'],
                'severity' => 'high',
                'message' => "Controller '{$controller}' not found for route '{$route['name']}'",
                'controller' => $controller,
                'method' => $method,
            ];
            return;
        }

        if (!method_exists($controller, $method)) {
            $this->issues[] = [
                'type' => 'missing_method',
                'route' => $route['name'],
                'severity' => 'medium',
                'message' => "Method '{$method}' not found in controller '{$controller}'",
                'controller' => $controller,
                'method' => $method,
            ];
        }
    }

    protected function checkDuplicateRoutes(array $registeredRoutes): void
    {
        $uriMap = [];
        foreach ($registeredRoutes as $route) {
            $key = implode('|', $route['methods']) . ':' . $route['uri'];
            if (isset($uriMap[$key])) {
                $this->issues[] = [
                    'type' => 'duplicate_route',
                    'route' => $route['name'],
                    'severity' => 'medium',
                    'message' => "Duplicate route definition: {$route['uri']}",
                    'duplicate_of' => $uriMap[$key],
                ];
            } else {
                $uriMap[$key] = $route['name'];
            }
        }
    }

    protected function checkParameterMismatches(array $routeUsages, array $registeredRoutes): void
    {
        foreach ($routeUsages as $usage) {
            if (!isset($registeredRoutes[$usage['route']]) || !$usage['parameters']) {
                continue;
            }

            $route = $registeredRoutes[$usage['route']];
            $expectedParams = count($route['parameters']);
            
            // Simple parameter count check
            $providedParams = substr_count($usage['parameters'], ',') + 1;
            
            if ($expectedParams !== $providedParams) {
                $this->issues[] = [
                    'type' => 'parameter_mismatch',
                    'route' => $usage['route'],
                    'severity' => 'medium',
                    'message' => "Parameter count mismatch for route '{$usage['route']}': expected {$expectedParams}, got {$providedParams}",
                    'file' => $usage['file'],
                    'line' => $usage['line'],
                ];
            }
        }
    }

    protected function autoFix(): void
    {
        $this->info('🔧 Starting automatic fixes...');
        $this->newLine();

        foreach ($this->issues as $issue) {
            switch ($issue['type']) {
                case 'missing_route':
                    $this->fixMissingRoute($issue);
                    break;
                case 'missing_controller':
                    $this->fixMissingController($issue);
                    break;
                case 'missing_method':
                    $this->fixMissingMethod($issue);
                    break;
            }
        }
    }

    protected function fixMissingRoute(array $issue): void
    {
        $routeName = $issue['route'];
        $this->info("🔧 Generating missing route: {$routeName}");

        // Analyze route name to suggest implementation
        $suggestion = $this->routeAuditService->generateRouteSuggestion($routeName, $issue['usages']);
        
        if ($suggestion) {
            $routeDefinition = $this->routeAuditService->generateRouteDefinition($suggestion);
            
            // Add to appropriate route file
            $routeFile = $this->determineRouteFile($routeName);
            $this->appendToRouteFile($routeFile, $routeDefinition);
            
            $this->fixedIssues[] = [
                'type' => 'missing_route',
                'route' => $routeName,
                'action' => 'Generated route definition',
                'file' => $routeFile,
            ];
            
            $this->line("  ✓ Generated route definition for '{$routeName}'");
        }
    }

    protected function fixMissingController(array $issue): void
    {
        $controller = $issue['controller'];
        $this->info("🔧 Creating missing controller: {$controller}");

        if ($this->routeAuditService->createControllerStub($controller, $issue['method'])) {
            $this->fixedIssues[] = [
                'type' => 'missing_controller',
                'controller' => $controller,
                'action' => 'Created controller stub',
            ];
            
            $this->line("  ✓ Created controller stub for '{$controller}'");
        }
    }

    protected function fixMissingMethod(array $issue): void
    {
        $controller = $issue['controller'];
        $method = $issue['method'];
        
        $this->info("🔧 Adding missing method: {$controller}::{$method}");

        if ($this->routeAuditService->addMethodToController($controller, $method)) {
            $this->fixedIssues[] = [
                'type' => 'missing_method',
                'controller' => $controller,
                'method' => $method,
                'action' => 'Added method stub',
            ];
            
            $this->line("  ✓ Added method stub '{$method}' to '{$controller}'");
        }
    }

    protected function generateReport(): void
    {
        $reportPath = storage_path('logs/route-audit-' . date('Y-m-d-H-i-s') . '.json');
        
        $report = [
            'timestamp' => now()->toISOString(),
            'summary' => [
                'total_issues' => count($this->issues),
                'high_severity' => count(array_filter($this->issues, fn($i) => $i['severity'] === 'high')),
                'medium_severity' => count(array_filter($this->issues, fn($i) => $i['severity'] === 'medium')),
                'low_severity' => count(array_filter($this->issues, fn($i) => $i['severity'] === 'low')),
                'fixed_issues' => count($this->fixedIssues),
            ],
            'issues' => $this->issues,
            'fixed_issues' => $this->fixedIssues,
        ];

        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("📊 Report saved to: {$reportPath}");
    }

    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Route Audit Summary');
        $this->line('========================');
        
        $high = count(array_filter($this->issues, fn($i) => $i['severity'] === 'high'));
        $medium = count(array_filter($this->issues, fn($i) => $i['severity'] === 'medium'));
        $low = count(array_filter($this->issues, fn($i) => $i['severity'] === 'low'));

        $this->line("Total Issues Found: " . count($this->issues));
        $this->line("  🔴 High Severity: {$high}");
        $this->line("  🟡 Medium Severity: {$medium}");
        $this->line("  🟢 Low Severity: {$low}");
        
        if ($this->option('fix')) {
            $this->line("Fixed Issues: " . count($this->fixedIssues));
        }

        if (!$this->option('missing-only')) {
            $this->newLine();
            $this->displayIssueDetails();
        }
    }

    protected function displayIssueDetails(): void
    {
        foreach ($this->issues as $issue) {
            $icon = match($issue['severity']) {
                'high' => '🔴',
                'medium' => '🟡',
                'low' => '🟢',
                default => '⚪'
            };
            
            $this->line("{$icon} {$issue['message']}");
            
            if (isset($issue['usages'])) {
                foreach ($issue['usages'] as $usage) {
                    $this->line("    📁 {$usage['file']}:{$usage['line']}");
                }
            }
        }
    }

    protected function getLineNumber(string $content, string $needle): int
    {
        $lines = explode("\n", substr($content, 0, strpos($content, $needle)));
        return count($lines);
    }

    protected function determineRouteFile(string $routeName): string
    {
        if (Str::startsWith($routeName, 'admin.')) {
            return base_path('routes/admin.php');
        }
        
        if (Str::startsWith($routeName, 'api.')) {
            return base_path('routes/api.php');
        }
        
        return base_path('routes/web.php');
    }

    protected function appendToRouteFile(string $filePath, string $content): void
    {
        if (!File::exists($filePath)) {
            $this->warn("Route file not found: {$filePath}");
            return;
        }

        File::append($filePath, "\n" . $content);
    }
}
