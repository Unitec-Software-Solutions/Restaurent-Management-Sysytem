<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Models\Payment;
use Carbon\Carbon;

/**
 * Order Validation Matrix Service
 * 
 * This service provides comprehensive validation scenarios for orders
 * including minimum order validation, kitchen capacity checks,
 * dietary compliance, and payment method validation per order type.
 */
class OrderValidationMatrixService
{
    /**
     * Validation matrix configuration
     */
    private $validationMatrix = [
        'minimum_order' => [
            'takeaway' => 15.00,
            'dine_in' => 10.00,
            'delivery' => 20.00,
        ],
        'kitchen_capacity' => [
            'max_concurrent_orders' => 50,
            'max_items_per_order' => 20,
            'rush_hour_multiplier' => 0.7, // Reduce capacity during rush hours
            'peak_hours' => [
                ['start' => '12:00', 'end' => '14:00'], // Lunch
                ['start' => '18:00', 'end' => '21:00'], // Dinner
            ],
        ],
        'dietary_compliance' => [
            'allergen_warnings' => [
                'nuts', 'dairy', 'gluten', 'soy', 'eggs', 'shellfish', 'fish'
            ],
            'dietary_flags' => [
                'vegetarian', 'vegan', 'gluten_free', 'dairy_free', 'nut_free'
            ],
            'spice_levels' => ['mild', 'medium', 'hot', 'extra_hot'],
        ],
        'payment_methods' => [
            'takeaway' => ['cash', 'credit_card', 'debit_card', 'mobile_payment'],
            'dine_in' => ['cash', 'credit_card', 'debit_card', 'mobile_payment', 'split_payment'],
            'delivery' => ['credit_card', 'debit_card', 'mobile_payment', 'cash_on_delivery'],
        ],
        'order_timing' => [
            'preparation_times' => [
                'appetizer' => 10, // minutes
                'main_course' => 25,
                'dessert' => 15,
                'beverage' => 5,
                'side_dish' => 12,
            ],
            'delivery_times' => [
                'takeaway' => [15, 30], // min, max minutes
                'dine_in' => [20, 45],
                'delivery' => [30, 60],
            ],
        ],
    ];

    /**
     * Validate minimum order amount
     */
    public function validateMinimumOrder(Order $order): array
    {
        $orderType = $order->order_type;
        $minimumAmount = $this->validationMatrix['minimum_order'][$orderType] ?? 0;
        
        $isValid = $order->total_price >= $minimumAmount;
        
        return [
            'valid' => $isValid,
            'rule' => "Minimum order for {$orderType}",
            'required' => $minimumAmount,
            'actual' => $order->total_price,
            'message' => $isValid 
                ? 'Order meets minimum requirement' 
                : "Order must be at least $" . number_format($minimumAmount, 2),
            'scenario' => 'minimum_order_validation'
        ];
    }

    /**
     * Validate kitchen capacity constraints
     */
    public function validateKitchenCapacity(Order $order, $currentOrderCount = null): array
    {
        $config = $this->validationMatrix['kitchen_capacity'];
        $currentHour = Carbon::now()->format('H:i');
        
        // Check if it's rush hour
        $isRushHour = collect($config['peak_hours'])->some(function($period) use ($currentHour) {
            return $currentHour >= $period['start'] && $currentHour <= $period['end'];
        });
        
        $maxCapacity = $config['max_concurrent_orders'];
        if ($isRushHour) {
            $maxCapacity = (int) ($maxCapacity * $config['rush_hour_multiplier']);
        }
        
        // Get current order count if not provided
        if ($currentOrderCount === null) {
            $currentOrderCount = Order::whereIn('status', ['pending', 'confirmed', 'preparing'])
                                    ->where('branch_id', $order->branch_id)
                                    ->count();
        }
        
        $itemCount = $order->orderItems->sum('quantity');
        
        $capacityValid = $currentOrderCount < $maxCapacity;
        $itemCountValid = $itemCount <= $config['max_items_per_order'];
        
        return [
            'valid' => $capacityValid && $itemCountValid,
            'capacity_check' => [
                'valid' => $capacityValid,
                'current_orders' => $currentOrderCount,
                'max_capacity' => $maxCapacity,
                'is_rush_hour' => $isRushHour,
            ],
            'item_count_check' => [
                'valid' => $itemCountValid,
                'item_count' => $itemCount,
                'max_items' => $config['max_items_per_order'],
            ],
            'message' => $capacityValid && $itemCountValid 
                ? 'Kitchen capacity available' 
                : 'Kitchen at capacity or too many items',
            'scenario' => 'kitchen_capacity_validation'
        ];
    }

