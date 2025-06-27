<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class RouteTest extends Command
{
    protected $signature = 'route:test 
                            {--route= : Test specific route}
                            {--method=GET : HTTP method to test}
                            {--with-auth : Test with authentication}
                            {--admin-id= : Admin ID for authentication}
                            {--comprehensive : Test all routes}
                            {--export= : Export results to file}';

    protected $description = 'Test route availability and functionality';

    protected $testResults = [];

    public function handle()
    {
        $this->info('🧪 Route Testing Suite');
        $this->newLine();

        if ($this->option('comprehensive')) {
            return $this->runComprehensiveTests();
        }

        if ($routeName = $this->option('route')) {
            return $this->testSingleRoute($routeName);
        }

        $this->warn('Please specify --route or --comprehensive option');
        return 1;
    }

    protected function runComprehensiveTests(): int
    {
        $this->info('🔄 Running comprehensive route tests...');
        $routes = Route::getRoutes();
        $totalRoutes = 0;
        $passedTests = 0;
        $failedTests = 0;

        foreach ($routes as $route) {
            $name = $route->getName();
            if (!$name) continue;

            $totalRoutes++;
            $result = $this->testRoute($route);
            
            if ($result['status'] === 'passed') {
                $passedTests++;
                $this->line("✅ {$name}");
            } else {
                $failedTests++;
                $this->line("❌ {$name} - {$result['error']}");
            }

            $this->testResults[] = $result;
        }

        $this->newLine();
        $this->info("📊 Test Summary:");
        $this->line("Total Routes: {$totalRoutes}");
        $this->line("Passed: {$passedTests}");
        $this->line("Failed: {$failedTests}");
        $this->line("Success Rate: " . round(($passedTests / $totalRoutes) * 100, 1) . "%");

        if ($exportPath = $this->option('export')) {
            $this->exportResults($exportPath);
        }

        return $failedTests > 0 ? 1 : 0;
    }

    protected function testSingleRoute(string $routeName): int
    {
        $route = Route::getRoutes()->getByName($routeName);
        
        if (!$route) {
            $this->error("Route '{$routeName}' not found");
            return 1;
        }

        $result = $this->testRoute($route);
        
        if ($result['status'] === 'passed') {
            $this->info("✅ Route test passed");
            $this->line("URL: {$result['url']}");
            $this->line("Response Time: {$result['response_time']}ms");
            return 0;
        } else {
            $this->error("❌ Route test failed: {$result['error']}");
            return 1;
        }
    }

    protected function testRoute($route): array
    {
        $startTime = microtime(true);
        $result = [
            'route_name' => $route->getName(),
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'status' => 'failed',
            'error' => null,
            'url' => null,
            'response_time' => null,
            'response_code' => null,
        ];

        try {
            // Test route URL generation
            $parameters = $this->generateTestParameters($route);
            $url = route($route->getName(), $parameters);
            $result['url'] = $url;

            // Test route accessibility
            $response = $this->makeTestRequest($url, $route->methods()[0]);
            
            $result['response_code'] = $response['status_code'];
            $result['response_time'] = round((microtime(true) - $startTime) * 1000, 2);

            // Consider various status codes as success
            if (in_array($response['status_code'], [200, 302, 301, 401, 403])) {
                $result['status'] = 'passed';
            } else {
                $result['error'] = "HTTP {$response['status_code']}";
            }

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $result['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
        }

        return $result;
    }

    protected function generateTestParameters($route): array
    {
        $parameters = [];
        $parameterNames = $route->parameterNames();

        foreach ($parameterNames as $param) {
            switch ($param) {
                case 'id':
                case 'user':
                case 'admin':
                case 'organization':
                case 'branch':
                    $parameters[$param] = 1;
                    break;
                case 'date':
                    $parameters[$param] = '2024-01-01';
                    break;
                case 'slug':
                    $parameters[$param] = 'test-slug';
                    break;
                default:
                    $parameters[$param] = 'test';
            }
        }

        return $parameters;
    }

    protected function makeTestRequest(string $url, string $method): array
    {
        try {
            // Setup authentication if required
            if ($this->option('with-auth')) {
                $this->setupAuthentication();
            }

            // Parse URL to get path for internal testing
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '/';
            $query = $parsedUrl['query'] ?? '';
            
            if ($query) {
                $path .= '?' . $query;
            }

            // Make internal request using Laravel's testing framework
            $response = $this->callInternal($method, $path);

            return [
                'status_code' => $response->getStatusCode(),
                'content' => $response->getContent(),
            ];

        } catch (\Exception $e) {
            return [
                'status_code' => 500,
                'content' => $e->getMessage(),
            ];
        }
    }

    protected function setupAuthentication(): void
    {
        if ($adminId = $this->option('admin-id')) {
            $admin = Admin::find($adminId);
        } else {
            $admin = Admin::first();
        }

        if ($admin) {
            Auth::guard('admin')->login($admin);
        }
    }

    protected function exportResults(string $path): void
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'total_routes' => count($this->testResults),
            'passed' => count(array_filter($this->testResults, fn($r) => $r['status'] === 'passed')),
            'failed' => count(array_filter($this->testResults, fn($r) => $r['status'] === 'failed')),
            'results' => $this->testResults,
        ];

        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("📄 Test results exported to: {$path}");
    }

    // Helper method to make HTTP calls (similar to Laravel's TestCase)
    protected function makeHttpCall(string $method, string $uri, array $parameters = []): \Illuminate\Http\Response
    {
        $request = \Illuminate\Http\Request::create($uri, $method, $parameters);
        
        // Set up request context
        app()->instance('request', $request);
        
        try {
            $response = app()->handle($request);
            return $response;
        } catch (\Exception $e) {
            // Create a mock response for errors
            return response($e->getMessage(), 500);
        }
    }
}
