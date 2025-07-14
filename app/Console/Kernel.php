<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\DatabaseSeedSafeCommand::class,
        Commands\DatabaseIntegrityCheckCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            \App\Models\Subscription::where('expires_at', '<', now())
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            \App\Models\Organization::whereHas('subscriptions', function ($q) {
                $q->where('status', 'expired');
            })->update(['is_active' => false]);
        })->daily();

        $schedule->command('subscriptions:check')->daily();
    }
}