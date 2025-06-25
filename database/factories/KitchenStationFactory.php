<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\KitchenStation;
use Illuminate\Database\Eloquent\Factories\Factory;

class KitchenStationFactory extends Factory
{
    protected $model = KitchenStation::class;

    public function definition(): array
    {
        $types = ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar'];
        $type = $this->faker->randomElement($types);
        $name = $this->getStationName($type);
        
        return [
            'branch_id' => Branch::factory(),
            'name' => $name,
            'code' => function (array $attributes) use ($type, $name) {
                $branchId = $attributes['branch_id'];
                return $this->generateUniqueStationCode($type, $branchId);
            },
            'type' => $type,
            'is_active' => $this->faker->boolean(90),
            'order_priority' => $this->faker->numberBetween(1, 10),
            'max_capacity' => $this->getCapacityByType($type),
            'description' => $this->getDescriptionByType($type),
            'printer_config' => [
                'printer_ip' => $this->faker->localIpv4(),
                'printer_name' => $name . ' Printer',
                'paper_size' => $this->faker->randomElement(['58mm', '80mm']),
                'auto_print' => $this->faker->boolean(30),
                'print_logo' => $this->faker->boolean(70),
                'print_quality' => $this->faker->randomElement(['standard', 'high']),
                'connection_timeout' => 5000,
                'retry_attempts' => 3
            ],
            'settings' => [
                'ui_icon' => $this->getIconByType($type),
                'ui_color' => $this->getColorByType($type),
                'dashboard_priority' => $this->getDashboardPriorityByType($type),
                'card_category' => $this->getCardCategoryByType($type),
                'notification_sound' => $this->faker->boolean(80),
                'auto_accept_orders' => $this->faker->boolean(40),
                'enable_status_updates' => true,
                'show_capacity_indicator' => true,
                'show_order_queue' => true,
                'enable_real_time_updates' => true,
                'mobile_optimized' => true,
                'compact_view_available' => true,
                'enable_hover_effects' => true,
                'transition_duration' => '300ms',
                'enable_loading_states' => true,
                'high_contrast_mode' => false,
                'screen_reader_support' => true,
                'keyboard_navigation' => true,
                'form_validation_real_time' => true,
                'show_helper_text' => true,
                'error_display_inline' => true
            ],
            'notes' => $this->faker->optional(0.6)->sentence()
        ];
    }

    private function generateUniqueStationCode(string $type, int $branchId): string
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
        $randomSuffix = $this->faker->numberBetween(100, 999);
        
        return $typePrefix . '-' . $branchCode . '-' . $randomSuffix;
    }

    private function getStationName(string $type): string
    {
        $names = [
            'cooking' => ['Hot Kitchen', 'Main Kitchen', 'Chef Station', 'Cooking Bay'],
            'prep' => ['Cold Station', 'Prep Area', 'Salad Station', 'Prep Kitchen'],
            'beverage' => ['Beverage Bar', 'Drink Station', 'Juice Counter', 'Beverage Hub'],
            'dessert' => ['Dessert Corner', 'Sweet Station', 'Pastry Section', 'Dessert Bay'],
            'grill' => ['Grill Station', 'BBQ Corner', 'Flame Grill', 'Char Station'],
            'fry' => ['Fry Station', 'Deep Fryer', 'Hot Oil Section', 'Fryer Bay'],
            'bar' => ['Main Bar', 'Cocktail Station', 'Wine Counter', 'Spirits Bar']
        ];
        
        return $this->faker->randomElement($names[$type] ?? $names['cooking']);
    }

    private function getCapacityByType(string $type): float
    {
        return match($type) {
            'cooking' => $this->faker->randomFloat(2, 40, 60),
            'grill' => $this->faker->randomFloat(2, 25, 40),
            'prep' => $this->faker->randomFloat(2, 20, 35),
            'beverage' => $this->faker->randomFloat(2, 15, 25),
            'dessert' => $this->faker->randomFloat(2, 10, 20),
            'fry' => $this->faker->randomFloat(2, 15, 30),
            'bar' => $this->faker->randomFloat(2, 20, 35),
            default => $this->faker->randomFloat(2, 25, 45)
        };
    }

    private function getDescriptionByType(string $type): string
    {
        return match($type) {
            'cooking' => 'Main cooking station for hot dishes and daily specials',
            'prep' => 'Cold food preparation area for salads and appetizers',
            'beverage' => 'Drink preparation station for juices, smoothies, and beverages',
            'dessert' => 'Dessert preparation area for sweets and pastries',
            'grill' => 'Grilling station for BBQ items and flame-cooked dishes',
            'fry' => 'Deep frying station for fried items and crispy preparations',
            'bar' => 'Bar station for alcoholic and specialty beverages',
            default => 'General purpose kitchen station'
        };
    }

    private function getIconByType(string $type): string
    {
        return match($type) {
            'cooking' => 'fas fa-fire',
            'prep' => 'fas fa-leaf',
            'beverage' => 'fas fa-coffee',
            'dessert' => 'fas fa-birthday-cake',
            'grill' => 'fas fa-utensils',
            'fry' => 'fas fa-drumstick-bite',
            'bar' => 'fas fa-wine-glass',
            default => 'fas fa-utensils'
        };
    }

    private function getColorByType(string $type): string
    {
        return match($type) {
            'cooking' => 'bg-red-600',
            'prep' => 'bg-green-600',
            'beverage' => 'bg-blue-600',
            'dessert' => 'bg-purple-600',
            'grill' => 'bg-orange-600',
            'fry' => 'bg-yellow-600',
            'bar' => 'bg-indigo-600',
            default => 'bg-gray-600'
        };
    }

    private function getCardCategoryByType(string $type): string
    {
        return match($type) {
            'cooking' => 'primary',
            'prep' => 'success',
            'beverage' => 'info',
            'dessert' => 'premium',
            'grill' => 'warning',
            'fry' => 'warning',
            'bar' => 'primary',
            default => 'default'
        };
    }

    private function getDashboardPriorityByType(string $type): int
    {
        return match($type) {
            'cooking' => 1,
            'grill' => 2,
            'prep' => 3,
            'fry' => 4,
            'beverage' => 5,
            'dessert' => 6,
            'bar' => 7,
            default => 5
        };
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false
        ]);
    }
}
