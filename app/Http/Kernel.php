<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'auth:admin' => \App\Http\Middleware\Authenticate::class,
        'organization.active' => \App\Http\Middleware\OrganizationActive::class,
        'org.active' => \App\Http\Middleware\OrganizationActive::class,
        'branch.active' => \App\Http\Middleware\BranchPermission::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'superadmin' => \App\Http\Middleware\SuperAdmin::class,
        'subscription.active' => \App\Http\Middleware\SubscriptionActive::class
    ];
}