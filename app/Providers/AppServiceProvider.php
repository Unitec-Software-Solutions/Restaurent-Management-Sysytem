<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Observers\OrganizationObserver;
use Illuminate\Support\Facades\Gate;
use App\Policies\RolePolicy;
use App\Models\Role;

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
    }
}
