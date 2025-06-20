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
