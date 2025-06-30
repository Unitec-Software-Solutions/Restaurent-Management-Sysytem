<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send reservation confirmation notification
     */
    public function sendReservationConfirmation(Reservation $reservation): bool
    {
        try {
            $customer = $reservation->customer;
            
            if (!$customer) {
                Log::warning('No customer found for reservation notification', ['reservation_id' => $reservation->id]);
                return false;
            }
            
            if ($customer->isPreferredContactSms()) {
                return $this->sendSmsReservationConfirmation($reservation);
            } else {
                return $this->sendEmailReservationConfirmation($reservation);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send reservation confirmation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send order receipt notification
     */
    public function sendOrderReceipt(Order $order): bool
    {
        try {
            $customer = $order->customer;
            
            if (!$customer) {
                Log::warning('No customer found for order receipt', ['order_id' => $order->id]);
                return false;
            }
            
            if ($customer->isPreferredContactSms()) {
                return $this->sendSmsOrderReceipt($order);
            } else {
                return $this->sendEmailOrderReceipt($order);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order receipt', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send reservation cancellation notification
     */
    public function sendReservationCancellation(Reservation $reservation): bool
    {
        try {
            $customer = $reservation->customer;
            
            if (!$customer) {
                return false;
            }
            
            if ($customer->isPreferredContactSms()) {
                return $this->sendSmsReservationCancellation($reservation);
            } else {
                return $this->sendEmailReservationCancellation($reservation);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send reservation cancellation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send order status update
     */
    public function sendOrderStatusUpdate(Order $order, string $previousStatus): bool
    {
        try {
            $customer = $order->customer;
            
            if (!$customer) {
                return false;
            }
            
            // Only send notifications for significant status changes
            $notifiableStatuses = ['confirmed', 'preparing', 'ready', 'completed'];
            if (!in_array($order->status, $notifiableStatuses)) {
                return true; // Not an error, just not a notifiable status
            }
            
            if ($customer->isPreferredContactSms()) {
                return $this->sendSmsOrderStatus($order, $previousStatus);
            } else {
                return $this->sendEmailOrderStatus($order, $previousStatus);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order status update', [
                'order_id' => $order->id,
                'status' => $order->status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Private methods for specific notification types
     */
    private function sendEmailReservationConfirmation(Reservation $reservation): bool
    {
        if (!$reservation->email) {
            return false;
        }
        
        // Here you would implement the actual email sending
        // For now, just log the action
        Log::info('Email reservation confirmation sent', [
            'reservation_id' => $reservation->id,
            'email' => $reservation->email,
            'phone' => $reservation->phone
        ]);
        
        return true;
    }

    private function sendSmsReservationConfirmation(Reservation $reservation): bool
    {
        // Here you would implement SMS sending (Twilio, etc.)
        Log::info('SMS reservation confirmation sent', [
            'reservation_id' => $reservation->id,
            'phone' => $reservation->phone
        ]);
        
        return true;
    }

    private function sendEmailOrderReceipt(Order $order): bool
    {
        if (!$order->customer_email) {
            return false;
        }
        
        Log::info('Email order receipt sent', [
            'order_id' => $order->id,
            'email' => $order->customer_email,
            'phone' => $order->customer_phone
        ]);
        
        return true;
    }

    private function sendSmsOrderReceipt(Order $order): bool
    {
        Log::info('SMS order receipt sent', [
            'order_id' => $order->id,
            'phone' => $order->customer_phone
        ]);
        
        return true;
    }

    private function sendEmailReservationCancellation(Reservation $reservation): bool
    {
        if (!$reservation->email) {
            return false;
        }
        
        Log::info('Email reservation cancellation sent', [
            'reservation_id' => $reservation->id,
            'email' => $reservation->email,
            'cancellation_fee' => $reservation->cancellation_fee
        ]);
        
        return true;
    }

    private function sendSmsReservationCancellation(Reservation $reservation): bool
    {
        Log::info('SMS reservation cancellation sent', [
            'reservation_id' => $reservation->id,
            'phone' => $reservation->phone,
            'cancellation_fee' => $reservation->cancellation_fee
        ]);
        
        return true;
    }

    private function sendEmailOrderStatus(Order $order, string $previousStatus): bool
    {
        if (!$order->customer_email) {
            return false;
        }
        
        Log::info('Email order status update sent', [
            'order_id' => $order->id,
            'email' => $order->customer_email,
            'status' => $order->status,
            'previous_status' => $previousStatus
        ]);
        
        return true;
    }

    private function sendSmsOrderStatus(Order $order, string $previousStatus): bool
    {
        Log::info('SMS order status update sent', [
            'order_id' => $order->id,
            'phone' => $order->customer_phone,
            'status' => $order->status,
            'previous_status' => $previousStatus
        ]);
        
        return true;
    }
}
