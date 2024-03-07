<?php

declare(strict_types=1);

namespace App\Notifications\Email;

use App\Models\Polls\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPollAvailableEmailNotification extends Notification
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
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📊 Neue Umfrage verfügbar! #'.$this->poll->getKey())
            ->greeting('Hallo,')
            ->line('Es ist eine neue Umfrage auf pr0p0ll verfügbar.')
            ->line('Titel: '.$this->poll->title)
            ->action('Zur Umfrage', url(route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
                'record' => $this->poll->getKey(),
            ])))
            ->line('Danke, dass du pr0p0ll nutzt.');
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