    /**
     * Validate dietary compliance
     */
    public function validateDietaryCompliance(Order $order, array $customerRequirements = []): array
    {
        $violations = [];
        $warnings = [];
        $compliance = [];
        
        foreach ($order->orderItems as $orderItem) {
            $item = $orderItem->itemMaster;
            
            // Check allergen conflicts
            foreach ($this->validationMatrix['dietary_compliance']['allergen_warnings'] as $allergen) {
                $allergenField = "contains_{$allergen}";
                if (isset($item->$allergenField) && $item->$allergenField) {
                    if (in_array($allergen, $customerRequirements['avoid_allergens'] ?? [])) {
                        $violations[] = [
                            'item' => $item->name,
                            'allergen' => $allergen,
                            'severity' => 'high'
                        ];
                    } else {
                        $warnings[] = [
                            'item' => $item->name,
                            'allergen' => $allergen,
                            'severity' => 'low'
                        ];
                    }
                }
            }
            
            // Check dietary preferences
            foreach ($this->validationMatrix['dietary_compliance']['dietary_flags'] as $flag) {
                $flagField = "is_{$flag}";
                if (isset($item->$flagField) && $item->$flagField) {
                    $compliance[] = [
                        'item' => $item->name,
                        'dietary_flag' => $flag,
                        'compliant' => true
                    ];
                }
            }
            
            // Check spice level
            if (isset($customerRequirements['max_spice_level'])) {
                $spiceLevels = $this->validationMatrix['dietary_compliance']['spice_levels'];
                $maxLevel = array_search($customerRequirements['max_spice_level'], $spiceLevels);
                $itemLevel = array_search($item->spice_level, $spiceLevels);
                
                if ($itemLevel !== false && $maxLevel !== false && $itemLevel > $maxLevel) {
                    $warnings[] = [
                        'item' => $item->name,
                        'issue' => 'spice_level_too_high',
                        'item_spice' => $item->spice_level,
                        'max_preferred' => $customerRequirements['max_spice_level'],
                        'severity' => 'medium'
                    ];
                }
            }
        }
        
        return [
            'valid' => empty($violations),
            'violations' => $violations,
            'warnings' => $warnings,
            'compliance' => $compliance,
            'message' => empty($violations) 
                ? 'Dietary requirements met' 
                : 'Dietary violations detected',
            'scenario' => 'dietary_compliance_validation'
        ];
    }

    /**
     * Validate payment method for order type
     */
    public function validatePaymentMethod(Order $order, string $paymentMethod): array
    {
        $orderType = $order->order_type;
        $allowedMethods = $this->validationMatrix['payment_methods'][$orderType] ?? [];
        
        $isValid = in_array($paymentMethod, $allowedMethods);
        
        return [
            'valid' => $isValid,
            'payment_method' => $paymentMethod,
            'order_type' => $orderType,
            'allowed_methods' => $allowedMethods,
            'message' => $isValid 
                ? 'Payment method accepted' 
                : "Payment method not available for {$orderType} orders",
            'scenario' => 'payment_method_validation'
        ];
    }

    /**
     * Validate order timing and preparation estimates
     */
    public function validateOrderTiming(Order $order): array
    {
        $preparationTimes = $this->validationMatrix['order_timing']['preparation_times'];
        $totalPrepTime = 0;
        $itemDetails = [];
        
        foreach ($order->orderItems as $orderItem) {
            $item = $orderItem->itemMaster;
            $prepTime = $preparationTimes[$item->category] ?? 20; // Default 20 minutes
            $adjustedTime = $prepTime * $orderItem->quantity;
            $totalPrepTime += $adjustedTime;
            
            $itemDetails[] = [
                'item' => $item->name,
                'category' => $item->category,
                'quantity' => $orderItem->quantity,
                'base_prep_time' => $prepTime,
                'total_prep_time' => $adjustedTime,
            ];
        }
        
        // Apply complexity modifier (more items = slightly longer)
        $complexityModifier = 1 + (count($order->orderItems) * 0.02);
        $totalPrepTime = (int) ($totalPrepTime * $complexityModifier);
        
        $deliveryRange = $this->validationMatrix['order_timing']['delivery_times'][$order->order_type] ?? [20, 40];
        $estimatedReady = Carbon::now()->addMinutes($totalPrepTime);
        $estimatedDelivery = [
            'min' => $estimatedReady->copy()->addMinutes($deliveryRange[0]),
            'max' => $estimatedReady->copy()->addMinutes($deliveryRange[1]),
        ];
        
        return [
            'valid' => true,
            'preparation_time' => $totalPrepTime,
            'estimated_ready' => $estimatedReady->format('H:i'),
            'estimated_delivery' => [
                'min' => $estimatedDelivery['min']->format('H:i'),
                'max' => $estimatedDelivery['max']->format('H:i'),
            ],
            'item_breakdown' => $itemDetails,
            'complexity_modifier' => $complexityModifier,
            'message' => "Order will be ready in approximately {$totalPrepTime} minutes",
            'scenario' => 'order_timing_validation'
        ];
    }

