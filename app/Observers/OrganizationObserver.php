<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\KitchenStation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrganizationObserver
{
    /**
     * Handle the Organization "created" event for Laravel + PostgreSQL + Tailwind CSS stack
     */
    public function created(Organization $organization): void
    {
        Log::info('Organization created event triggered', [
            'organization_id' => $organization->id,
            'name' => $organization->name
        ]);

        DB::transaction(function () use ($organization) {
            try {
                // Create head office branch
                $this->createHeadOfficeBranch($organization);

                Log::info('Head office branch created successfully', [
                    'organization_id' => $organization->id
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to create head office branch', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e; // Re-throw to rollback transaction
            }
        });
    }

    /**
     * Create head office branch with kitchen stations for PostgreSQL
     */
    private function createHeadOfficeBranch(Organization $organization): void
    {
        $headOfficeBranch = $organization->branches()->create([
            'name' => $organization->name . ' Head Office',
            'slug' => Str::slug($organization->name . ' head office'),
            'address' => $organization->address,
            'phone' => $organization->phone,
            'email' => $organization->email,
            'opening_time' => '09:00:00', // Set default opening time if not provided
            'closing_time' => '22:00:00', // Ensure closing_time is set
            'total_capacity' => 100,
            'max_capacity' => 50,
            'reservation_fee' => 0,
            'cancellation_fee' => 0,
            'activation_key' => Str::random(40),
            'is_active' => true,
            'is_head_office' => true,
            'type' => 'restaurant',
            'status' => 'inactive',
            'contact_person' => $organization->contact_person ?? 'Manager',
            'contact_person_designation' => 'Branch Manager',
            'contact_person_phone' => $organization->contact_person_phone ?? $organization->phone,
            'manager_name' => $organization->contact_person ?? 'Branch Manager',
            'manager_phone' => $organization->contact_person_phone ?? $organization->phone,
            'features' => json_encode([
                'dine_in' => true,
                'takeaway' => true,
                'delivery' => false,
                'reservations' => true,
                'online_ordering' => false
            ]),
            'settings' => json_encode([
                'timezone' => 'UTC',
                'currency' => 'USD',
                'tax_rate' => 0.0,
                'service_charge' => 0.0,
                'reservation_advance_days' => 30,
                'cancellation_hours_limit' => 24
            ]),
            'operating_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
                'tuesday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
                'wednesday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
                'thursday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
                'friday' => ['open' => '08:00', 'close' => '23:00', 'closed' => false],
                'saturday' => ['open' => '08:00', 'close' => '23:00', 'closed' => false],
                'sunday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false]
            ])
        ]);

        Log::info('Head office branch created', [
            'branch_id' => $headOfficeBranch->id,
            'branch_name' => $headOfficeBranch->name
        ]);

        // Create kitchen stations for the head office branch
        $this->createDefaultKitchenStations($headOfficeBranch);
    }

    /**
     * Create default kitchen stations for a branch using PostgreSQL
     */
    private function createDefaultKitchenStations(Branch $branch): void
    {
        try {
            $defaultStations = $branch->getDefaultKitchenStations();

            foreach ($defaultStations as $stationData) {
                $station = KitchenStation::create(array_merge($stationData, [
                    'branch_id' => $branch->id
                ]));

                Log::info('Kitchen station created', [
                    'station_id' => $station->id,
                    'station_name' => $station->name,
                    'branch_id' => $branch->id
                ]);
            }

            Log::info('All kitchen stations created successfully', [
                'branch_id' => $branch->id,
                'stations_count' => count($defaultStations)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create kitchen stations', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle the Organization "updated" event
     */
    public function updated(Organization $organization): void
    {
        Log::info('Organization updated', [
            'organization_id' => $organization->id,
            'name' => $organization->name
        ]);
    }

    /**
     * Handle the Organization "deleted" event
     */
    public function deleted(Organization $organization): void
    {
        Log::info('Organization deleted', [
            'organization_id' => $organization->id,
            'name' => $organization->name
        ]);
    }

    /**
     * Handle the Organization "restored" event
     */
    public function restored(Organization $organization): void
    {
        Log::info('Organization restored', [
            'organization_id' => $organization->id,
            'name' => $organization->name
        ]);
    }

    /**
     * Handle the Organization "force deleted" event
     */
    public function forceDeleted(Organization $organization): void
    {
        Log::info('Organization force deleted', [
            'organization_id' => $organization->id,
            'name' => $organization->name
        ]);
    }
}
