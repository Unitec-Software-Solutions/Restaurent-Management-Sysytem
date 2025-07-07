<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    /**
     * Generate a unique order number for the given branch
     */
    public static function generate(int $branchId): string
    {
        $prefix = 'ORD' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $date = now()->format('Ymd');
        
        // Try up to 100 times to generate a unique order number
        for ($attempt = 1; $attempt <= 100; $attempt++) {
            $orderNumber = self::generateCandidate($prefix, $date, $branchId, $attempt);
            
            // Check if this order number exists using a database query
            if (!Order::where('order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }
            
            // Log collision for the first few attempts
            if ($attempt <= 5) {
                Log::warning('Order number collision detected', [
                    'order_number' => $orderNumber,
                    'attempt' => $attempt,
                    'branch_id' => $branchId
                ]);
            }
        }
        
        // Ultimate fallback: use timestamp + random suffix to ensure uniqueness
        return self::generateFallbackNumber($prefix, $date);
    }
    
    /**
     * Generate a candidate order number
     */
    private static function generateCandidate(string $prefix, string $date, int $branchId, int $attempt): string
    {
        // Use a combination of daily count + attempt for sequence
        $dailyCount = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->count();
            
        $sequence = $dailyCount + $attempt;
        
        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate a fallback order number using timestamp
     */
    private static function generateFallbackNumber(string $prefix, string $date): string
    {
        $timestamp = now()->format('His'); // Hours, minutes, seconds
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $fallbackNumber = $prefix . $date . substr($timestamp . $random, -6);
        
        Log::error('Order number generation exhausted retries, using fallback', [
            'fallback_number' => $fallbackNumber
        ]);
        
        return $fallbackNumber;
    }
    
    /**
     * Generate order number with atomic cache-based sequence (alternative approach)
     */
    public static function generateWithCache(int $branchId): string
    {
        $prefix = 'ORD' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $date = now()->format('Ymd');
        $cacheKey = "order_seq_{$branchId}_{$date}";
        
        // Initialize cache if it doesn't exist
        if (!Cache::has($cacheKey)) {
            $dailyCount = Order::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->count();
            Cache::put($cacheKey, $dailyCount, now()->endOfDay());
        }
        
        // Atomic increment
        $sequence = Cache::increment($cacheKey);
        $orderNumber = $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        // Verify uniqueness (fallback check)
        if (Order::where('order_number', $orderNumber)->exists()) {
            Log::warning('Cache-based order number collision, using fallback', [
                'order_number' => $orderNumber,
                'branch_id' => $branchId
            ]);
            return self::generateFallbackNumber($prefix, $date);
        }
        
        return $orderNumber;
    }
    
    /**
     * Generate production order number
     */
    public static function generateProductionOrderNumber(): string
    {
        $prefix = 'PRD';
        $date = now()->format('Ymd');
        
        for ($attempt = 1; $attempt <= 100; $attempt++) {
            $sequence = \App\Models\ProductionOrder::whereDate('created_at', today())->count() + $attempt;
            $orderNumber = $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
            if (!\App\Models\ProductionOrder::where('production_order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }
        }
        
        // Fallback
        $timestamp = now()->format('His');
        return $prefix . $date . $timestamp;
    }
    
    /**
     * Validate order number format
     */
    public static function isValidFormat(string $orderNumber): bool
    {
        // Order numbers should match pattern: ORD[BB][YYYYMMDD][SSS]
        return preg_match('/^ORD\d{2}\d{8}\d{3,6}$/', $orderNumber);
    }
}
