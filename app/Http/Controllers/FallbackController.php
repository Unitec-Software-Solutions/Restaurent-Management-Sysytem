<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class FallbackController extends Controller
{
    /**
     * Handle undefined route requests
     */
    public function __invoke(Request $request)
    {
        $path = $request->path();
        $method = $request->method();
        
        // Log the attempted route for analysis
        Log::warning("Undefined route accessed: {$method} {$path}", [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'user_id' => Auth::check() ? Auth::id() : null,
        ]);

        // Suggest similar routes
        $suggestions = $this->suggestSimilarRoutes($path);

        // Return appropriate response based on request type
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Route not found',
                'message' => "The endpoint '{$path}' was not found.",
                'suggestions' => $suggestions,
                'status' => 404
            ], 404);
        }

        // For web requests, show a user-friendly page
        return response()->view('errors.route-not-found', [
            'attempted_path' => $path,
            'method' => $method,
            'suggestions' => $suggestions,
            'is_admin' => $request->is('admin/*'),
        ], 404);
    }

    /**
     * Suggest similar routes based on the attempted path
     */
    protected function suggestSimilarRoutes(string $path): array
    {
        $suggestions = [];
        $pathParts = explode('/', trim($path, '/'));
        
        // Get all registered routes
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            $routeUri = $route->uri();
            $routeName = $route->getName();
            
            if (!$routeName) continue;
            
            // Calculate similarity
            $similarity = $this->calculateSimilarity($path, $routeUri);
            
            if ($similarity > 0.5) {
                $suggestions[] = [
                    'name' => $routeName,
                    'uri' => $routeUri,
                    'similarity' => $similarity,
                    'methods' => $route->methods(),
                ];
            }
        }

        // Sort by similarity and return top 5
        usort($suggestions, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        
        return array_slice($suggestions, 0, 5);
    }

    /**
     * Calculate similarity between two paths
     */
    protected function calculateSimilarity(string $path1, string $path2): float
    {
        // Simple similarity calculation
        $path1Parts = explode('/', trim($path1, '/'));
        $path2Parts = explode('/', trim($path2, '/'));
        
        $common = array_intersect($path1Parts, $path2Parts);
        $total = array_unique(array_merge($path1Parts, $path2Parts));
        
        return count($total) > 0 ? count($common) / count($total) : 0;
    }
}
