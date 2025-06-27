<?php
// filepath: app/Console/Commands/TestOrderCreation.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TestOrderCreation extends Command
{
    protected $signature = 'test:orders {--count=1 : Number of orders to create} {--type=mixed : Order type (dine_in, takeaway, delivery, mixed)}';
    protected $description = 'Test order creation with proper validation and field mapping';

    public function handle()
    {
        $count = $this->option('count');
        $type = $this->option('type');
        
        $this->info("🧪 Testing Order Creation System");
        $this->line(str_repeat('=', 60));
        
        // Check database structure first
        $this->checkDatabaseStructure();
        
        // Check prerequisites
        $this->checkPrerequisites();
        
        $this->newLine();
        $this->info("🚀 Creating {$count} test order(s) of type: {$type}");
        
        $createdOrders = [];
        $errors = [];
        
        for ($i = 1; $i <= $count; $i++) {
            try {
                $order = $this->createTestOrder($type, $i);
                $createdOrders[] = $order;
                
                $this->line("  ✅ Order #{$i}: {$order->order_number} - Total: {$order->currency_symbol}{$order->formatted_total}");
                
            } catch (\Exception $e) {
                $errors[] = "Order #{$i}: " . $e->getMessage();
                $this->error("  ❌ Order #{$i}: Failed - " . $e->getMessage());
            }
        }
        
        $this->displayResults($createdOrders, $errors);
        
        return Command::SUCCESS;
    }

    private function checkDatabaseStructure()
    {
        $this->info("🔍 Checking Database Structure:");
        
        if (!Schema::hasTable('orders')) {
            $this->error("❌ Orders table does not exist!");
            return;
        }
        
        $columns = Schema::getColumnListing('orders');
        $requiredColumns = ['total_amount', 'tax_amount', 'discount_amount', 'subtotal'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            $this->info("  ✅ All required columns present");
        } else {
            $this->warn("  ⚠️ Missing columns: " . implode(', ', $missingColumns));
        }
        
        $this->line("  📊 Total columns: " . count($columns));
    }
    
    private function checkPrerequisites()
    {
        $orgCount = Organization::count();
        $branchCount = Branch::count();
        $menuCount = MenuItem::count();
        
        $this->info("📋 Prerequisites Check:");
        $this->line("  • Organizations: {$orgCount}");
        $this->line("  • Branches: {$branchCount}");
        $this->line("  • Menu Items: {$menuCount}");
        
        if ($orgCount === 0 || $branchCount === 0) {
            $this->error("❌ Missing organizations or branches. Run seeders first.");
            $this->line("  Command: php artisan db:seed");
            throw new \Exception("Prerequisites not met");
        }
    }
    
    private function createTestOrder(string $type, int $index): Order
    {
        $factory = Order::factory();
        
        // Apply type-specific configuration
        switch ($type) {
            case 'dine_in':
                $factory = $factory->dineIn();
                break;
            case 'takeaway':
                $factory = $factory->takeaway();
                break;
            case 'delivery':
                $factory = $factory->delivery();
                break;
            case 'mixed':
                $orderTypes = ['dine_in', 'takeaway', 'delivery'];
                $selectedType = $orderTypes[($index - 1) % count($orderTypes)];
                if ($selectedType === 'dine_in') {
                    $factory = $factory->dineIn();
                } elseif ($selectedType === 'takeaway') {
                    $factory = $factory->takeaway();
                } else {
                    $factory = $factory->delivery();
                }
                break;
        }
        
        // Occasionally create high-value orders
        if ($index % 5 === 0) {
            $factory = $factory->highValue();
        }
        
        return $factory->create();
    }
    
    private function displayResults(array $createdOrders, array $errors)
    {
        $this->newLine();
        $this->info("📊 Test Results:");
        $this->line("  • Successfully created: " . count($createdOrders));
        $this->line("  • Errors: " . count($errors));
        
        if (!empty($createdOrders)) {
            $this->newLine();
            $this->info("✅ Sample Order Details:");
            $order = $createdOrders[0];
            
            $this->table(
                ['Field', 'Value'],
                [
                    ['Order Number', $order->order_number],
                    ['Customer', $order->customer_name],
                    ['Phone', $order->customer_phone],
                    ['Branch', $order->branch?->name ?? 'N/A'],
                    ['Organization', $order->organization?->name ?? 'N/A'],
                    ['Type', ucfirst(str_replace('_', ' ', $order->order_type))],
                    ['Status', ucfirst($order->status)],
                    ['Subtotal', $order->currency . ' ' . number_format($order->subtotal, 2)],
                    ['Tax Amount', $order->currency . ' ' . number_format($order->tax_amount, 2)],
                    ['Service Charge', $order->currency . ' ' . number_format($order->service_charge, 2)],
                    ['Discount', $order->currency . ' ' . number_format($order->discount_amount, 2)],
                    ['Delivery Fee', $order->currency . ' ' . number_format($order->delivery_fee ?? 0, 2)],
                    ['Total Amount', $order->currency . ' ' . number_format($order->total_amount, 2)],
                    ['Payment Status', ucfirst($order->payment_status)],
                    ['Created At', $order->created_at->format('Y-m-d H:i:s')],
                ]
            );
            
            // Show field mapping verification
            $this->newLine();
            $this->info("🔄 Field Mapping Verification:");
            $this->table(
                ['Legacy Field', 'New Field', 'Match'],
                [
                    ['total', 'total_amount', $order->total == $order->total_amount ? '✅' : '❌'],
                    ['tax', 'tax_amount', $order->tax == $order->tax_amount ? '✅' : '❌'],
                    ['discount', 'discount_amount', $order->discount == $order->discount_amount ? '✅' : '❌'],
                ]
            );
        }
        
        if (!empty($errors)) {
            $this->newLine();
            $this->error("❌ Errors encountered:");
            foreach ($errors as $error) {
                $this->line("  • {$error}");
            }
        }
        
        // Show database verification
        $this->newLine();
        $this->info("🗄️ Database Verification:");
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', today())->count();
        $this->line("  • Total orders in database: {$totalOrders}");
        $this->line("  • Orders created today: {$todayOrders}");
    }
}