<?php

namespace App\Notifications;

use App\Abstracts\Poll as AbstractPoll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pr0gramm\Pr0grammChannel;

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
            Pr0grammChannel::class,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hallo Admin,')
            ->line('Eine Umfrage braucht eine Überprüfung.')
            ->line('Titel: '.$this->poll->title)
            ->line('Erstellt von: '.$this->poll->user->name)
            ->action('Zur Umfrage', url(route('filament.pr0p0ll.resources.all-polls.view', ['record' => $this->poll->getKey()])));
    }

    public function toPr0gramm($notifiable): string
    {
        return 'Hallo, es würde eine neue Umfrage zur Überprüfung eingereicht. Bitte überprüfen.';
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
