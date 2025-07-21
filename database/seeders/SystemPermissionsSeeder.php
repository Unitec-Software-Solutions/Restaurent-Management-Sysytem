<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SystemPermissionsSeeder extends Seeder
{
    /**
     * Seed all system permissions for the admin guard.
     *
     * @return void
     */
    public function run()
    {
        
        $service = app(\App\Services\PermissionSystemService::class);
        $defs = $service->getPermissionDefinitions();
        $allPermissions = [];
        foreach ($defs as $cat) {
            if (isset($cat['permissions'])) {
                foreach ($cat['permissions'] as $perm => $desc) {
                    $allPermissions[$perm] = $desc;
                }
            }
        }

        // Add legacy and sidebar/menu permissions
        $sidebarFiles = [app_path('View/Components/AdminSidebar.php'), app_path('View/Components/Sidebar.php')];
        foreach ($sidebarFiles as $sidebarPath) {
            if (file_exists($sidebarPath)) {
                $code = file_get_contents($sidebarPath);
                preg_match_all('/permission[\'\"]?\s*=>\s*[\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
                foreach ($matches[1] as $perm) {
                    $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
                }
            }
        }

        // Scan blade files for @can/@canany usage
        $bladeFiles = glob(resource_path('views/**/*.blade.php'));
        foreach ($bladeFiles as $file) {
            $code = file_get_contents($file);
            preg_match_all('/@can\([\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
            foreach ($matches[1] as $perm) {
                $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
            }
        }

        foreach (array_keys($allPermissions) as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }
    }
}
