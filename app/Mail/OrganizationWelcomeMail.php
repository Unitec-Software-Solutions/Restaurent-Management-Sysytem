<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganizationWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $organization;
    public $admin;

    public function __construct(Organization $organization, Admin $admin)
    {
        $this->organization = $organization;
        $this->admin = $admin;
    }

    public function build()
    {
        return $this->subject('Welcome to Restaurant Management System')
                    ->view('emails.organization-welcome')
                    ->with([
                        'organizationName' => $this->organization->name,
                        'adminName' => $this->admin->name,
                        'adminEmail' => $this->admin->email,
                        'temporaryPassword' => $this->admin->temporary_password,
                        'loginUrl' => route('admin.login'),
                    ]);
    }
}