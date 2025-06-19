<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BranchPermission
{
    public function handle(Request $request, Closure $next)
    {
        $branch = $request->route('branch');
        if (!$branch || !$branch->is_active) {
            return redirect()->route('admin.dashboard')->withErrors(['message' => 'Branch is inactive']);
        }
        return $next($request);
    }
}
