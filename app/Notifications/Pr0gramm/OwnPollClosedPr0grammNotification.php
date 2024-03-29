<?php

declare(strict_types=1);

namespace App\Notifications\Pr0gramm;

use App\Models\Polls\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pr0gramm\Pr0grammChannel;

class OwnPollClosedPr0grammNotification extends Notification
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

    public function toPr0gramm($notifiable): string
    {
        $url = route('filament.pr0p0ll.resources.umfragen.results', [
            'record' => $this->poll->getKey(),
        ]);
        $title = $this->poll->title;

        return "Hallo, deine Umfrage ({$title}) ist nun zu Ende. Du kannst jetzt einen pr0-Post erstellen.\n".'Auswertung: '.$url;
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
