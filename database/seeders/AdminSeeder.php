<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds following UI/UX guidelines.
     */
    public function run(): void
    {
        $this->command->info('👤 Seeding admin users with role assignments...');

        // Check if we can use soft deletes
        $canUseSoftDeletes = Schema::hasColumn('admins', 'deleted_at');
        
        if (!$canUseSoftDeletes) {
            $this->command->warn('⚠️  deleted_at column not found. Using regular queries...');
        }

        // Ensure required roles exist
        $this->ensureRolesExist();
        
        // Create Super Admin
        $superAdmin = $this->createSuperAdmin($canUseSoftDeletes);
        
        // Create Organization Admins
        $this->createOrganizationAdmins($canUseSoftDeletes);
        
        // Create Branch Admins
        $this->createBranchAdmins($canUseSoftDeletes);

        $this->command->info('  ✅ Admin users seeded successfully with proper role assignments.');
    }

    /**
     * Ensure required roles exist
     */
    private function ensureRolesExist(): void
    {
        $roles = [
            'Super Admin' => 'System administrator with full access',
            'Organization Admin' => 'Organization-level administrator',
            'Branch Admin' => 'Branch-level administrator'
        ];
        
        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'admin'
            ]);
        }
    }

    /**
     * Create Super Admin following UI/UX guidelines
     */
    private function createSuperAdmin(bool $canUseSoftDeletes): Admin
    {
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();

        // Use appropriate query method based on soft delete availability
        if ($canUseSoftDeletes) {
            $superAdmin = Admin::withTrashed()->firstOrCreate(
                ['email' => 'superadmin@rms.com'],
                $this->getSuperAdminData()
            );
            
            // Restore if soft deleted
            if ($superAdmin->trashed()) {
                $superAdmin->restore();
            }
        } else {
            $superAdmin = Admin::firstOrCreate(
                ['email' => 'superadmin@rms.com'],
                $this->getSuperAdminData()
            );
        }

        // Update attributes
        $superAdmin->update($this->getSuperAdminData());

        // Assign role safely
        if ($superAdminRole && !$superAdmin->hasRole($superAdminRole)) {
            $superAdmin->assignRole($superAdminRole);
        }

        $this->command->info("    ✅ Super Admin created: {$superAdmin->email}");
        return $superAdmin;
    }

    /**
     * Get Super Admin data following UI/UX guidelines
     */
    private function getSuperAdminData(): array
    {
        return [
            'name' => 'System Super Admin',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'is_active' => true,
            'organization_id' => null,
            'branch_id' => null,
            'ui_settings' => [
                'theme' => 'light',
                'sidebar_collapsed' => false,
                'dashboard_layout' => 'grid',
                'notifications_enabled' => true,
                'preferred_language' => 'en',
                'show_all_organizations' => true,
            ],
            'preferences' => [
                'timezone' => 'Asia/Colombo',
                'date_format' => 'Y-m-d',
                'time_format' => '24h',
                'super_admin_mode' => true,
            ],
        ];
    }

    /**
     * Create Organization Admins following UI/UX guidelines
     */
    private function createOrganizationAdmins(bool $canUseSoftDeletes): void
    {
        $organizations = Organization::where('is_active', true)->get();
        $orgAdminRole = Role::where('name', 'Organization Admin')->where('guard_name', 'admin')->first();
        
        foreach ($organizations as $organization) {
            $adminEmail = 'admin@' . strtolower(str_replace([' ', '.', '-'], '', $organization->trading_name ?? $organization->name)) . '.com';
            
            $orgAdminData = [
                'name' => "Admin - {$organization->trading_name}",
                'password' => Hash::make('password123'),
                'organization_id' => $organization->id,
                'branch_id' => null,
                'is_super_admin' => false,
                'is_active' => true,
                'ui_settings' => [
                    'theme' => 'light',
                    'sidebar_collapsed' => false,
                    'dashboard_layout' => 'cards',
                    'show_organization_selector' => false,
                ],
                'preferences' => [
                    'timezone' => 'Asia/Colombo',
                    'default_organization_id' => $organization->id,
                ],
            ];

            if ($canUseSoftDeletes) {
                $orgAdmin = Admin::withTrashed()->firstOrCreate(['email' => $adminEmail], $orgAdminData);
                if ($orgAdmin->trashed()) {
                    $orgAdmin->restore();
                }
            } else {
                $orgAdmin = Admin::firstOrCreate(['email' => $adminEmail], $orgAdminData);
            }

            $orgAdmin->update($orgAdminData);

            // Assign role safely
            if ($orgAdminRole && !$orgAdmin->hasRole($orgAdminRole)) {
                $orgAdmin->assignRole($orgAdminRole);
            }

            $this->command->info("    ✅ Organization Admin created: {$orgAdmin->email} for {$organization->name}");
        }
    }

    /**
     * Create Branch Admins following UI/UX guidelines
     */
    private function createBranchAdmins(bool $canUseSoftDeletes): void
    {
        $branches = Branch::with('organization')->where('is_active', true)->take(3)->get();
        $branchAdminRole = Role::where('name', 'Branch Admin')->where('guard_name', 'admin')->first();

        foreach ($branches as $branch) {
            $adminEmail = 'branch.admin@' . 
                strtolower(str_replace([' ', '.', '-'], '', $branch->organization->trading_name ?? $branch->organization->name)) . 
                '.branch' . $branch->id . '.com';
            
            $branchAdminData = [
                'name' => "Branch Admin - {$branch->name}",
                'password' => Hash::make('password123'),
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'is_super_admin' => false,
                'is_active' => true,
                'ui_settings' => [
                    'theme' => 'light',
                    'sidebar_collapsed' => true,
                    'dashboard_layout' => 'compact',
                    'show_branch_selector' => false,
                ],
                'preferences' => [
                    'timezone' => 'Asia/Colombo',
                    'default_branch_id' => $branch->id,
                    'focus_mode' => true,
                ],
            ];

            if ($canUseSoftDeletes) {
                $branchAdmin = Admin::withTrashed()->firstOrCreate(['email' => $adminEmail], $branchAdminData);
                if ($branchAdmin->trashed()) {
                    $branchAdmin->restore();
                }
            } else {
                $branchAdmin = Admin::firstOrCreate(['email' => $adminEmail], $branchAdminData);
            }

            $branchAdmin->update($branchAdminData);

            // Assign role safely
            if ($branchAdminRole && !$branchAdmin->hasRole($branchAdminRole)) {
                $branchAdmin->assignRole($branchAdminRole);
            }

            $this->command->info("    ✅ Branch Admin created: {$branchAdmin->email} for {$branch->name}");
        }
    }
}
