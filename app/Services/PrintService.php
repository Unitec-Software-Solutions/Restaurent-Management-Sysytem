<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Bill;
use App\Models\Reservation;
use Illuminate\Support\Collection;

class PrintService
{
    /**
     * Generate KOT data for printing
     */
    public function generateKOTData(Order $order)
    {
        $order->load(['items.inventoryItem.category', 'branch', 'steward']);

        // Group items by category for kitchen organization
        $groupedItems = $order->items->groupBy(function ($item) {
            return $item->inventoryItem->category->name ?? 'Uncategorized';
        });

        return [
            'order' => $order,
            'kot_number' => 'KOT-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'grouped_items' => $groupedItems,
            'total_items' => $order->items->sum('quantity'),
            'generated_at' => now(),
            'branch' => $order->branch,
            'steward' => $order->steward,
            'table_info' => $order->reservation ? "Table: {$order->reservation->table_number}" : 'Takeaway',
            'special_instructions' => $order->notes
        ];
    }

    /**
     * Generate Bill data for printing
     */
    public function generateBillData(Bill $bill)
    {
        $bill->load(['order.items.inventoryItem', 'branch.organization', 'generatedBy']);

        return [
            'bill' => $bill,
            'order' => $bill->order,
            'items' => $bill->order->items,
            'organization' => $bill->branch->organization,
            'branch' => $bill->branch,
            'generated_by' => $bill->generatedBy,
            'print_time' => now(),
            'tax_breakdown' => $this->calculateTaxBreakdown($bill),
            'payment_summary' => $this->getPaymentSummary($bill)
        ];
    }

    /**
     * Generate Order Summary for export
     */
    public function generateOrderSummary(Collection $orders, $filters = [])
    {
        $summary = [
            'filters' => $filters,
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
            'average_order_value' => $orders->avg('total'),
            'orders_by_status' => $orders->groupBy('status')->map->count(),
            'orders_by_type' => $orders->groupBy('order_type')->map->count(),
            'top_items' => $this->getTopSellingItems($orders),
            'peak_hours' => $this->getPeakHours($orders),
            'generated_at' => now()
        ];

        return $summary;
    }

    /**
     * Generate Reservation Summary for export
     */
    public function generateReservationSummary(Collection $reservations, $filters = [])
    {
        return [
            'filters' => $filters,
            'total_reservations' => $reservations->count(),
            'reservations_by_status' => $reservations->groupBy('status')->map->count(),
            'peak_booking_times' => $this->getReservationPeakTimes($reservations),
            'average_party_size' => $reservations->avg('party_size'),
            'conversion_rate' => $this->calculateConversionRate($reservations),
            'generated_at' => now()
        ];
    }

    /**
     * Calculate tax breakdown for bill
     */
    protected function calculateTaxBreakdown(Bill $bill)
    {
        $subtotal = $bill->subtotal;
        $taxRate = 0.13; // 13% VAT
        $serviceChargeRate = 0.10; // 10% service charge

        return [
            'subtotal' => $subtotal,
            'vat_rate' => $taxRate * 100,
            'vat_amount' => $bill->tax_amount,
            'service_charge_rate' => $serviceChargeRate * 100,
            'service_charge_amount' => $bill->service_charge,
            'discount_amount' => $bill->discount_amount,
            'total_before_tax' => $subtotal,
            'total_tax' => $bill->tax_amount + $bill->service_charge,
            'final_total' => $bill->total_amount
        ];
    }

    /**
     * Get payment summary
     */
    protected function getPaymentSummary(Bill $bill)
    {
        return [
            'payment_method' => $bill->payment_method ?? 'Cash',
            'payment_status' => $bill->payment_status ?? 'Pending',
            'total_due' => $bill->total_amount,
            'amount_paid' => $bill->isPaid() ? $bill->total_amount : 0,
            'balance_due' => $bill->isPaid() ? 0 : $bill->total_amount
        ];
    }

    /**
     * Get top selling items from orders
     */
    protected function getTopSellingItems(Collection $orders)
    {
        $items = $orders->flatMap->items;
        
        return $items->groupBy('inventory_item_id')
            ->map(function ($groupedItems) {
                $first = $groupedItems->first();
                return [
                    'item_name' => $first->inventoryItem->name ?? 'Unknown',
                    'total_quantity' => $groupedItems->sum('quantity'),
                    'total_revenue' => $groupedItems->sum('total_price'),
                    'orders_count' => $groupedItems->count()
                ];
            })
            ->sortByDesc('total_quantity')
            ->take(10)
            ->values();
    }

    /**
     * Get peak hours from orders
     */
    protected function getPeakHours(Collection $orders)
    {
        return $orders->groupBy(function ($order) {
            return $order->created_at->format('H');
        })->map->count()
        ->sortByDesc()
        ->take(5);
    }

    /**
     * Get peak booking times for reservations
     */
    protected function getReservationPeakTimes(Collection $reservations)
    {
        return $reservations->groupBy(function ($reservation) {
            return $reservation->reservation_time->format('H:00');
        })->map->count()
        ->sortByDesc()
        ->take(5);
    }

    /**
     * Calculate reservation to order conversion rate
     */
    protected function calculateConversionRate(Collection $reservations)
    {
        $totalReservations = $reservations->count();
        $convertedReservations = $reservations->filter(function ($reservation) {
            return $reservation->orders()->exists();
        })->count();

        return $totalReservations > 0 ? ($convertedReservations / $totalReservations) * 100 : 0;
    }

    /**
     * Format data for CSV export
     */
    public function formatForCSV(Collection $data, $type = 'orders')
    {
        if ($type === 'orders') {
            return $data->map(function ($order) {
                return [
                    'Order ID' => $order->order_number ?? "ORD-{$order->id}",
                    'Date' => $order->order_date->format('Y-m-d H:i'),
                    'Customer' => $order->customer_name ?? 'Walk-in',
                    'Phone' => $order->customer_phone ?? '-',
                    'Type' => ucwords(str_replace('_', ' ', $order->order_type)),
                    'Status' => ucfirst($order->status),
                    'Items' => $order->items->count(),
                    'Steward' => $order->steward->name ?? '-',
                    'Branch' => $order->branch->name,
                    'Subtotal' => number_format($order->subtotal, 2),
                    'Tax' => number_format($order->tax, 2),
                    'Service Charge' => number_format($order->service_charge, 2),
                    'Discount' => number_format($order->discount ?? 0, 2),
                    'Total' => number_format($order->total, 2)
                ];
            });
        }

        if ($type === 'reservations') {
            return $data->map(function ($reservation) {
                return [
                    'Reservation ID' => "RES-{$reservation->id}",
                    'Date' => $reservation->reservation_date->format('Y-m-d'),
                    'Time' => $reservation->reservation_time->format('H:i'),
                    'Customer' => $reservation->name,
                    'Phone' => $reservation->phone,
                    'Party Size' => $reservation->party_size,
                    'Table' => $reservation->table_number ?? '-',
                    'Status' => ucfirst($reservation->status),
                    'Branch' => $reservation->branch->name,
                    'Special Requests' => $reservation->special_requests ?? '-',
                    'Created At' => $reservation->created_at->format('Y-m-d H:i')
                ];
            });
        }

        return collect();
    }
}
