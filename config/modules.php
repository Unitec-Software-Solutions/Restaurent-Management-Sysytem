<?php

return [
    'pos' => [
        'name' => 'Point of Sale',
        'description' => 'Order taking, payment processing, and customer service with real-time KOT integration',
        'tiers' => [
            'basic' => [
                'permissions' => ['create_orders', 'process_payments', 'view_menu', 'generate_kot', 'basic_receipts'],
                'features' => ['basic_ordering', 'cash_payments', 'receipt_printing', 'kot_generation', 'order_status_tracking']
            ],
            'premium' => [
                'permissions' => ['split_bills', 'apply_discounts', 'void_items', 'refunds', 'advanced_kot', 'real_time_tracking'],
                'features' => ['split_billing', 'discount_management', 'loyalty_points', 'detailed_analytics', 'real_time_kitchen_status', 'advanced_reporting']
            ]
        ]
    ],    
    'kitchen' => [
        'name' => 'Kitchen Management',
        'description' => 'Real-time KOT tracking, order preparation, and kitchen workflow optimization',
        'tiers' => [
            'basic' => [
                'permissions' => ['view_kot', 'update_order_status', 'mark_preparing', 'mark_ready'],
                'features' => ['kot_display', 'basic_status_updates', 'order_queue', 'preparation_tracking']
            ],
            'premium' => [
                'permissions' => ['prep_time_tracking', 'kitchen_analytics', 'recipe_management', 'station_assignment', 'batch_processing'],
                'features' => ['advanced_timing', 'kitchen_reports', 'recipe_costing', 'multi_station_kot', 'prep_time_analytics', 'auto_staff_assignment']
            ]
        ]
    ],    
    'inventory' => [
        'name' => 'Inventory Management',
        'description' => 'Smart stock tracking with 10% low-stock alerts, automated purchasing, and waste management',
        'tiers' => [
            'basic' => [
                'permissions' => ['view_stock', 'manual_adjustments', 'view_alerts'],
                'features' => ['basic_stock_view', 'manual_updates', 'low_stock_alerts_10_percent', 'basic_reports']
            ],
            'premium' => [
                'permissions' => ['auto_reordering', 'waste_tracking', 'supplier_management', 'purchase_orders', 'predictive_analytics'],
                'features' => ['automated_reordering', 'waste_analytics', 'supplier_performance', 'cost_analysis', 'smart_forecasting', 'batch_expiry_tracking']
            ]
        ]
    ],
    
    'reservations' => [
        'name' => 'Table Reservations',
        'description' => 'Table booking, guest management, and seating optimization',
        'tiers' => [
            'basic' => [
                'permissions' => ['create_reservations', 'view_availability'],
                'features' => ['basic_booking', 'table_assignment']
            ],
            'premium' => [
                'permissions' => ['waitlist_management', 'guest_preferences', 'special_requests'],
                'features' => ['advanced_scheduling', 'guest_history', 'automated_reminders']
            ]
        ]
    ],    
    'staff' => [
        'name' => 'Staff Management',
        'description' => 'Smart shift-based staff assignment, performance tracking, and automated scheduling',
        'tiers' => [
            'basic' => [
                'permissions' => ['view_schedule', 'clock_in_out', 'shift_assignment'],
                'features' => ['basic_scheduling', 'attendance_tracking', 'shift_based_auto_assignment']
            ],
            'premium' => [
                'permissions' => ['performance_tracking', 'payroll_management', 'shift_optimization', 'advanced_scheduling', 'staff_analytics'],
                'features' => ['performance_analytics', 'automated_scheduling', 'labor_cost_tracking', 'skill_based_assignment', 'workload_optimization']
            ]
        ]
    ],
    
    'reporting' => [
        'name' => 'Reports & Analytics',
        'description' => 'Business intelligence, sales reports, and operational metrics',
        'tiers' => [
            'basic' => [
                'permissions' => ['view_sales_reports', 'basic_analytics'],
                'features' => ['daily_sales', 'basic_charts']
            ],
            'premium' => [
                'permissions' => ['advanced_analytics', 'custom_reports', 'forecasting'],
                'features' => ['predictive_analytics', 'custom_dashboards', 'automated_reporting']
            ]
        ]
    ],
    
    'customer' => [
        'name' => 'Customer Management',
        'description' => 'Customer profiles, loyalty programs, and feedback management',
        'tiers' => [
            'basic' => [
                'permissions' => ['view_customers', 'basic_profiles'],
                'features' => ['customer_directory', 'order_history']
            ],
            'premium' => [
                'permissions' => ['loyalty_management', 'customer_analytics', 'feedback_tracking'],
                'features' => ['loyalty_programs', 'customer_segmentation', 'feedback_analysis']
            ]
        ]
    ]
];
