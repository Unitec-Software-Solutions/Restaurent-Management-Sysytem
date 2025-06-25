<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Services\RouteAuditService;

class RouteHealth extends Command
{
    protected $signature = 'route:health 
                            {--format=table : Output format (table, json, html)}
                            {--export= : Export to file}
                            {--with-heatmap : Generate route usage heatmap}
                            {--monitor : Start continuous monitoring}';

    protected $description = 'Generate comprehensive route health report with usage analytics and monitoring';

    protected $routeAuditService;

    public function __construct(RouteAuditService $routeAuditService)
    {
        parent::__construct();
        $this->routeAuditService = $routeAuditService;
    }

    public function handle()
    {
        $this->info('🏥 Generating Route Health Report...');
        $this->newLine();

        if ($this->option('monitor')) {
            return $this->startContinuousMonitoring();
        }

        // Collect comprehensive route data
        $routeData = $this->collectRouteData();
        $usageStats = $this->generateUsageStats($routeData);
        $healthMetrics = $this->calculateHealthMetrics($routeData);

        // Generate heatmap if requested
        $heatmapData = null;
        if ($this->option('with-heatmap')) {
            $heatmapData = $this->generateUsageHeatmap($routeData);
        }

        // Output in requested format
        $format = $this->option('format');
        
        switch ($format) {
            case 'json':
                $this->outputJson($routeData, $usageStats, $healthMetrics, $heatmapData);
                break;
            case 'html':
                $this->outputHtml($routeData, $usageStats, $healthMetrics, $heatmapData);
                break;
            default:
                $this->outputTable($routeData, $usageStats, $healthMetrics);
        }

        // Export if requested
        if ($exportPath = $this->option('export')) {
            $this->exportReport($exportPath, $routeData, $usageStats, $healthMetrics, $heatmapData);
        }

        return 0;
    }

    /**
     * Collects comprehensive route data and returns an array with health metrics.
     */
    protected function collectRouteData(): array
    {
        $routes = Route::getRoutes()->getRoutes();
        $health = [
            'total_routes' => count($routes),
            'named_routes' => 0,
            'unnamed_routes' => 0,
            'valid_controllers' => 0,
            'invalid_controllers' => 0,
            'closure_routes' => 0,
            'missing_controllers' => [],
            'duplicate_names' => [],
            'middleware_stats' => [],
            'method_stats' => [],
        ];

        $routeNames = [];
        $middlewareCount = [];
        $methodCount = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            $action = $route->getActionName();
            $methods = $route->methods();
            $middleware = $route->middleware();

            // Count named vs unnamed routes
            if ($name) {
                $health['named_routes']++;

                // Check for duplicate names
                if (isset($routeNames[$name])) {
                    $health['duplicate_names'][] = $name;
                } else {
                    $routeNames[$name] = true;
                }
            } else {
                $health['unnamed_routes']++;
            }

            // Analyze controllers
            if ($action === 'Closure') {
                $health['closure_routes']++;
            } elseif (str_contains($action, '@') || str_contains($action, '::')) {
                $controllerClass = str_contains($action, '@')
                    ? explode('@', $action)[0]
                    : explode('::', $action)[0];

                if (class_exists($controllerClass)) {
                    $health['valid_controllers']++;
                } else {
                    $health['invalid_controllers']++;
                    $health['missing_controllers'][] = [
                        'route' => $name ?: $route->uri(),
                        'controller' => $controllerClass,
                    ];
                }
            }

            // Count middleware usage
            foreach ($middleware as $mw) {
                $middlewareCount[$mw] = ($middlewareCount[$mw] ?? 0) + 1;
            }

            // Count method usage
            foreach ($methods as $method) {
                $methodCount[$method] = ($methodCount[$method] ?? 0) + 1;
            }
        }

        $health['middleware_stats'] = $middlewareCount;
        $health['method_stats'] = $methodCount;

        // Calculate health score
        $healthScore = $this->calculateHealthScore($health);
        $health['health_score'] = $healthScore;

        return $health;
    }

    protected function calculateHealthScore(array $health): float
    {
        $score = 100;

        // Deduct points for issues
        $score -= $health['unnamed_routes'] * 0.5; // 0.5 point per unnamed route
        $score -= $health['invalid_controllers'] * 10; // 10 points per invalid controller
        $score -= count($health['duplicate_names']) * 5; // 5 points per duplicate name
        $score -= ($health['total_routes'] - $health['named_routes'] - $health['closure_routes']) * 2; // 2 points per unnamed non-closure route

        return max(0, $score);
    }

    protected function displayHealthReport(array $health): void
    {
        // Overall health
        $healthScore = $health['health_score'];
        $statusColor = $healthScore >= 90 ? 'green' : ($healthScore >= 70 ? 'yellow' : 'red');
        $statusIcon = $healthScore >= 90 ? '✅' : ($healthScore >= 70 ? '⚠️' : '❌');
        
        $this->line("$statusIcon <fg=$statusColor>Health Score: {$healthScore}%</>");
        $this->newLine();

        // Route statistics
        $this->info('📊 Route Statistics');
        $this->line("Total Routes: {$health['total_routes']}");
        $this->line("Named Routes: {$health['named_routes']}");
        $this->line("Unnamed Routes: {$health['unnamed_routes']}");
        $this->line("Closure Routes: {$health['closure_routes']}");
        $this->newLine();

        // Controller validation
        $this->info('🎮 Controller Validation');
        $this->line("Valid Controllers: {$health['valid_controllers']}");
        $this->line("Invalid Controllers: {$health['invalid_controllers']}");
        
        if (!empty($health['missing_controllers'])) {
            $this->warn('Missing Controllers:');
            foreach ($health['missing_controllers'] as $missing) {
                $this->line("  - {$missing['route']}: {$missing['controller']}");
            }
        }
        $this->newLine();

        // Duplicate names
        if (!empty($health['duplicate_names'])) {
            $this->warn('🚨 Duplicate Route Names:');
            foreach ($health['duplicate_names'] as $duplicate) {
                $this->line("  - $duplicate");
            }
            $this->newLine();
        }

        // HTTP Methods distribution
        $this->info('🌐 HTTP Methods Distribution');
        foreach ($health['method_stats'] as $method => $count) {
            $this->line("$method: $count routes");
        }
        $this->newLine();

        // Top middleware usage
        $this->info('🛡️ Most Used Middleware');
        arsort($health['middleware_stats']);
        $topMiddleware = array_slice($health['middleware_stats'], 0, 10, true);
        foreach ($topMiddleware as $middleware => $count) {
            $this->line("$middleware: $count routes");
        }
    }

    protected function saveReport(array $health, string $filename): void
    {
        $path = storage_path("logs/$filename");
        $report = [
            'timestamp' => now()->toISOString(),
            'health_data' => $health,
        ];

        File::put($path, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("Report saved to: $path");
    }
}
