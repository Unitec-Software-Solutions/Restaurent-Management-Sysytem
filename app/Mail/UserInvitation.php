<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Invitation to join ' . $this->user->organization->name)
            ->markdown('emails.user_invitation')
            ->with([
                'activationUrl' => url('/complete-registration?token=' . $this->user->invitation_token),
                'organization' => $this->user->organization,
                'branch' => $this->user->branch
            ]);
    }
}
