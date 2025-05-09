<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->markdown('emails.reservations.confirmed')
                    ->subject('Reservation Confirmed - ' . $this->reservation->branch->name)
                    ->with([
                        'reservation' => $this->reservation,
                        'branch' => $this->reservation->branch,
                        'customerName' => $this->reservation->customer_name
                    ]);
    }
} 