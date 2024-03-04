<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Polls\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pr0gramm\Pr0grammChannel;

// TODO: Add ShouldQueue
class PollNeedsReviewNotification extends Notification
{
    use Queueable;

    private Poll $poll;

    /**
     * Create a new notification instance.
     */
    public function __construct(Poll $poll)
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
            ->subject('📊 Neue Umfrage zur Überprüfung! #'.$this->poll->getKey())
            ->greeting('Hallo Admin,')
            ->line('Eine Umfrage braucht eine Überprüfung.')
            ->line('Titel: '.$this->poll->title)
            ->line('Erstellt von: '.$this->poll->user->name)
            ->action('Zur Umfrage', url(route('filament.pr0p0ll.resources.all-polls.view', ['record' => $this->poll->getKey()])));
    }

    public function toPr0gramm($notifiable): string
    {
        $url = url(route('filament.pr0p0ll.resources.all-polls.view', ['record' => $this->poll->getKey()]));

        return "Hallo, es würde eine neue Umfrage zur Überprüfung eingereicht. Bitte überprüfen.\n\n$url";
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
