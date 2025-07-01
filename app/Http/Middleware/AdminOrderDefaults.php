<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrderDefaults
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('admin')->check()) {
            $admin = auth('admin')->user();
            
            // Add default admin values to the request
            $request->merge([
                'admin_defaults' => [
                    'branch_id' => $admin->branch_id,
                    'organization_id' => $admin->organization_id,
                    'created_by' => $admin->id,
                    'placed_by_admin' => true
                ]
            ]);
        }

        return $next($request);
    }
}