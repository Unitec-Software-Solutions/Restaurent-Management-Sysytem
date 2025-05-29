<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Mail\Mailable;

class ReservationConfirmed extends Mailable
{
    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('Reservation Confirmed')
                    ->view('emails.reservations.confirmed');
    }
}