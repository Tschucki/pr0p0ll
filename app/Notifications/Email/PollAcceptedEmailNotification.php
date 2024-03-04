<?php

declare(strict_types=1);

namespace App\Notifications\Email;

use App\Models\Polls\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PollAcceptedEmailNotification extends Notification
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
    public function via($notifiable): array
    {
        return [
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📊 Deine Umfrage wurde genehmigt! #'.$this->poll->getKey())
            ->greeting('Hallo,')
            ->line('Deine Umfrage mit dem Titel "'.$this->poll->title.'" wurde genehmigt und ist nun öffentlich sichtbar.')
            ->action('An Umfrage teilnehmen', url(route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
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
