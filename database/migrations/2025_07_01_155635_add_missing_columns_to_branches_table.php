<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS stack
     */
    public function up(): void
    {
        try {
            Schema::table('branches', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('branches');
                Log::info('Existing branches table columns:', $existingColumns);

                // Add opening_time column if it doesn't exist
                if (!in_array('opening_time', $existingColumns)) {
                    $table->time('opening_time')->default('09:00:00')->nullable();
                    Log::info('Added opening_time column to branches table');
                }
                
                // Add closing_time column if it doesn't exist
                if (!in_array('closing_time', $existingColumns)) {
                    $table->time('closing_time')->default('22:00:00')->nullable();
                    Log::info('Added closing_time column to branches table');
                }
                
                // Add max_capacity column if it doesn't exist
                if (!in_array('max_capacity', $existingColumns)) {
                    $table->integer('max_capacity')->default(50)->nullable();
                    Log::info('Added max_capacity column to branches table');
                }

                // Add status column (required by OrganizationObserver)
                if (!in_array('status', $existingColumns)) {
                    $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('inactive');
                    Log::info('Added status column to branches table');
                }

                // Add type column if it doesn't exist
                if (!in_array('type', $existingColumns)) {
                    $table->string('type')->default('restaurant');
                    Log::info('Added type column to branches table');
                }

                // Add is_head_office column if it doesn't exist
                if (!in_array('is_head_office', $existingColumns)) {
                    $table->boolean('is_head_office')->default(false);
                    Log::info('Added is_head_office column to branches table');
                }

                // Add slug column if it doesn't exist
                if (!in_array('slug', $existingColumns)) {
                    $table->string('slug')->nullable();
                    Log::info('Added slug column to branches table');
                }

                // Add contact person fields if they don't exist
                if (!in_array('contact_person', $existingColumns)) {
                    $table->string('contact_person')->nullable();
                    Log::info('Added contact_person column to branches table');
                }

                if (!in_array('contact_person_designation', $existingColumns)) {
                    $table->string('contact_person_designation')->nullable();
                    Log::info('Added contact_person_designation column to branches table');
                }

                if (!in_array('contact_person_phone', $existingColumns)) {
                    $table->string('contact_person_phone')->nullable();
                    Log::info('Added contact_person_phone column to branches table');
                }

                // Add additional useful columns for restaurant management
                if (!in_array('features', $existingColumns)) {
                    $table->json('features')->nullable();
                    Log::info('Added features column to branches table');
                }

                if (!in_array('settings', $existingColumns)) {
                    $table->json('settings')->nullable();
                    Log::info('Added settings column to branches table');
                }

                // Add operational timing fields
                if (!in_array('opened_at', $existingColumns)) {
                    $table->timestamp('opened_at')->nullable();
                    Log::info('Added opened_at column to branches table');
                }

                if (!in_array('activated_at', $existingColumns)) {
                    $table->timestamp('activated_at')->nullable();
                    Log::info('Added activated_at column to branches table');
                }

                // Add manager information
                if (!in_array('manager_name', $existingColumns)) {
                    $table->string('manager_name')->nullable();
                    Log::info('Added manager_name column to branches table');
                }

                if (!in_array('manager_phone', $existingColumns)) {
                    $table->string('manager_phone')->nullable();
                    Log::info('Added manager_phone column to branches table');
                }

                // Add operating hours as JSON for PostgreSQL
                if (!in_array('operating_hours', $existingColumns)) {
                    $table->json('operating_hours')->nullable();
                    Log::info('Added operating_hours column to branches table');
                }

                // Add code column if it doesn't exist
                if (!in_array('code', $existingColumns)) {
                    $table->string('code')->nullable();
                    Log::info('Added code column to branches table');
                }
            });

            // Update existing branches with default values
            $this->updateExistingBranches();
            
            Log::info('Successfully updated branches table structure for Laravel + PostgreSQL + Tailwind CSS');

        } catch (\Exception $e) {
            Log::error('Error updating branches table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update existing branches with default values for PostgreSQL
     */
    private function updateExistingBranches(): void
    {
        try {
            // Update branches that don't have required values set
            $updates = [];

            // Set default opening/closing times
            $updates['opening_time'] = '09:00:00';
            $updates['closing_time'] = '22:00:00';
            $updates['max_capacity'] = 50;
            $updates['status'] = 'active';
            $updates['type'] = 'restaurant';

            // Set default features for existing branches (PostgreSQL JSON)
            $defaultFeatures = json_encode([
                'dine_in' => true,
                'takeaway' => true,
                'delivery' => false,
                'reservations' => true,
                'online_ordering' => false,
                'parking' => true
            ]);

            $updates['features'] = $defaultFeatures;

            // Set default settings for existing branches (PostgreSQL JSON)
            $defaultSettings = json_encode([
                'timezone' => 'UTC',
                'currency' => 'USD',
                'tax_rate' => 0.0,
                'service_charge' => 0.0,
                'reservation_advance_days' => 30,
                'cancellation_hours_limit' => 24,
                'auto_confirm_reservations' => false
            ]);

            $updates['settings'] = $defaultSettings;

            // Set default operating hours (PostgreSQL JSON)
            $defaultOperatingHours = json_encode([
                'monday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                'tuesday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                'wednesday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                'thursday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                'friday' => ['open' => '09:00', 'close' => '23:00', 'closed' => false],
                'saturday' => ['open' => '09:00', 'close' => '23:00', 'closed' => false],
                'sunday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false]
            ]);

            $updates['operating_hours'] = $defaultOperatingHours;

            // Update all existing branches
            $affectedRows = DB::table('branches')->update($updates);

            // Generate slugs for existing branches
            $branches = DB::table('branches')->whereNull('slug')->get();
            foreach ($branches as $branch) {
                $slug = \Illuminate\Support\Str::slug($branch->name);
                DB::table('branches')
                    ->where('id', $branch->id)
                    ->update(['slug' => $slug]);
            }

            Log::info("Updated {$affectedRows} existing branches with default values");

        } catch (\Exception $e) {
            Log::warning('Could not update existing branches: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        try {
            Schema::table('branches', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('branches');
                
                $columnsToRemove = [
                    'opening_time', 'closing_time', 'max_capacity', 'status', 
                    'type', 'is_head_office', 'slug', 'contact_person', 
                    'contact_person_designation', 'contact_person_phone',
                    'features', 'settings', 'opened_at', 'activated_at',
                    'manager_name', 'manager_phone', 'operating_hours', 'code'
                ];

                foreach ($columnsToRemove as $column) {
                    if (in_array($column, $existingColumns)) {
                        $table->dropColumn($column);
                    }
                }
            });

            Log::info('Reverted branches table changes');

        } catch (\Exception $e) {
            Log::error('Error reverting branches table: ' . $e->getMessage());
            throw $e;
        }
    }
};
