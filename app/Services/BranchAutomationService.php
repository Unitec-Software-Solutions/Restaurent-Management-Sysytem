<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Admin;
use App\Models\KitchenStation;
use App\Models\CustomRole;
use App\Notifications\NewAdminWelcome;
use Database\Seeders\EnhancedPermissionSeeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class BranchAutomationService
{
    public function setupNewBranch(Branch $branch): void
    {
        // Skip automated setup for head office (handled by OrganizationObserver)
        if ($branch->is_head_office) {
            Log::info("Skipping branch setup for head office: {$branch->name}");
            return;
        }

        Log::info("BranchAutomationService: Starting automated setup for branch: {$branch->name}");

        DB::transaction(function () use ($branch) {
            try {
                Log::info("BranchAutomationService: Creating branch admin for: {$branch->name}");
                // Step 1: Create Branch Admin
                $branchAdmin = $this->createBranchAdmin($branch);
                Log::info("BranchAutomationService: Branch admin created: {$branchAdmin->email}");
                
                Log::info("BranchAutomationService: Creating kitchen stations for: {$branch->name}");
                // Step 2: Create Kitchen Stations based on branch type
                $this->createKitchenStationsForBranch($branch);
                Log::info("BranchAutomationService: Kitchen stations created for: {$branch->name}");
                
                // Step 3: Send Welcome Email to Branch Admin
                $this->sendWelcomeEmail($branchAdmin, $branch);
                
                Log::info("BranchAutomationService: Successfully created branch {$branch->name} with automated setup");
                
            } catch (\Exception $e) {
                Log::error("BranchAutomationService: Failed to create branch setup for {$branch->name}: " . $e->getMessage());
                Log::error("BranchAutomationService: Stack trace: " . $e->getTraceAsString());
                throw $e; // Re-throw to trigger transaction rollback
            }
        });
    }

    private function createBranchAdmin(Branch $branch): Admin
    {
        // Generate secure password
        $password = $this->generateSecurePassword();
        
        // Create branch admin user
        $admin = Admin::create([
            'name' => $branch->contact_person ?: ($branch->name . ' Admin'),
            'email' => $this->generateBranchAdminEmail($branch),
            'password' => Hash::make($password),
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        // Create and assign branch admin role
        $branchAdminRole = $this->createBranchAdminRole($branch);
        $admin->assignRole($branchAdminRole);
        
        // Store plain password for email
        $admin->temp_password = $password;
        
        return $admin;
    }

    private function createBranchAdminRole(Branch $branch): CustomRole
    {
        // Create Branch Admin role with branch-specific permissions
        $role = CustomRole::create([
            'name' => 'Branch Admin - ' . $branch->name,
            'guard_name' => 'admin',
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'scope' => 'branch',
        ]);

        // Get permissions for branch admin from template
        $permissions = EnhancedPermissionSeeder::getPermissionsForRole('branch_admin');
        
        if (!empty($permissions)) {
            $permissionModels = Permission::where('guard_name', 'admin')
                ->whereIn('name', $permissions)
                ->get();
            $role->syncPermissions($permissionModels);
        }

        return $role;
    }    private function createKitchenStationsForBranch(Branch $branch): void
    {
        $stations = $branch->getDefaultKitchenStations();
        
        // Customize stations based on branch type
        $stations = $this->customizeStationsByType($stations, $branch->type);
        
        $index = 1;
        foreach ($stations as $stationName => $config) {
            // Generate unique code for kitchen station
            $typePrefix = match($config['type']) {
                'cooking' => 'COOK',
                'prep' => 'PREP',
                'beverage' => 'BEV',
                'dessert' => 'DESS',
                'grill' => 'GRILL',
                'fry' => 'FRY',
                'bar' => 'BAR',
                default => 'MAIN'
            };
            
            $branchCode = str_pad($branch->id, 2, '0', STR_PAD_LEFT);
            $sequenceCode = str_pad($index, 3, '0', STR_PAD_LEFT);
            $code = $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
            
            // Ensure uniqueness
            $attempts = 0;
            while (KitchenStation::where('code', $code)->exists() && $attempts < 100) {
                $attempts++;
                $sequenceCode = str_pad($index + $attempts, 3, '0', STR_PAD_LEFT);
                $code = $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
            }
            
            KitchenStation::create([
                'branch_id' => $branch->id,
                'name' => $stationName,
                'code' => $code,
                'type' => $config['type'],
                'order_priority' => $config['priority'],
                'is_active' => true,
                'printer_config' => json_encode([
                    'printer_name' => null,
                    'paper_size' => 'thermal',
                    'auto_print' => false,
                    'branch_type' => $branch->type,
                ]),
                'notes' => "Auto-created for {$branch->type} branch",
            ]);
            
            $index++;
        }
    }

    private function customizeStationsByType(array $defaultStations, string $branchType): array
    {
        switch ($branchType) {
            case 'cafe':
                // Cafes focus more on beverages and light food
                return [
                    'Espresso Station' => ['type' => 'beverage', 'priority' => 1],
                    'Cold Brew Station' => ['type' => 'beverage', 'priority' => 2],
                    'Pastry Station' => ['type' => 'dessert', 'priority' => 3],
                    'Sandwich Station' => ['type' => 'prep', 'priority' => 4],
                ];
            
            case 'bar':
            case 'pub':
                // Bars focus on beverages and pub food
                return [
                    'Main Bar' => ['type' => 'bar', 'priority' => 1],
                    'Beer Station' => ['type' => 'beverage', 'priority' => 2],
                    'Cocktail Station' => ['type' => 'bar', 'priority' => 3],
                    'Kitchen Grill' => ['type' => 'grill', 'priority' => 4],
                    'Fry Station' => ['type' => 'fry', 'priority' => 5],
                ];
            
            case 'fast_food':
                // Fast food focuses on quick preparation
                return [
                    'Grill Station' => ['type' => 'grill', 'priority' => 1],
                    'Fry Station' => ['type' => 'fry', 'priority' => 2],
                    'Assembly Station' => ['type' => 'prep', 'priority' => 3],
                    'Beverage Station' => ['type' => 'beverage', 'priority' => 4],
                ];
            
            case 'fine_dining':
                // Fine dining needs specialized stations
                return [
                    'Hot Kitchen' => ['type' => 'cooking', 'priority' => 1],
                    'Cold Kitchen' => ['type' => 'prep', 'priority' => 2],
                    'Grill Station' => ['type' => 'grill', 'priority' => 3],
                    'Sauce Station' => ['type' => 'cooking', 'priority' => 4],
                    'Pastry Kitchen' => ['type' => 'dessert', 'priority' => 5],
                    'Plating Station' => ['type' => 'prep', 'priority' => 6],
                ];
            
            default: // 'restaurant' and others
                return $defaultStations;
        }
    }

    private function generateBranchAdminEmail(Branch $branch): string
    {
        $baseEmail = 'admin.' . Str::slug($branch->name) . '@' . $this->extractDomainFromOrgEmail($branch->organization->email);
        
        // Check if email already exists, append number if needed
        $counter = 1;
        $email = $baseEmail;
        while (Admin::where('email', $email)->exists()) {
            $email = str_replace('@', $counter . '@', $baseEmail);
            $counter++;
        }
        
        return $email;
    }

    private function extractDomainFromOrgEmail(string $orgEmail): string
    {
        $parts = explode('@', $orgEmail);
        return count($parts) > 1 ? $parts[1] : 'example.com';
    }

    private function sendWelcomeEmail(Admin $admin, Branch $branch): void
    {
        try {
            $admin->notify(new NewAdminWelcome($branch->organization, $admin->temp_password, $branch));
        } catch (\Exception $e) {
            Log::warning("BranchAutomationService: Failed to send welcome email to branch admin {$admin->email}: " . $e->getMessage());
            // Don't throw exception as this shouldn't break the creation process
        }
    }

    private function generateSecurePassword(): string
    {
        $length = 12;
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];
        
        $allChars = $uppercase . $lowercase . $numbers . $specialChars;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }
}
