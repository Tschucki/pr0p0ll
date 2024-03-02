<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Abstracts\Poll;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PollDeniedNotification extends Notification
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hallo '.$notifiable->name.',')
            ->line('Deine Umfrage mit dem Titel "'.$this->poll->title.'" wurde abgelehnt.')
            ->line('Grund: ')
            ->line($this->poll->admin_notes)
            ->action('Zur Umfrage', url(route('filament.pr0p0ll.resources.my-polls.view', [
                'record' => $this->poll->getKey(),
            ])))
            ->line('Danke, dass du Pr0p0ll nutzt.');
    }

    public function toPr0gramm($notifiable): string
    {
        $url = route('filament.pr0p0ll.resources.my-polls.view', [
            'record' => $this->poll->getKey(),
        ]);
        $title = $this->poll->title;
        $reason = $this->poll->admin_notes;

        return "Hallo, deine Umfrage ({$title}) wurde abgelehnt.\nGrund:\n{$reason}\n".'Zur Umfrage: '.$url;
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
