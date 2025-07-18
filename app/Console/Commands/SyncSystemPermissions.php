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
        $allPermissions = [];

        // 1. Get from Permission::getSystemPermissions()
        foreach (\App\Models\Permission::getSystemPermissions() as $group) {
            foreach ($group as $perm => $desc) {
                $allPermissions[$perm] = $desc;
            }
        }

        // 2. Get from PermissionSystemService definitions
        $service = new \App\Services\PermissionSystemService();
        $defs = $service->getPermissionDefinitions();
        foreach ($defs as $cat) {
            if (isset($cat['permissions'])) {
                foreach ($cat['permissions'] as $perm => $desc) {
                    $allPermissions[$perm] = $desc;
                }
            }
        }

        // 3. Scan sidebar/menu definitions
        foreach ([app_path('View/Components/AdminSidebar.php'), app_path('View/Components/Sidebar.php')] as $sidebarPath) {
            if (file_exists($sidebarPath)) {
                $code = file_get_contents($sidebarPath);
                preg_match_all('/permission[\'\"]?\s*=>\s*[\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
                foreach ($matches[1] as $perm) {
                    $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
                }
            }
        }

        // 4. Scan Blade menu-item usage
        $bladeFiles = glob(resource_path('views/**/*.blade.php'));
        foreach ($bladeFiles as $file) {
            $code = file_get_contents($file);
            preg_match_all('/can\([\'\"]([^\'\"]+)[\'\"]\)/', $code, $matches);
            foreach ($matches[1] as $perm) {
                $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
            }
        }

        // 5. Sync all permissions
        $count = 0;
        foreach ($allPermissions as $name => $desc) {
            \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'admin'],
                ['description' => $desc]
            );
            $count++;
            $this->info("Synced permission: $name");
        }
        $this->info("Total permissions synced: $count");
        return 0;
    }
}
