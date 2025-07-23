<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemPermissionsSeeder extends Seeder
{
    /**
     * Seed all system permissions for the admin guard.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('  Starting SystemPermissionsSeeder...');

        // Clear cache first
        app()['cache']->forget(config('permission.cache.key'));

        // Don't truncate - just ensure permissions exist
        // This prevents breaking existing role-permission relationships

        $service = app(\App\Services\PermissionSystemService::class);
        $defs = $service->getPermissionDefinitions();
        $allPermissions = [];

        // Collect all permissions from definitions
        foreach ($defs as $category => $categoryData) {
            if (isset($categoryData['permissions'])) {
                foreach ($categoryData['permissions'] as $perm => $desc) {
                    $allPermissions[$perm] = [
                        'description' => $desc,
                        'category' => $category
                    ];
                }
            }
        }

        // Add legacy and sidebar/menu permissions
        $sidebarFiles = [
            app_path('View/Components/AdminSidebar.php'),
            app_path('View/Components/Sidebar.php')
        ];

        foreach ($sidebarFiles as $sidebarPath) {
            if (file_exists($sidebarPath)) {
                $code = file_get_contents($sidebarPath);
                preg_match_all('/permission[\'\"]?\s*=>\s*[\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
                foreach ($matches[1] as $perm) {
                    if (!isset($allPermissions[$perm])) {
                        $allPermissions[$perm] = [
                            'description' => ucwords(str_replace(['.', '_'], ' ', $perm)),
                            'category' => 'sidebar'
                        ];
                    }
                }
            }
        }

        // Scan blade files for @can/@canany usage
        $bladeFiles = glob(resource_path('views/**/*.blade.php'));
        foreach ($bladeFiles as $file) {
            if (file_exists($file)) {
                $code = file_get_contents($file);
                preg_match_all('/@can\([\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
                foreach ($matches[1] as $perm) {
                    if (!isset($allPermissions[$perm])) {
                        $allPermissions[$perm] = [
                            'description' => ucwords(str_replace(['.', '_'], ' ', $perm)),
                            'category' => 'blade'
                        ];
                    }
                }
            }
        }

        $created = [];
        $updated = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($allPermissions as $permissionName => $permissionData) {
                try {
                    $permission = Permission::firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => 'admin',
                    ]);

                    if ($permission->wasRecentlyCreated) {
                        $created[] = $permissionName . ' (id: ' . $permission->id . ')';
                    } else {
                        $updated[] = $permissionName . ' (id: ' . $permission->id . ')';
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to create permission '{$permissionName}': " . $e->getMessage();
                    Log::error("Permission creation failed", [
                        'permission' => $permissionName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Log results
            Log::info('  SystemPermissionsSeeder completed', [
                '  created_count' => count($created),
                '  updated_count' => count($updated),
                '  error_count' => count($errors),
                '  total_permissions' => count($allPermissions)
            ]);

            if (!empty($created)) {
                Log::info('  SystemPermissionsSeeder created permissions:', $created);
            }

            if (!empty($errors)) {
                Log::error('  SystemPermissionsSeeder errors:', $errors);
            }

            // Verify all permissions exist
            $existingPermissions = Permission::where('guard_name', 'admin')
                ->pluck('name')
                ->toArray();

            $missingPermissions = array_diff(array_keys($allPermissions), $existingPermissions);
            if (!empty($missingPermissions)) {
                Log::warning('  Missing permissions after seeding:', $missingPermissions);
            }

            // Debug dump of the permissions table after seeding
            $all = Permission::where('guard_name', 'admin')
                ->orderBy('id')
                ->get(['id', 'name', 'guard_name']);

            Log::debug('Permissions table after seeding:', $all->toArray());

            $this->command->info('  Created ' . count($created) . ' new permissions.');
            $this->command->info('  Updated ' . count($updated) . ' existing permissions.');
            $this->command->info('  Total permissions: ' . count($allPermissions));

            if (!empty($errors)) {
                $this->command->warn('  Encountered ' . count($errors) . ' errors. Check logs for details.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('  SystemPermissionsSeeder transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->command->error('  Permission seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
