<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class ValidateRouteExistence
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only validate in development/testing environments
        if (!app()->environment(['local', 'testing'])) {
            return $response;
        }

        // Check for route usage in response content
        if ($response->getStatusCode() === 200 && method_exists($response, 'getContent')) {
            $content = $response->getContent();
            $updatedContent = $this->validateRoutesInContent($content, $request);
            
            // Update response content if it was modified
            if ($updatedContent !== $content && method_exists($response, 'setContent')) {
                $response->setContent($updatedContent);
            }
        }

        return $response;
    }

    /**
     * Validate routes mentioned in content
     */
    protected function validateRoutesInContent(string $content, Request $request): string
    {
        // Look for route() calls in the content
        preg_match_all('/route\([\'"]([^\'"]+)[\'"]/', $content, $matches);
        
        if (!empty($matches[1])) {
            $missingRoutes = [];
            
            foreach ($matches[1] as $routeName) {
                if (!Route::has($routeName)) {
                    $missingRoutes[] = $routeName;
                }
            }
            
            if (!empty($missingRoutes)) {
                Log::warning('Missing routes detected in response', [
                    'url' => $request->url(),
                    'missing_routes' => $missingRoutes,
                    'user_agent' => $request->userAgent(),
                ]);
                
                // In debug mode, add a comment to the response
                if (config('app.debug') && str_contains($content, '</body>')) {
                    $debugComment = "\n<!-- DEBUG: Missing routes detected: " . implode(', ', $missingRoutes) . " -->\n";
                    $content = str_replace('</body>', $debugComment . '</body>', $content);
                }
            }
        }

        return $content;
    }
}
