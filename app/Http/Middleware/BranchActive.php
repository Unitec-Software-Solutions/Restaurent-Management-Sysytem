<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BranchActive
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()->branch->is_active) {
            abort(403, 'Branch is inactive.');
        }
        return $next($request);
    }
}
