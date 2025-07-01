<?php

namespace App\Listeners;

use App\Events\ReservationCancelled;
use App\Services\NotificationService;

class ReservationCancelledListener
{
    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(ReservationCancelled $event): void
    {
        $reservation = $event->reservation;
        
        // Send cancellation confirmation to customer
        $this->notificationService->sendReservationCancellation($reservation);
        
        // Handle any cancellation fees if applicable
        if ($reservation->shouldChargeCancellationFee()) {
            $reservation->applyCancellationFee();
        }
    }
}
