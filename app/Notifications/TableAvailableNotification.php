<?php

namespace App\Notifications;

use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TableAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $waitlist;

    /**
     * Create a new notification instance.
     */
    public function __construct(Waitlist $waitlist)
    {
        $this->waitlist = $waitlist;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Table Available!')
            ->greeting('Hello ' . $this->waitlist->name)
            ->line('A table has become available for your requested time slot!')
            ->line('Date: ' . $this->waitlist->date->format('F j, Y'))
            ->line('Time: ' . $this->waitlist->preferred_time->format('H:i'))
            ->line('Number of People: ' . $this->waitlist->number_of_people)
            ->line('Branch: ' . $this->waitlist->branch->name)
            ->action('Make Reservation', route('reservations.create', [
                'date' => $this->waitlist->date->format('Y-m-d'),
                'time' => $this->waitlist->preferred_time->format('H:i'),
                'people' => $this->waitlist->number_of_people,
                'branch' => $this->waitlist->branch_id
            ]))
            ->line('Please note that this table will be held for 15 minutes.')
            ->line('Thank you for your patience!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'waitlist_id' => $this->waitlist->id,
            'date' => $this->waitlist->date->format('Y-m-d'),
            'time' => $this->waitlist->preferred_time->format('H:i'),
            'branch' => $this->waitlist->branch->name,
            'message' => 'A table has become available for your requested time slot!'
        ];
    }
}
