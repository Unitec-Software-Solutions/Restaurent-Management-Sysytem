<?php
// filepath: database/seeders/KitchenStationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\KitchenStation;
use App\Models\Branch;
use App\Services\SeederValidationService;
use Exception;

class KitchenStationSeeder extends Seeder
{
    protected SeederValidationService $validator;
    protected array $stationTemplates;

    public function __construct()
    {
        $this->validator = new SeederValidationService();
        $this->initializeStationTemplates();
    }

    public function run(): void
    {
        $this->command->info('🏭 Starting Kitchen Stations seeding with comprehensive validation...');

        // Use safe seeding with transaction protection
        $result = $this->validator->safeSeed(
            [$this, 'seedKitchenStations'],
            'KitchenStationSeeder'
        );

        if ($result['success']) {
            $this->command->info($result['message']);
            $this->command->info("📊 Summary: {$result['data']['total_created']} stations created across {$result['data']['branches_processed']} branches");
        } else {
            $this->command->error($result['message']);
            throw new Exception("Kitchen station seeding failed: " . $result['message']);
        }
    }

    /**
     * Main seeding logic with validation
     */
    public function seedKitchenStations(): array
    {
        $branches = Branch::all();
        $totalCreated = 0;
        $branchesProcessed = 0;

        if ($branches->isEmpty()) {
            throw new Exception("No branches found. Please run BranchSeeder first.");
        }

        foreach ($branches as $branch) {
            // Check if this branch already has kitchen stations
            if ($branch->kitchenStations()->exists()) {
                $this->command->info("  ⏭️ Branch {$branch->name} already has kitchen stations, skipping...");
                continue;
            }

            $stationsData = $this->generateStationsForBranch($branch);
            
            // Validate data before seeding
            $validation = $this->validator->validateBeforeSeeding('KitchenStationSeeder', $stationsData);
            
            if (!$validation['valid']) {
                $errorMsg = "Validation failed for branch {$branch->name}: " . implode(', ', $validation['errors']);
                Log::error($errorMsg);
                throw new Exception($errorMsg);
            }

            // Create stations with individual error handling
            $stationsCreated = $this->createStationsForBranch($branch, $stationsData);
            $totalCreated += $stationsCreated;
            $branchesProcessed++;

            $this->command->info("    ✅ Created {$stationsCreated} kitchen stations for {$branch->name}");
        }

        return [
            'total_created' => $totalCreated,
            'branches_processed' => $branchesProcessed
        ];
    }

    /**
     * Generate station data for a branch
     */
    protected function generateStationsForBranch(Branch $branch): array
    {
        $stations = [];

        foreach ($this->stationTemplates as $index => $template) {
            $code = $this->generateUniqueStationCode($template['type'], $branch->id, $index + 1);
            
            $stations[] = [
                'branch_id' => $branch->id,
                'name' => $template['name'],
                'code' => $code,
                'type' => $template['type'],
                'order_priority' => $template['order_priority'],
                'max_capacity' => $template['max_capacity'],
                'description' => $template['description'],
                'is_active' => true,
                'printer_config' => $this->generatePrinterConfig($template, $index),
                'settings' => $this->generateStationSettings($template),
                'notes' => null
            ];
        }

        return $stations;
    }

    /**
     * Create stations for a branch with error handling
     */
    protected function createStationsForBranch(Branch $branch, array $stationsData): int
    {
        $created = 0;

        foreach ($stationsData as $stationData) {
            try {
                // Double-check code uniqueness before creation
                $this->ensureCodeUniqueness($stationData['code']);
                
                KitchenStation::create($stationData);
                $created++;
                
            } catch (Exception $e) {
                Log::error("Failed to create kitchen station for branch {$branch->id}", [
                    'station_data' => $stationData,
                    'error' => $e->getMessage()
                ]);
                
                // Try with a different code
                $stationData['code'] = $this->generateUniqueStationCode(
                    $stationData['type'], 
                    $branch->id, 
                    rand(100, 999)
                );
                
                try {
                    KitchenStation::create($stationData);
                    $created++;
                    Log::info("Successfully created station with alternative code: {$stationData['code']}");
                } catch (Exception $retryException) {
                    Log::error("Failed to create station even with alternative code", [
                        'final_attempt' => $stationData,
                        'error' => $retryException->getMessage()
                    ]);
                    throw $retryException;
                }
            }
        }

        return $created;
    }

