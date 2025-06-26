<?php
// app/Http/Middleware/CheckPermission.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();
        $organization = $request->route('organization') ?? $user->organization;
        $branch = $request->route('branch');
        if (!$user->hasPermission($permission, $organization, $branch)) {
            abort(403, 'Unauthorized action');
        }
        return $next($request);
    }
}