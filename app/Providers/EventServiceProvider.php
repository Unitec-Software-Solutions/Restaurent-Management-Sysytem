<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use App\Events\ReservationCancelled;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Listeners\ReservationCancelledListener;
use App\Models\Organization;
use App\Models\Branch;
use App\Observers\OrganizationObserver;
use App\Observers\BranchObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ReservationCancelled::class => [
            ReservationCancelledListener::class,
        ],
    ];

    /**
     * The model observers.
     *
     * @var array
     */
    protected $observers = [
        // Note: Using boot() method instead for better reliability
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Log observer registration for debugging
        Log::info('EventServiceProvider::boot() - Registering observers');
        
        // Manually register observers to ensure they work
        Organization::observe(OrganizationObserver::class);
        Branch::observe(BranchObserver::class);
        
        Log::info('EventServiceProvider::boot() - Observers registered successfully');
    }
} 