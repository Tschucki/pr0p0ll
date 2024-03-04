<?php

declare(strict_types=1);

namespace App\Notifications\Pr0gramm;

use App\Models\Polls\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pr0gramm\Pr0grammChannel;

class PollAcceptedPr0grammNotification extends Notification
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
            Pr0grammChannel::class,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hallo,')
            ->line('Deine Umfrage mit dem Titel "'.$this->poll->title.'" wurde genehmigt und ist nun öffentlich sichtbar.')
            ->action('An Umfrage teilnehmen', url(route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
                'record' => $this->poll->getKey(),
            ])))
            ->line('Danke, dass du Pr0p0ll nutzt.');
    }

    public function toPr0gramm($notifiable): string
    {
        $url = route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
            'record' => $this->poll->getKey(),
        ]);
        $title = $this->poll->title;

        return "Hallo, deine Umfrage ({$title}) wurde genehmigt und ist nun öffentlich sichtbar.\n".'Teilnehmen: '.$url;
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
