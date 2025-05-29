<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Mail\Mailable;

class ReservationRejected extends Mailable
{
    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('Reservation Declined')
                    ->view('emails.reservations.rejected');
    }
}