    /**
     * Run comprehensive validation for an order
     */
    public function validateOrder(Order $order, array $options = []): array
    {
        $results = [
            'overall_valid' => true,
            'validations' => [],
            'summary' => [],
        ];
        
        // Run all validations
        $validations = [
            'minimum_order' => $this->validateMinimumOrder($order),
            'kitchen_capacity' => $this->validateKitchenCapacity($order, $options['current_order_count'] ?? null),
            'dietary_compliance' => $this->validateDietaryCompliance($order, $options['customer_requirements'] ?? []),
            'order_timing' => $this->validateOrderTiming($order),
        ];
        
        // Add payment method validation if provided
        if (isset($options['payment_method'])) {
            $validations['payment_method'] = $this->validatePaymentMethod($order, $options['payment_method']);
        }
        
        // Compile results
        foreach ($validations as $type => $result) {
            $results['validations'][$type] = $result;
            if (!$result['valid']) {
                $results['overall_valid'] = false;
                $results['summary'][] = $result['message'];
            }
        }
        
        return $results;
    }

    /**
     * Generate test scenarios for validation matrix
     */
    public function generateTestScenarios(): array
    {
        return [
            'minimum_order_scenarios' => [
                [
                    'name' => 'Below minimum takeaway order',
                    'order_type' => 'takeaway',
                    'total_price' => 10.00,
                    'expected_valid' => false,
                ],
                [
                    'name' => 'Valid takeaway order',
                    'order_type' => 'takeaway',
                    'total_price' => 25.00,
                    'expected_valid' => true,
                ],
                [
                    'name' => 'Below minimum delivery order',
                    'order_type' => 'delivery',
                    'total_price' => 15.00,
                    'expected_valid' => false,
                ],
            ],
            'kitchen_capacity_scenarios' => [
                [
                    'name' => 'Normal capacity order',
                    'current_orders' => 10,
                    'item_count' => 5,
                    'is_rush_hour' => false,
                    'expected_valid' => true,
                ],
                [
                    'name' => 'At capacity during rush hour',
                    'current_orders' => 35,
                    'item_count' => 8,
                    'is_rush_hour' => true,
                    'expected_valid' => false,
                ],
                [
                    'name' => 'Too many items in order',
                    'current_orders' => 5,
                    'item_count' => 25,
                    'is_rush_hour' => false,
                    'expected_valid' => false,
                ],
            ],
            'dietary_compliance_scenarios' => [
                [
                    'name' => 'Nut allergy violation',
                    'customer_requirements' => ['avoid_allergens' => ['nuts']],
                    'items' => [['contains_nuts' => true]],
                    'expected_valid' => false,
                ],
                [
                    'name' => 'Vegetarian compliance',
                    'customer_requirements' => ['dietary_preferences' => ['vegetarian']],
                    'items' => [['is_vegetarian' => true]],
                    'expected_valid' => true,
                ],
                [
                    'name' => 'Spice level too high',
                    'customer_requirements' => ['max_spice_level' => 'mild'],
                    'items' => [['spice_level' => 'extra_hot']],
                    'expected_valid' => true, // Warning, not violation
                ],
            ],
            'payment_method_scenarios' => [
                [
                    'name' => 'Cash for takeaway',
                    'order_type' => 'takeaway',
                    'payment_method' => 'cash',
                    'expected_valid' => true,
                ],
                [
                    'name' => 'Cash on delivery for dine-in',
                    'order_type' => 'dine_in',
                    'payment_method' => 'cash_on_delivery',
                    'expected_valid' => false,
                ],
                [
                    'name' => 'Split payment for dine-in',
                    'order_type' => 'dine_in',
                    'payment_method' => 'split_payment',
                    'expected_valid' => true,
                ],
            ],
        ];
    }

    /**
     * Get validation matrix configuration
     */
    public function getValidationMatrix(): array
    {
        return $this->validationMatrix;
    }
}
