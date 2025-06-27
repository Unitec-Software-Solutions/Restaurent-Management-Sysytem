<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\User;
use App\Models\KitchenStation;
use App\Models\MenuCategory;
use App\Models\InventoryItem;
use App\Models\CustomRole;
use App\Notifications\NewAdminWelcome;
use Database\Seeders\EnhancedPermissionSeeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class OrganizationObserver
{
    public function creating(Organization $organization)
    {
        $organization->activation_key = Str::random(40);
    }

    public function created(Organization $organization)
    {
        DB::transaction(function () use ($organization) {
            try {
                // Step 1: Create Head Office Branch
                $headOffice = $this->createHeadOfficeBranch($organization);
                
                // Step 2: Create Organization Admin
                $orgAdmin = $this->createOrganizationAdmin($organization, $headOffice);
                
                // Step 3: Create Default Kitchen Stations for Head Office
                $this->createDefaultKitchenStations($headOffice);
                
                // Step 4: Create Default Menu Categories (skipped - no org relationship)
                // $this->createDefaultMenuCategories($organization);
                
                // Step 5: Create Inventory Starter Kit (skipped - inventory system not set up)
                // $this->createInventoryStarterKit($organization, $headOffice);
                
                // Step 6: Send Welcome Email
                $this->sendWelcomeEmail($orgAdmin, $organization);
                
                Log::info("Successfully created organization {$organization->name} with automated setup");
                
            } catch (\Exception $e) {
                Log::error("Failed to create organization setup: " . $e->getMessage());
                throw $e; // Re-throw to trigger transaction rollback
            }
        });
    }

    private function createHeadOfficeBranch(Organization $organization): Branch
    {
        return $organization->branches()->create([
            'name' => $organization->name . ' Head Office',
            'slug' => Str::slug($organization->name . '-head-office'),
            'type' => 'restaurant', // Default to restaurant
            'is_head_office' => true,
            'address' => $organization->address,
            'phone' => $organization->phone,
            'contact_person' => $organization->contact_person,
            'contact_person_designation' => $organization->contact_person_designation,
            'contact_person_phone' => $organization->contact_person_phone,
            'opening_time' => '08:00:00',
            'closing_time' => '22:00:00',
            'total_capacity' => 100,
            'reservation_fee' => 0.00,
            'cancellation_fee' => 0.00,
            'activation_key' => Str::random(40),
            'is_active' => true,
        ]);
    }

    private function createOrganizationAdmin(Organization $organization, Branch $headOffice): Admin
    {
        // Generate secure password
        $password = $this->generateSecurePassword();
        
        // Create admin user
        $admin = Admin::create([
            'name' => $organization->contact_person ?: 'Organization Admin',
            'email' => $organization->email,
            'password' => Hash::make($password),
            'organization_id' => $organization->id,
            'branch_id' => null, // Org admin has access to all branches
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        // Create and assign organization admin role
        $adminRole = $this->createOrganizationAdminRole($organization);
        $admin->assignRole($adminRole);
        
        // Store plain password for email (will be sent securely)
        $admin->temp_password = $password;
        
        return $admin;
    }

    private function createOrganizationAdminRole(Organization $organization): CustomRole
    {
        // Create Organization Admin role with full permissions
        $role = CustomRole::firstOrCreate([
            'name' => 'Organization Admin - ' . $organization->name,
            'guard_name' => 'admin',
            'organization_id' => $organization->id,
        ], [
            'branch_id' => null,
            'scope' => 'organization',
        ]);

        // Get permissions for org admin from template
        $permissions = EnhancedPermissionSeeder::getPermissionsForRole('org_admin');
        
        if (!empty($permissions)) {
            $permissionModels = Permission::where('guard_name', 'admin')
                ->whereIn('name', $permissions)
                ->get();
            $role->syncPermissions($permissionModels);
        }

        return $role;
    }

    private function createDefaultKitchenStations(Branch $branch): void
    {
        $defaultStations = $branch->getDefaultKitchenStations();
        
        $index = 1;
        foreach ($defaultStations as $stationName => $config) {
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
                    'paper_size' => 'A4',
                    'auto_print' => false,
                ]),
                'notes' => 'Auto-created default station',
            ]);
            
            $index++;
        }
    }

    private function createDefaultMenuCategories(Organization $organization): void
    {
        $defaultCategories = [
            'Appetizers' => 'Starters and small plates',
            'Main Courses' => 'Primary dishes and entrees',
            'Desserts' => 'Sweet treats and desserts',
            'Beverages' => 'Drinks and refreshments',
            'Specials' => 'Daily specials and seasonal items',
        ];

        foreach ($defaultCategories as $categoryName => $description) {
            MenuCategory::firstOrCreate([
                'organization_id' => $organization->id,
                'name' => $categoryName,
            ], [
                'description' => $description,
                'is_active' => true,
                'sort_order' => array_search($categoryName, array_keys($defaultCategories)) + 1,
            ]);
        }
    }

    private function createInventoryStarterKit(Organization $organization, Branch $branch): void
    {
        $starterItems = [
            // Basic ingredients
            ['name' => 'Salt', 'category' => 'Seasonings', 'unit' => 'kg', 'cost_per_unit' => 2.50],
            ['name' => 'Black Pepper', 'category' => 'Seasonings', 'unit' => 'kg', 'cost_per_unit' => 15.00],
            ['name' => 'Olive Oil', 'category' => 'Oils', 'unit' => 'liter', 'cost_per_unit' => 8.00],
            ['name' => 'Onions', 'category' => 'Vegetables', 'unit' => 'kg', 'cost_per_unit' => 1.50],
            ['name' => 'Garlic', 'category' => 'Vegetables', 'unit' => 'kg', 'cost_per_unit' => 4.00],
            
            // Basic proteins
            ['name' => 'Chicken Breast', 'category' => 'Proteins', 'unit' => 'kg', 'cost_per_unit' => 12.00],
            ['name' => 'Ground Beef', 'category' => 'Proteins', 'unit' => 'kg', 'cost_per_unit' => 10.00],
            
            // Dairy
            ['name' => 'Milk', 'category' => 'Dairy', 'unit' => 'liter', 'cost_per_unit' => 1.20],
            ['name' => 'Butter', 'category' => 'Dairy', 'unit' => 'kg', 'cost_per_unit' => 6.00],
            ['name' => 'Cheese (Cheddar)', 'category' => 'Dairy', 'unit' => 'kg', 'cost_per_unit' => 8.50],
        ];

        foreach ($starterItems as $item) {
            InventoryItem::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'name' => $item['name'],
                'category' => $item['category'],
                'unit_of_measure' => $item['unit'],
                'cost_per_unit' => $item['cost_per_unit'],
                'current_stock' => 0,
                'minimum_stock' => 5,
                'maximum_stock' => 100,
                'is_active' => true,
            ]);
        }
    }

    private function sendWelcomeEmail(Admin $admin, Organization $organization): void
    {
        try {
            $admin->notify(new NewAdminWelcome($organization, $admin->temp_password));
        } catch (\Exception $e) {
            Log::warning("Failed to send welcome email to {$admin->email}: " . $e->getMessage());
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