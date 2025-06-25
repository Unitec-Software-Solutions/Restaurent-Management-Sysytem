<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Observers\OrganizationObserver;
use Illuminate\Support\Facades\Gate;
use App\Policies\RolePolicy;
use App\Models\Role;
use App\Models\Module;
use App\Policies\ModulePolicy;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Organization::creating(function ($org) {
            $org->activation_key = Str::random(40);
        });

        // Register observer
        Organization::observe(OrganizationObserver::class);
        
        // Register policy
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Module::class, ModulePolicy::class);

        $this->registerPolicies();
        $this->registerBladeDirectives();
        $this->registerRouteMacros();
        $this->registerRouteHelpers();
        $this->registerGlobalViewVariables();
    }

    /**
     * Register the application's policy mappings.
     *
     * @return void
     */
    protected function registerPolicies()
    {
        Gate::policy(\App\Models\Branch::class, \App\Policies\BranchPolicy::class);
    }

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\Branch::class => \App\Policies\BranchPolicy::class,
        \App\Models\Module::class => \App\Policies\ModulePolicy::class,
    ];

    /**
     * Register custom Blade directives for restaurant permissions and safe routing
     */
    protected function registerBladeDirectives()
    {
        // Enhanced directive to check if a route exists
        Blade::if('routeexists', function ($route) {
            return Route::has($route);
        });

        // Enhanced safe route directive for comprehensive error prevention
        Blade::directive('safeRoute', function ($expression) {
            return "<?php echo app('App\\Services\\RouteAuditService')->safeRoute({$expression}); ?>";
        });

        // Route existence check directive
        Blade::directive('routeExists', function ($expression) {
            return "<?php echo app('App\\Services\\RouteAuditService')->routeExists({$expression}) ? 'true' : 'false'; ?>";
        });

        // Enhanced safe link directive with fallback text and styling
        Blade::directive('safeLink', function ($expression) {
            return "<?php 
                \$args = {$expression};
                \$route = \$args[0] ?? '#';
                \$text = \$args[1] ?? 'Link';
                \$attributes = \$args[2] ?? [];
                \$fallbackText = \$args[3] ?? 'Unavailable';
                
                if (\\Illuminate\\Support\\Facades\\Route::has(\$route)) {
                    \$url = route(\$route);
                    \$attrStr = '';
                    foreach (\$attributes as \$key => \$value) {
                        \$attrStr .= \" {\$key}='{$value}'\";
                    }
                    echo \"<a href='{\$url}'{\$attrStr}>{\$text}</a>\";
                } else {
                    echo \"<span class='disabled-link text-muted'>{\$fallbackText}</span>\";
                }
            ?>";
        });

        // Safe form action directive
        Blade::directive('safeAction', function ($expression) {
            return "<?php 
                \$route = {$expression};
                if (\\Illuminate\\Support\\Facades\\Route::has(\$route)) {
                    echo route(\$route);
                } else {
                    echo 'javascript:void(0)';
                    \\Illuminate\\Support\\Facades\\Log::warning('Form action route not found: ' . \$route);
                }
            ?>";
        });

        // Organization-aware route directive for admin routes
        Blade::directive('adminRoute', function ($expression) {
            return "<?php 
                \$routeName = {$expression};
                if (\\Illuminate\\Support\\Facades\\Route::has(\$routeName)) {
                    \$params = [];
                    if (auth('admin')->check() && auth('admin')->user()->organization_id) {
                        \$params['organization'] = auth('admin')->user()->organization_id;
                    }
                    echo route(\$routeName, \$params);
                } else {
                    echo '#';
                    \\Illuminate\\Support\\Facades\\Log::warning('Admin route not found: ' . \$routeName);
                }
            ?>";
        });

        // Restaurant permission directives (existing)
        Blade::directive('canRestaurant', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->can($permission)): ?>";
        });

        Blade::directive('endcanRestaurant', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('hasRestaurantRole', function ($role) {
            return "<?php if(auth()->check() && auth()->user() instanceof \\App\\Models\\Employee && auth()->user()->hasRole($role)): ?>";
        });

        Blade::directive('endhasRestaurantRole', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('isServer', function () {
            return "<?php if(auth()->check() && auth()->user() instanceof \\App\\Models\\Employee && auth()->user()->isServer()): ?>";
        });

        Blade::directive('endisServer', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('isChef', function () {
            return "<?php if(auth()->check() && auth()->user() instanceof \\App\\Models\\Employee && auth()->user()->isChef()): ?>";
        });

        Blade::directive('endisChef', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('isHost', function () {
            return "<?php if(auth()->check() && auth()->user() instanceof \\App\\Models\\Employee && auth()->user()->isHost()): ?>";
        });

        Blade::directive('endisHost', function () {
            return "<?php endif; ?>";
        });

        // Organization-aware route directive
        Blade::directive('orgRoute', function ($expression) {
            return "<?php 
                \$args = {$expression};
                \$routeName = \$args[0] ?? '';
                \$parameters = \$args[1] ?? [];
                \$fallback = \$args[2] ?? '#';
                
                // Auto-inject organization if route requires it and not provided
                if (str_contains(\$routeName, 'admin.branches.') || str_contains(\$routeName, 'admin.organizations.')) {
                    if (!isset(\$parameters['organization']) && auth('admin')->check()) {
                        \$user = auth('admin')->user();
                        if (\$user && \$user->organization_id) {
                            \$parameters['organization'] = \$user->organization_id;
                        }
                    }
                }
                
                try {
                    if (\\Illuminate\\Support\\Facades\\Route::has(\$routeName)) {
                        echo route(\$routeName, \$parameters);
                    } else {
                        echo \$fallback;
                        \\Illuminate\\Support\\Facades\\Log::warning('Route not found: ' . \$routeName);
                    }
                } catch (\\Exception \$e) {
                    echo \$fallback;
                    \\Illuminate\\Support\\Facades\\Log::error('Route generation failed: ' . \$e->getMessage());
                }
            ?>";
        });

        // Organization-aware link directive
        Blade::directive('orgLink', function ($expression) {
            return "<?php 
                \$args = {$expression};
                \$routeName = \$args[0] ?? '';
                \$text = \$args[1] ?? 'Link';
                \$parameters = \$args[2] ?? [];
                \$attributes = \$args[3] ?? [];
                \$fallbackText = \$args[4] ?? 'Unavailable';
                
                // Auto-inject organization if route requires it and not provided
                if (str_contains(\$routeName, 'admin.branches.') || str_contains(\$routeName, 'admin.organizations.')) {
                    if (!isset(\$parameters['organization']) && auth('admin')->check()) {
                        \$user = auth('admin')->user();
                        if (\$user && \$user->organization_id) {
                            \$parameters['organization'] = \$user->organization_id;
                        }
                    }
                }
                
                try {
                    if (\\Illuminate\\Support\\Facades\\Route::has(\$routeName)) {
                        \$url = route(\$routeName, \$parameters);
                        \$attrStr = '';
                        foreach (\$attributes as \$key => \$value) {
                            \$attrStr .= \" {\$key}='{\$value}'\";
                        }
                        echo \"<a href='{\$url}'{\$attrStr}>{\$text}</a>\";
                    } else {
                        echo \"<span class='disabled-link text-muted'>{\$fallbackText}</span>\";
                    }
                } catch (\\Exception \$e) {
                    echo \"<span class='disabled-link text-muted'>{\$fallbackText}</span>\";
                    \\Illuminate\\Support\\Facades\\Log::error('Route link generation failed: ' . \$e->getMessage());
                }
            ?>";
        });
    }

    /**
     * Register safe route macros for enhanced route handling
     */
    protected function registerRouteMacros()
    {
        // Safe route macro that logs failures and provides fallbacks
        Route::macro('safeGet', function ($uri, $action) {
            try {
                $routeName = Str::slug($uri, '.') . '.index';
                return Route::get($uri, $action)->name($routeName);
            } catch (\Exception $e) {
                Log::error("Route registration failed for GET {$uri}: " . $e->getMessage());
                
                // Register fallback route
                return Route::get($uri, function () use ($uri) {
                    return response()->view('errors.route-unavailable', [
                        'attempted_route' => $uri,
                        'message' => 'This feature is temporarily unavailable.'
                    ], 503);
                })->name($routeName ?? 'fallback.' . Str::slug($uri, '.'));
            }
        });

        // Safe route macro for POST routes
        Route::macro('safePost', function ($uri, $action) {
            try {
                $routeName = Str::slug($uri, '.') . '.store';
                return Route::post($uri, $action)->name($routeName);
            } catch (\Exception $e) {
                Log::error("Route registration failed for POST {$uri}: " . $e->getMessage());
                return Route::post($uri, function () {
                    return response()->json(['error' => 'Action unavailable'], 503);
                })->name($routeName ?? 'fallback.' . Str::slug($uri, '.') . '.store');
            }
        });

        // Route testing macro
        Route::macro('test', function () {
            return new class {
                public function exists(string $routeName): bool {
                    return Route::has($routeName);
                }
                
                public function validate(string $routeName): array {
                    if (!Route::has($routeName)) {
                        return ['status' => 'missing', 'message' => "Route '{$routeName}' not found"];
                    }
                    
                    try {
                        $route = Route::getRoutes()->getByName($routeName);
                        $controller = $route->getActionName();
                        
                        if (Str::contains($controller, '@') || Str::contains($controller, '::')) {
                            $controllerClass = Str::before($controller, '@') ?: Str::before($controller, '::');
                            if (!class_exists($controllerClass)) {
                                return ['status' => 'controller_missing', 'message' => "Controller '{$controllerClass}' not found"];
                            }
                        }
                        
                        return ['status' => 'valid', 'message' => 'Route is valid'];
                    } catch (\Exception $e) {
                        return ['status' => 'error', 'message' => $e->getMessage()];
                    }
                }
            };
        });

        // Register route group macros from RouteGroupService
        $routeGroupService = app(\App\Services\RouteGroupService::class);
        $routeGroupService->registerRouteMacros();
    }

    /**
     * Register global route helper functions
     */
    protected function registerRouteHelpers()
    {
        // Use static flag to prevent multiple registrations
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        // Safe route helper that never throws exceptions
        if (!function_exists('safe_route')) {
            function safe_route(string $name, array $parameters = [], string $fallback = '#'): string {
                try {
                    if (\Illuminate\Support\Facades\Route::has($name)) {
                        return route($name, $parameters);
                    }
                    
                    \Illuminate\Support\Facades\Log::warning("Route '{$name}' not found, using fallback");
                    return $fallback;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error generating route '{$name}': " . $e->getMessage());
                    return $fallback;
                }
            }
        }

        // Route existence checker
        if (!function_exists('route_exists')) {
            function route_exists(string $name): bool {
                return \Illuminate\Support\Facades\Route::has($name);
            }
        }

        // Get route with validation
        if (!function_exists('validated_route')) {
            function validated_route(string $name, array $parameters = []): ?string {
                if (!\Illuminate\Support\Facades\Route::has($name)) {
                    return null;
                }
                
                try {
                    return route($name, $parameters);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error validating route '{$name}': " . $e->getMessage());
                    return null;
                }
            }
        }
    }

    /**
     * Register global view variables for admin context
     */
    protected function registerGlobalViewVariables()
    {
        View::composer('*', function ($view) {
            // Only add admin context variables to admin views
            if (auth('admin')->check()) {
                $admin = auth('admin')->user();
                
                $view->with([
                    'currentAdmin' => $admin,
                    'currentOrganization' => $admin->organization ?? null,
                    'currentOrganizationId' => $admin->organization_id ?? null,
                    'isSuper' => $admin->is_super_admin ?? false
                ]);
            }
        });
    }
}
