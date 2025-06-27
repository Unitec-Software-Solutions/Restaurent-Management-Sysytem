<?php
// filepath: app/Console/Commands/ShowRecentOrders.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class ShowRecentOrders extends Command
{
    protected $signature = 'orders:recent {--count=5 : Number of recent orders to show}';
    protected $description = 'Display recent orders with formatted output following UI/UX guidelines';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("📋 Recent {$count} Orders");
        $this->line(str_repeat('=', 50));
        
        $orders = Order::with(['branch.organization'])
            ->latest()
            ->take($count)
            ->get();
            
        if ($orders->isEmpty()) {
            $this->warn('📭 No orders found in the system');
            return Command::SUCCESS;
        }
        
        // Create table data
        $tableData = [];
        foreach ($orders as $order) {
            $tableData[] = [
                $order->order_number,
                $order->customer_name ?? 'N/A',
                ucfirst(str_replace('_', ' ', $order->order_type)),
                ucfirst($order->status),
                $order->currency_symbol . $order->formatted_total,
                $order->branch?->name ?? 'N/A',
                $order->created_at->format('M d, H:i')
            ];
        }
        
        $this->table(
            ['Order #', 'Customer', 'Type', 'Status', 'Total', 'Branch', 'Created'],
            $tableData
        );
        
        // Summary statistics
        $this->newLine();
        $this->info('📊 Summary:');
        $totalAmount = $orders->sum('total_amount');
        $avgAmount = $orders->avg('total_amount');
        
        $statusCount = $orders->groupBy('status')->map->count();
        
        $this->line("  💰 Total Value: LKR " . number_format($totalAmount, 2));
        $this->line("  📈 Average Order: LKR " . number_format($avgAmount, 2));
        $this->line("  📊 Status Distribution:");
        
        foreach ($statusCount as $status => $count) {
            $this->line("    • " . ucfirst($status) . ": {$count} orders");
        }
        
        return Command::SUCCESS;
    }
}