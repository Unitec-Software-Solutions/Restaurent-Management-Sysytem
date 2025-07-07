<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderNumberService
{

    public static function generate(int $branchId): string
    {
        // Use a cache-based lock to prevent race conditions across all database types
        $lockKey = "order_number_generation_branch_{$branchId}";
        
        return Cache::lock($lockKey, 10)->block(5, function () use ($branchId) {
            $prefix = 'ORD' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
            $date = now()->format('Ymd');
            $basePattern = $prefix . $date;
            
            // Get all today's order numbers for this branch that match our pattern
            $todayOrders = Order::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->where('order_number', 'LIKE', $basePattern . '%')
                ->pluck('order_number')
                ->toArray();
            
            // Extract sequence numbers and find the highest
            $maxSequence = 0;
            foreach ($todayOrders as $orderNumber) {
                if (strlen($orderNumber) >= strlen($basePattern) + 3) {
                    $sequencePart = substr($orderNumber, strlen($basePattern), 3);
                    if (is_numeric($sequencePart) && strlen($sequencePart) === 3) {
                        $maxSequence = max($maxSequence, (int) $sequencePart);
                    }
                }
            }
            
            // Generate next sequence number
            $nextSequence = $maxSequence + 1;
            $candidateNumber = $basePattern . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness by checking if candidate exists
            $attempts = 0;
            while (Order::where('order_number', $candidateNumber)->exists() && $attempts < 100) {
                $nextSequence++;
                $candidateNumber = $basePattern . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
                $attempts++;
                
                // Safety check to prevent infinite loop
                if ($nextSequence > 999) {
                    // Fallback to microsecond-based number
                    $microseconds = substr(microtime(), 2, 6);
                    $candidateNumber = $basePattern . $microseconds;
                    
                    Log::warning('Order number sequence exceeded 999, using microsecond fallback', [
                        'order_number' => $candidateNumber,
                        'branch_id' => $branchId
                    ]);
                    break;
                }
            }
            
            // Final safety check
            if (Order::where('order_number', $candidateNumber)->exists()) {
                $timestamp = now()->format('His');
                $candidateNumber = $basePattern . $timestamp;
                
                Log::error('Could not generate unique order number, using timestamp fallback', [
                    'order_number' => $candidateNumber,
                    'branch_id' => $branchId
                ]);
            }
            
            Log::info('Generated order number', [
                'order_number' => $candidateNumber,
                'branch_id' => $branchId,
                'sequence' => $nextSequence
            ]);
            
            return $candidateNumber;
        });
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
    
    /**
     * Generate a unique takeaway ID for the given branch
     */
    public static function generateTakeawayId(int $branchId): string
    {
        // Use a cache-based lock to prevent race conditions
        $lockKey = "takeaway_id_generation_branch_{$branchId}";
        
        return Cache::lock($lockKey, 10)->block(5, function () use ($branchId) {
            $prefix = 'TW';
            $date = now()->format('Ymd');
            $basePattern = $prefix . $date . str_pad($branchId, 2, '0', STR_PAD_LEFT);
            
            // Get all today's takeaway IDs for this branch that match our pattern
            $todayTakeawayIds = Order::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->where('takeaway_id', 'LIKE', $basePattern . '%')
                ->pluck('takeaway_id')
                ->toArray();
            
            // Extract sequence numbers and find the highest
            $maxSequence = 0;
            foreach ($todayTakeawayIds as $takeawayId) {
                if (strlen($takeawayId) >= strlen($basePattern) + 3) {
                    $sequencePart = substr($takeawayId, strlen($basePattern), 3);
                    if (is_numeric($sequencePart) && strlen($sequencePart) === 3) {
                        $maxSequence = max($maxSequence, (int) $sequencePart);
                    }
                }
            }
            
            // Generate next sequence number
            $nextSequence = $maxSequence + 1;
            $candidateId = $basePattern . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness
            $attempts = 0;
            while (Order::where('takeaway_id', $candidateId)->exists() && $attempts < 100) {
                $nextSequence++;
                $candidateId = $basePattern . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
                $attempts++;
                
                if ($nextSequence > 999) {
                    // Fallback to timestamp-based ID
                    $timestamp = now()->format('His');
                    $candidateId = $prefix . $timestamp . str_pad($branchId, 2, '0', STR_PAD_LEFT);
                    
                    Log::warning('Takeaway ID sequence exceeded 999, using timestamp fallback', [
                        'takeaway_id' => $candidateId,
                        'branch_id' => $branchId
                    ]);
                    break;
                }
            }
            
            // Final safety check
            if (Order::where('takeaway_id', $candidateId)->exists()) {
                $candidateId = $prefix . now()->format('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                Log::error('Could not generate unique takeaway ID, using timestamp+random fallback', [
                    'takeaway_id' => $candidateId,
                    'branch_id' => $branchId
                ]);
            }
            
            return $candidateId;
        });
    }
}
