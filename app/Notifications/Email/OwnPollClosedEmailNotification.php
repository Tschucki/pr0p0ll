<?php

declare(strict_types=1);

namespace App\Notifications\Email;

use App\Models\Polls\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OwnPollClosedEmailNotification extends Notification
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
            ->subject('📊 Deine Umfrage ist zu Ende! #'.$this->poll->getKey())
            ->greeting('Hallo,')
            ->line('Deine Umfrage "'.$this->poll->title.'" ist zu Ende.')
            ->line('Du kannst jetzt einen pr0-Post mit den Ergebnissen erstellen.')
            ->action('Zur Auswerung', url(route('filament.pr0p0ll.resources.my-polls.results', [
                'record' => $this->poll->getKey(),
            ])))
            ->line('Danke, dass du Pr0p0ll nutzt.');
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
