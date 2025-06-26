<?php

namespace App\Notifications;

use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAdminWelcome extends Notification implements ShouldQueue
{
    use Queueable;

    protected $organization;
    protected $tempPassword;
    protected $branch;

    /**
     * Create a new notification instance.
     */
    public function __construct(Organization $organization, string $tempPassword, Branch $branch = null)
    {
        $this->organization = $organization;
        $this->tempPassword = $tempPassword;
        $this->branch = $branch;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isOrgAdmin = !$this->branch;
        $roleType = $isOrgAdmin ? 'Organization Administrator' : 'Branch Administrator';
        $scope = $isOrgAdmin ? 'entire organization' : $this->branch->name . ' branch';

        return (new MailMessage)
                    ->subject("Welcome to {$this->organization->name} - Admin Access Created")
                    ->greeting("Welcome to {$this->organization->name}!")
                    ->line("You have been set up as the {$roleType} for the {$scope}.")
                    ->line("**Account Details:**")
                    ->line("• Email: {$notifiable->email}")
                    ->line("• Temporary Password: `{$this->tempPassword}`")
                    ->line("• Organization: {$this->organization->name}")
                    ->when($this->branch, function ($message) {
                        return $message->line("• Branch: {$this->branch->name}");
                    })
                    ->line('')
                    ->line("**Your Administrative Privileges:**")
                    ->when($isOrgAdmin, function ($message) {
                        return $message
                            ->line("✓ Full access to all branches")
                            ->line("✓ Organization-wide settings management")
                            ->line("✓ User and role management")
                            ->line("✓ System configuration");
                    })
                    ->when(!$isOrgAdmin, function ($message) {
                        return $message
                            ->line("✓ Full access to your branch operations")
                            ->line("✓ Staff management for your branch")
                            ->line("✓ Inventory and order management")
                            ->line("✓ Reports and analytics");
                    })
                    ->line('')
                    ->line("**Important Security Notes:**")
                    ->line("• Please log in and change your password immediately")
                    ->line("• Use a strong password with at least 8 characters")
                    ->line("• Never share your login credentials")
                    ->line("• Enable two-factor authentication if available")
                    ->action('Login to Admin Panel', url('/admin/login'))
                    ->line('')
                    ->line("**Getting Started:**")
                    ->line("1. Log in using the credentials above")
                    ->line("2. Change your temporary password")
                    ->line("3. Review your dashboard and available features")
                    ->line("4. Configure your preferences and settings")
                    ->when(!$isOrgAdmin, function ($message) {
                        return $message->line("5. Set up your branch-specific configurations");
                    })
                    ->line('')
                    ->line("If you have any questions or need assistance, please contact support or your system administrator.")
                    ->line('')
                    ->line("Thank you for joining {$this->organization->name}!");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'organization_id' => $this->organization->id,
            'organization_name' => $this->organization->name,
            'branch_id' => $this->branch?->id,
            'branch_name' => $this->branch?->name,
            'role_type' => $this->branch ? 'branch_admin' : 'org_admin',
            'temp_password_sent' => true,
        ];
    }
}
