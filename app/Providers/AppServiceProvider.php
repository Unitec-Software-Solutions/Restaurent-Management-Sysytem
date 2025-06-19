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
}
