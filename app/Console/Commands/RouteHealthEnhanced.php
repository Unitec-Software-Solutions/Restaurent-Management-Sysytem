<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Services\RouteAuditService;

class RouteHealthEnhanced extends Command
{
    protected $signature = 'route:health-enhanced 
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
        $this->info('🏥 Generating Enhanced Route Health Report...');
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

    protected function startContinuousMonitoring(): int
    {
        $this->info('🔄 Starting continuous route monitoring...');
        $this->info('Press Ctrl+C to stop monitoring');
        $this->newLine();

        try {
            while (true) {
                $this->call('route:audit', ['--missing-only' => true]);
                $this->info('⏰ Next check in 5 minutes...');
                sleep(300); // Check every 5 minutes
            }
        } catch (\Exception $e) {
            $this->error('Monitoring stopped: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function collectRouteData(): array
    {
        $routes = [];
        $registeredRoutes = Route::getRoutes();
        
        foreach ($registeredRoutes as $route) {
            $name = $route->getName();
            if (!$name) continue;

            $routes[$name] = [
                'name' => $name,
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'parameters' => $route->parameterNames(),
                'domain' => $route->domain(),
                'usage_count' => $this->getRouteUsageCount($name),
                'last_used' => $this->getLastUsedTimestamp($name),
                'performance_score' => $this->calculatePerformanceScore($name),
                'security_score' => $this->calculateSecurityScore($route),
                'accessibility_score' => $this->calculateAccessibilityScore($route),
                'seo_score' => $this->calculateSeoScore($route),
                'controller_exists' => $this->checkControllerExists($route->getActionName()),
                'method_exists' => $this->checkMethodExists($route->getActionName()),
            ];
        }

        return $routes;
    }

    protected function generateUsageStats(array $routeData): array
    {
        $totalRoutes = count($routeData);
        $usedRoutes = count(array_filter($routeData, fn($r) => $r['usage_count'] > 0));
        $unusedRoutes = $totalRoutes - $usedRoutes;
        
        $avgUsage = $totalRoutes > 0 ? array_sum(array_column($routeData, 'usage_count')) / $totalRoutes : 0;
        $mostUsedRoutes = array_slice(
            array_filter($routeData, fn($r) => $r['usage_count'] > 0),
            0, 10
        );
        
        return [
            'total_routes' => $totalRoutes,
            'used_routes' => $usedRoutes,
            'unused_routes' => $unusedRoutes,
            'usage_ratio' => $totalRoutes > 0 ? $usedRoutes / $totalRoutes * 100 : 0,
            'average_usage' => $avgUsage,
            'most_used' => $mostUsedRoutes,
            'unused_list' => array_filter($routeData, fn($r) => $r['usage_count'] === 0),
        ];
    }

    protected function calculateHealthMetrics(array $routeData): array
    {
        if (empty($routeData)) {
            return [
                'overall_health' => 0,
                'security_health' => 0,
                'accessibility_health' => 0,
                'seo_health' => 0,
                'critical_routes' => [],
                'secure_routes' => 0,
                'insecure_routes' => 0,
            ];
        }

        $scores = array_column($routeData, 'performance_score');
        $securityScores = array_column($routeData, 'security_score');
        $accessibilityScores = array_column($routeData, 'accessibility_score');
        $seoScores = array_column($routeData, 'seo_score');

        return [
            'overall_health' => array_sum($scores) / count($scores),
            'security_health' => array_sum($securityScores) / count($securityScores),
            'accessibility_health' => array_sum($accessibilityScores) / count($accessibilityScores),
            'seo_health' => array_sum($seoScores) / count($seoScores),
            'critical_routes' => array_filter($routeData, fn($r) => $r['performance_score'] < 5),
            'secure_routes' => count(array_filter($routeData, fn($r) => $r['security_score'] > 8)),
            'insecure_routes' => count(array_filter($routeData, fn($r) => $r['security_score'] < 5)),
        ];
    }

    protected function generateUsageHeatmap(array $routeData): array
    {
        $heatmap = [];
        $controllers = [];

        foreach ($routeData as $route) {
            $controller = $this->extractControllerName($route['action']);
            if (!isset($controllers[$controller])) {
                $controllers[$controller] = [];
            }
            $controllers[$controller][] = $route['usage_count'];
        }

        foreach ($controllers as $controller => $usages) {
            $heatmap[$controller] = [
                'total_usage' => array_sum($usages),
                'avg_usage' => count($usages) > 0 ? array_sum($usages) / count($usages) : 0,
                'route_count' => count($usages),
                'heat_level' => $this->calculateHeatLevel(array_sum($usages))
            ];
        }

        return $heatmap;
    }

    protected function outputTable(array $routeData, array $usageStats, array $healthMetrics): void
    {
        // Summary table
        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                ['Total Routes', $usageStats['total_routes'], '📊'],
                ['Used Routes', $usageStats['used_routes'], '✅'],
                ['Unused Routes', $usageStats['unused_routes'], $usageStats['unused_routes'] > 0 ? '⚠️' : '✅'],
                ['Usage Ratio', number_format($usageStats['usage_ratio'], 1) . '%', $usageStats['usage_ratio'] > 80 ? '✅' : '⚠️'],
                ['Overall Health', number_format($healthMetrics['overall_health'], 1) . '/10', $healthMetrics['overall_health'] > 7 ? '✅' : '⚠️'],
                ['Security Score', number_format($healthMetrics['security_health'], 1) . '/10', $healthMetrics['security_health'] > 8 ? '✅' : '🔒'],
                ['Accessibility Score', number_format($healthMetrics['accessibility_health'], 1) . '/10', $healthMetrics['accessibility_health'] > 7 ? '✅' : '♿'],
            ]
        );

        // Top issues
        if (!empty($healthMetrics['critical_routes'])) {
            $this->newLine();
            $this->warn('🚨 Critical Routes (Performance Score < 5):');
            foreach (array_slice($healthMetrics['critical_routes'], 0, 10) as $route) {
                $this->line("  • {$route['name']} (Score: {$route['performance_score']})");
            }
        }

        // Most used routes
        if (!empty($usageStats['most_used'])) {
            $this->newLine();
            $this->info('🔥 Most Used Routes:');
            foreach (array_slice($usageStats['most_used'], 0, 5) as $route) {
                $this->line("  • {$route['name']} ({$route['usage_count']} uses)");
            }
        }
    }

    protected function outputJson(array $routeData, array $usageStats, array $healthMetrics, ?array $heatmapData): void
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'summary' => $usageStats,
            'health_metrics' => $healthMetrics,
            'routes' => $routeData,
        ];

