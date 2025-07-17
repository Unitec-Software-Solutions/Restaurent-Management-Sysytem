<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Spatie\Permission\Models\Permission as SpatiePermission;

class SyncSystemPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all permissions in Permission::getSystemPermissions() exist in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $definitions = Permission::getSystemPermissions();
        $count = 0;
        foreach ($definitions as $category => $perms) {
            foreach ($perms as $name => $desc) {
                $perm = SpatiePermission::firstOrCreate(
                    [
                        'name' => $name,
                        'guard_name' => 'admin',
                    ],
                    [
                        'description' => $desc,
                    ]
                );
                $count++;
                $this->info("Synced permission: $name");
            }
        }
        $this->info("Total permissions synced: $count");
        return 0;
    }
}
