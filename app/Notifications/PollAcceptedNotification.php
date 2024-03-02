<?php

namespace App\Notifications;

use App\Models\Abstracts\Poll;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PollAcceptedNotification extends Notification
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
    public function via(User $notifiable): array
    {
        return $notifiable->getNotificationRoutesForType(NotificationType::where('identifier', \App\Enums\NotificationType::POLLACCEPTED)->first());
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hallo ' . $notifiable->name . ',')
            ->line('Deine Umfrage mit dem Titel "' . $this->poll->title . '" wurde genehmigt und ist nun öffentlich sichtbar.')
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

        return "Hallo, deine Umfrage ({$title}) wurde genehmigt und ist nun öffentlich sichtbar.\n" . "Teilnehmen: " . $url;
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
