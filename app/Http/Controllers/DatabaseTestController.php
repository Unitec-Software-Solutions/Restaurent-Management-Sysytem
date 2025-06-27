<?php

// filepath: app/Http/Controllers/DatabaseTestController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\MenuItem;

class DatabaseTestController extends Controller
{
    /**
     * Run database diagnostics via AJAX
     */
    public function diagnoseTable(Request $request)
    {
        $request->validate([
            'table' => 'required|string'
        ]);

        try {
            $tableName = $request->input('table');
            
            // Capture artisan command output
            $exitCode = Artisan::call('db:diagnose', [
                '--table' => $tableName
            ]);
            
            $output = Artisan::output();
            
            return response()->json([
                'success' => $exitCode === 0,
                'results' => $output,
                'message' => $exitCode === 0 ? 'Diagnosis completed' : 'Diagnosis failed'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'results' => ''
            ], 500);
        }
    }

    /**
     * Run database migrations via AJAX
     */
    public function runMigrations(Request $request)
    {
        try {
            $exitCode = Artisan::call('migrate');
            $output = Artisan::output();
            
            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Migrations completed successfully',
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run database seeder via AJAX
     */
    public function runSeeder(Request $request)
    {
        try {
            $exitCode = Artisan::call('db:seed', ['--class' => 'MenuItemSeeder']);
            $output = Artisan::output();
            
            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Database seeded successfully',
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run full system diagnosis via AJAX
     */
    public function fullDiagnose(Request $request)
    {
        try {
            $exitCode = Artisan::call('db:diagnose', ['--full' => true]);
            $output = Artisan::output();
            
            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Full diagnosis completed',
                'results' => $output
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run fresh migration via AJAX
     */
    public function freshMigrate(Request $request)
    {
        try {
            $exitCode = Artisan::call('migrate:fresh', ['--seed' => true]);
            $output = Artisan::output();
            
            return response()->json([
                'success' => $exitCode === 0,
                'message' => 'Fresh migration completed',
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test order creation via AJAX with enhanced error handling
     */
    public function testOrderCreation(Request $request)
    {
        try {
            $count = $request->input('count', 1);
            $type = $request->input('type', 'mixed');
            $results = [];
            
            // Use the enhanced test command
            $exitCode = Artisan::call('test:orders', [
                '--count' => $count,
                '--type' => $type
            ]);
            
            if ($exitCode === 0) {
                // Fetch the created orders
                $recentOrders = Order::latest()
                    ->take($count)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name,
                            'total' => $order->formatted_total,
                            'currency' => $order->currency_symbol,
                            'status' => $order->status,
                            'type' => $order->order_type,
                            'created_at' => $order->created_at->format('Y-m-d H:i:s')
                        ];
                    });
                
                return response()->json([
                    'success' => true,
                    'message' => "{$count} order(s) created successfully",
                    'orders' => $recentOrders,
                    'command_output' => Artisan::output()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Order creation failed',
                    'command_output' => Artisan::output()
                ], 422);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    /**
     * Get system stats via AJAX
     */
    public function getSystemStats(Request $request)
    {
        try {
            $stats = [
                'organizations' => Organization::count(),
                'branches' => Branch::count(),
                'menu_items' => MenuItem::count(),
                'orders' => Order::count(),
                'active_organizations' => Organization::where('is_active', true)->count(),
                'active_branches' => Branch::where('is_active', true)->count(),
                'active_menu_items' => MenuItem::where('is_active', true)->count(),
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statistics via AJAX
     */
    public function getOrderStats(Request $request)
    {
        try {
            $stats = [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'completed' => Order::whereIn('status', ['completed', 'served'])->count(),
                'revenue' => number_format(Order::whereDate('created_at', today())->sum('total_amount'), 0)
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent orders via AJAX
     */
    public function getRecentOrders(Request $request)
    {
        try {
            $orders = Order::with(['branch.organization'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($order) {
                    return [
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer_name,
                        'type' => $order->order_type,
                        'status' => $order->status,
                        'total' => $order->formatted_total,
                        'currency' => $order->currency_symbol,
                        'branch' => $order->branch?->name,
                        'created_at' => $order->created_at->format('M d, Y H:i')
                    ];
                });
        
            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders preview HTML via AJAX
     */
    public function getOrdersPreview(Request $request)
    {
        try {
            $recentOrders = Order::with(['branch.organization'])
                ->latest()
                ->take(5)
                ->get();
        
            $html = view('admin.partials.orders-preview', compact('recentOrders'))->render();
        
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