    /**
     * Generate unique station code with collision detection
     */
    protected function generateUniqueStationCode(string $type, int $branchId, int $sequence): string
    {
        $typePrefix = match($type) {
            'cooking' => 'COOK',
            'prep' => 'PREP',
            'beverage' => 'BEV',
            'dessert' => 'DESS',
            'grill' => 'GRILL',
            'fry' => 'FRY',
            'bar' => 'BAR',
            default => 'MAIN'
        };

        $branchCode = str_pad($branchId, 2, '0', STR_PAD_LEFT);
        
        // Try sequence numbers until we find a unique one
        $attempts = 0;
        do {
            $sequenceCode = str_pad($sequence + $attempts, 3, '0', STR_PAD_LEFT);
            $code = $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
            $exists = KitchenStation::where('code', $code)->exists();
            $attempts++;
        } while ($exists && $attempts < 100);

        if ($exists) {
            // Last resort: use timestamp
            $code = $typePrefix . '-' . $branchCode . '-' . substr(time(), -3);
        }

        return $code;
    }

    /**
     * Ensure code uniqueness before creation
     */
    protected function ensureCodeUniqueness(string $code): void
    {
        if (KitchenStation::where('code', $code)->exists()) {
            throw new Exception("Kitchen station code '{$code}' already exists");
        }
    }

    /**
     * Generate printer configuration following UI/UX guidelines
     */
    protected function generatePrinterConfig(array $template, int $index): array
    {
        return [
            'printer_ip' => '192.168.1.' . (100 + $index + 1),
            'printer_name' => $template['name'] . ' Printer',
            'paper_size' => $template['paper_size'] ?? '80mm',
            'auto_print' => $template['auto_print'] ?? false,
            'print_logo' => $template['print_logo'] ?? true,
            'print_quality' => $template['print_quality'] ?? 'standard',
            'connection_timeout' => 5000,
            'retry_attempts' => 3
        ];
    }

    /**
     * Generate station settings following UI/UX design system
     */
    protected function generateStationSettings(array $template): array
    {
        return [
            // UI/UX Design System Integration
            'ui_icon' => $template['ui_icon'],
            'ui_color' => $template['ui_color'],
            'dashboard_priority' => $template['dashboard_priority'],
            'card_category' => $template['card_category'],
            
            // Interactive Features
            'notification_sound' => true,
            'auto_accept_orders' => false,
            'enable_status_updates' => true,
            
            // Card Display Settings (responsive grid)
            'show_capacity_indicator' => true,
            'show_order_queue' => true,
            'enable_real_time_updates' => true,
            
            // Responsive Design Support
            'mobile_optimized' => true,
            'compact_view_available' => true,
            
            // Animation Preferences (300ms transitions)
            'enable_hover_effects' => true,
            'transition_duration' => '300ms',
            'enable_loading_states' => true,
            
            // Accessibility Standards
            'high_contrast_mode' => false,
            'screen_reader_support' => true,
            'keyboard_navigation' => true,
            
            // Form Standards Integration
            'form_validation_real_time' => true,
            'show_helper_text' => true,
            'error_display_inline' => true
        ];
    }

    /**
     * Initialize station templates with UI/UX design system
     */
    protected function initializeStationTemplates(): void
    {
        $this->stationTemplates = [
            [
                'name' => 'Main Kitchen',
                'type' => 'cooking',
                'order_priority' => 1,
                'max_capacity' => 50.00,
                'description' => 'Primary cooking station for hot dishes and daily specials',
                'ui_icon' => 'fas fa-fire',
                'ui_color' => 'bg-red-600',
                'dashboard_priority' => 1,
                'card_category' => 'primary',
                'paper_size' => '80mm',
                'auto_print' => false,
                'print_logo' => true,
                'print_quality' => 'high'
            ],
            [
                'name' => 'Grill Station',
                'type' => 'grill',
                'order_priority' => 2,
                'max_capacity' => 30.00,
                'description' => 'Specialized grilling station for BBQ items and flame-cooked dishes',
                'ui_icon' => 'fas fa-utensils',
                'ui_color' => 'bg-orange-600',
                'dashboard_priority' => 2,
                'card_category' => 'secondary',
                'paper_size' => '80mm',
                'auto_print' => false,
                'print_logo' => true,
                'print_quality' => 'standard'
            ],
            [
                'name' => 'Cold Prep',
                'type' => 'prep',
                'order_priority' => 3,
                'max_capacity' => 25.00,
                'description' => 'Cold food preparation area for salads and appetizers',
                'ui_icon' => 'fas fa-leaf',
                'ui_color' => 'bg-green-600',
                'dashboard_priority' => 3,
                'card_category' => 'success',
                'paper_size' => '58mm',
                'auto_print' => true,
                'print_logo' => false,
                'print_quality' => 'standard'
            ],
            [
                'name' => 'Beverage Station',
                'type' => 'beverage',
                'order_priority' => 4,
                'max_capacity' => 20.00,
                'description' => 'Drinks and beverage preparation station',
                'ui_icon' => 'fas fa-coffee',
                'ui_color' => 'bg-blue-600',
                'dashboard_priority' => 4,
                'card_category' => 'info',
                'paper_size' => '58mm',
                'auto_print' => true,
                'print_logo' => false,
                'print_quality' => 'standard'
            ]
        ];
    }
}