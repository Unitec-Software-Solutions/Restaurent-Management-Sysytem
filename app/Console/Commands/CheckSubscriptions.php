<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Organization;

class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';
    protected $description = 'Check and deactivate expired subscriptions';

    public function handle()
    {
        Subscription::where('end_date', '<', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);

        Organization::whereDoesntHave('subscriptions', function ($query) {
                $query->where('is_active', true);
            })
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->info('Subscription and organization status updated');
    }
}
