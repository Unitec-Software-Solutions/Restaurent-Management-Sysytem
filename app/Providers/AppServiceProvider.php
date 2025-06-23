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
     * Register custom Blade directives for restaurant permissions
     */
    protected function registerBladeDirectives()
    {
        // Directive to check if a route exists
        \Illuminate\Support\Facades\Blade::if('routeexists', function ($route) {
            return \Illuminate\Support\Facades\Route::has($route);
        });

        // Directive to safely generate routes with fallback
        \Illuminate\Support\Facades\Blade::directive('safeRoute', function ($expression) {
            return "<?php echo \Illuminate\Support\Facades\Route::has($expression) ? route($expression) : '#'; ?>";
        });

        // Directive for safe debug display
        \Illuminate\Support\Facades\Blade::directive('debugInfo', function ($expression) {
            return "<?php if(config('app.debug')): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('enddebugInfo', function () {
            return "<?php endif; ?>";
        });

        // Directive for safe route link with fallback text
        \Illuminate\Support\Facades\Blade::directive('safeRouteLink', function ($expression) {
            [$route, $text, $fallback] = explode(',', str_replace(['(', ')', "'", '"'], '', $expression));
            $route = trim($route);
            $text = trim($text);
            $fallback = trim($fallback ?: 'Link unavailable');
            
            return "<?php if(\Illuminate\Support\Facades\Route::has('$route')): ?>
                        <a href=\"<?php echo route('$route'); ?>\">$text</a>
                    <?php else: ?>
                        <span class=\"text-gray-400\">$fallback</span>
                    <?php endif; ?>";
        });

        // Directive to check if current user has restaurant permission
        \Illuminate\Support\Facades\Blade::directive('canRestaurant', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->can($permission)): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endcanRestaurant', function () {
            return "<?php endif; ?>";
        });

        // Directive to check if current user has specific restaurant role
        \Illuminate\Support\Facades\Blade::directive('hasRestaurantRole', function ($role) {
            return "<?php if(auth()->check() && auth()->user() instanceof \App\Models\Employee && auth()->user()->hasRole($role)): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endhasRestaurantRole', function () {
            return "<?php endif; ?>";
        });

        // Directive to check if current user is a specific restaurant role
        \Illuminate\Support\Facades\Blade::directive('isServer', function () {
            return "<?php if(auth()->check() && auth()->user() instanceof \App\Models\Employee && auth()->user()->isServer()): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endisServer', function () {
            return "<?php endif; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('isChef', function () {
            return "<?php if(auth()->check() && auth()->user() instanceof \App\Models\Employee && auth()->user()->isChef()): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endisChef', function () {
            return "<?php endif; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('isHost', function () {
            return "<?php if(auth()->check() && auth()->user() instanceof \App\Models\Employee && auth()->user()->isHost()): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endisHost', function () {
            return "<?php endif; ?>";
        });
    }
}
