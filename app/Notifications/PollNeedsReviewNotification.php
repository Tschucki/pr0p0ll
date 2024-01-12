<?php

namespace App\Notifications;

use App\Abstracts\Poll as AbstractPoll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// TODO: Add ShouldQueue
class PollNeedsReviewNotification extends Notification
{
    use Queueable;

    private AbstractPoll $poll;

    /**
     * Create a new notification instance.
     */
    public function __construct(AbstractPoll $poll)
    {
        $this->poll = $poll;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            'mail',
            // TODO: Add Pr0gramm Channel
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Hallo Admin,')
            ->line('Eine Umfrage braucht eine Überprüfung.')
            ->action('Zur Umfrage', url(route('filament.pr0p0ll.resources.all-polls.view', ['record' => $this->poll->getKey()])))
            ->line('Danke');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