        if ($heatmapData) {
            $report['heatmap'] = $heatmapData;
        }

        $this->line(json_encode($report, JSON_PRETTY_PRINT));
    }

    protected function outputHtml(array $routeData, array $usageStats, array $healthMetrics, ?array $heatmapData): void
    {
        $html = "<html><head><title>Route Health Report</title><style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .metric { display: inline-block; margin: 10px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
            .score { font-size: 2em; font-weight: bold; }
            .good { color: #28a745; } .warning { color: #ffc107; } .critical { color: #dc3545; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .heatmap { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin: 20px 0; }
            .heat-cell { padding: 10px; border-radius: 5px; text-align: center; }
            .very-hot { background: #dc3545; color: white; }
            .hot { background: #fd7e14; color: white; }
            .warm { background: #ffc107; color: black; }
            .cool { background: #20c997; color: white; }
            .cold { background: #6c757d; color: white; }
        </style></head><body><div class='container'>";
        
        $html .= "<h1>🏥 Route Health Report</h1>";
        $html .= "<p><strong>Generated:</strong> " . now()->format('Y-m-d H:i:s') . "</p>";
        
        // Health metrics
        $html .= "<h2>📊 Health Overview</h2><div class='metrics'>";
        $healthClass = $healthMetrics['overall_health'] > 7 ? 'good' : ($healthMetrics['overall_health'] > 4 ? 'warning' : 'critical');
        $html .= "<div class='metric'><div class='score {$healthClass}'>" . number_format($healthMetrics['overall_health'], 1) . "/10</div><div>Overall Health</div></div>";
        $html .= "<div class='metric'><div class='score'>" . $usageStats['total_routes'] . "</div><div>Total Routes</div></div>";
        $html .= "<div class='metric'><div class='score'>" . $usageStats['used_routes'] . "</div><div>Used Routes</div></div>";
        $html .= "<div class='metric'><div class='score'>" . $usageStats['unused_routes'] . "</div><div>Unused Routes</div></div>";
        $html .= "</div>";
        
        // Heatmap
        if ($heatmapData) {
            $html .= "<h2>🔥 Usage Heatmap</h2><div class='heatmap'>";
            foreach ($heatmapData as $controller => $data) {
                $html .= "<div class='heat-cell {$data['heat_level']}'>";
                $html .= "<strong>" . class_basename($controller) . "</strong><br>";
                $html .= "Usage: {$data['total_usage']}<br>";
                $html .= "Routes: {$data['route_count']}";
                $html .= "</div>";
            }
            $html .= "</div>";
        }
        
        $html .= "</div></body></html>";
        $this->line($html);
    }

    protected function exportReport(string $path, array $routeData, array $usageStats, array $healthMetrics, ?array $heatmapData): void
    {
        $report = [
            'generated_at' => now()->toISOString(),
            'summary' => $usageStats,
            'health_metrics' => $healthMetrics,
            'detailed_routes' => $routeData,
        ];

        if ($heatmapData) {
            $report['heatmap'] = $heatmapData;
        }

        File::put($path, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("📄 Report exported to: {$path}");
    }

    // Helper methods for scoring
    protected function getRouteUsageCount(string $routeName): int
    {
        // Estimate based on file scanning and common patterns
        return $this->routeAuditService->estimateRouteUsage($routeName);
    }

    protected function getLastUsedTimestamp(string $routeName): ?string
    {
        // This would connect to access logs in a real implementation
        return null;
    }

    protected function calculatePerformanceScore(string $routeName): float
    {
        $score = 10.0;

        // Deduct points for complex routes
        if (substr_count($routeName, '.') > 3) $score -= 1;
        
        // Deduct points for potentially slow operations
        if (Str::contains($routeName, ['export', 'report', 'bulk'])) $score -= 2;
        
        // Add points for cached routes
        if (Str::contains($routeName, ['dashboard', 'index'])) $score += 1;

        return max(0, min(10, $score));
    }

    protected function calculateSecurityScore($route): float
    {
        $score = 5.0;
        $middleware = $route->middleware();

        // Add points for authentication
        if (in_array('auth', $middleware) || in_array('auth:admin', $middleware)) $score += 2;
        
        // Add points for authorization
        if (in_array('can', $middleware) || Str::contains(implode(',', $middleware), 'permission')) $score += 2;
        
        // Add points for CSRF protection on POST routes
        if (in_array('POST', $route->methods()) && in_array('web', $middleware)) $score += 1;

        return max(0, min(10, $score));
    }

    protected function calculateAccessibilityScore($route): float
    {
        $score = 7.0;

        // Check if route serves user-facing content
        if (Str::contains($route->uri(), ['admin', 'api'])) {
            return $score; // Admin/API routes have different accessibility requirements
        }

        // Check for accessibility-friendly naming
        if (Str::contains($route->getName() ?? '', ['accessible', 'alt', 'aria'])) $score += 2;

        return max(0, min(10, $score));
    }

    protected function calculateSeoScore($route): float
    {
        $score = 5.0;
        $uri = $route->uri();

        // Add points for SEO-friendly URIs
        if (!Str::contains($uri, ['{', '}']) && !Str::contains($uri, 'admin')) $score += 2;
        
        // Add points for semantic naming
        if (Str::contains($uri, ['about', 'contact', 'services', 'products'])) $score += 2;
        
        // Deduct points for dynamic parameters
        if (Str::contains($uri, ['{', '}'])) $score -= 1;

        return max(0, min(10, $score));
    }

    protected function extractControllerName(string $action): string
    {
        if (Str::contains($action, '@')) {
            return Str::before($action, '@');
        }
        
        if (Str::contains($action, '::')) {
            return Str::before($action, '::');
        }
        
        return $action;
    }

    protected function calculateHeatLevel(int $usage): string
    {
        if ($usage > 1000) return 'very-hot';
        if ($usage > 500) return 'hot';
        if ($usage > 100) return 'warm';
        if ($usage > 10) return 'cool';
        return 'cold';
    }

    protected function checkControllerExists(string $action): bool
    {
        if (Str::contains($action, '@')) {
            $controller = Str::before($action, '@');
        } elseif (Str::contains($action, '::')) {
            $controller = Str::before($action, '::');
        } else {
            return false;
        }

        return class_exists($controller);
    }

    protected function checkMethodExists(string $action): bool
    {
        if (Str::contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
        } elseif (Str::contains($action, '::')) {
            [$controller, $method] = explode('::', $action);
        } else {
            return false;
        }

        return class_exists($controller) && method_exists($controller, $method);
    }
}
