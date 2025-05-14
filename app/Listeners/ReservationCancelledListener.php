<?php

namespace App\Listeners;

use App\Events\ReservationCancelled;
use App\Models\Waitlist;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TableAvailableNotification;
use Carbon\Carbon;

class ReservationCancelledListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ReservationCancelled $event): void
    {
        $reservation = $event->reservation;
        
        // Find waitlist entries for the same date, time slot, and branch
        $waitlistEntries = Waitlist::where('branch_id', $reservation->branch_id)
            ->where('date', $reservation->date)
            ->where('status', 'waiting')
            ->where('notify_when_available', true)
            ->where(function($query) use ($reservation) {
                $query->whereBetween('preferred_time', [
                    Carbon::parse($reservation->start_time)->subMinutes(30),
                    Carbon::parse($reservation->end_time)->addMinutes(30)
                ]);
            })
            ->orderBy('created_at')
            ->get();

        // Check if any waitlist entries can be accommodated
        foreach ($waitlistEntries as $entry) {
            // Check if the waitlist entry's party size can be accommodated
            if ($entry->number_of_people <= $reservation->number_of_people) {
                // Update waitlist entry status
                $entry->update(['status' => 'notified']);
                
                // Send notification to the customer
                if ($entry->user) {
                    Notification::send($entry->user, new TableAvailableNotification($entry));
                } else {
                    // Send SMS or email notification for non-registered users
                    // TODO: Implement SMS/email notification
                }
                
                // Break after notifying the first eligible customer
                break;
            }
        }
    }
}
