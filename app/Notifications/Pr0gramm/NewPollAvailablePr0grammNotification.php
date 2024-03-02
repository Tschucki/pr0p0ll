<?php

declare(strict_types=1);

namespace App\Notifications\Pr0gramm;

use App\Models\Polls\PublicPoll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pr0gramm\Pr0grammChannel;

class NewPollAvailablePr0grammNotification extends Notification
{
    use Queueable;

    private PublicPoll $poll;

    /**
     * Create a new notification instance.
     */
    public function __construct(PublicPoll $poll)
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
            Pr0grammChannel::class,
        ];
    }

    public function toPr0gramm($notifiable): string
    {
        $url = route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
            'record' => $this->poll,
        ]);

        return "Hallo, es wurde eine neue Umfrage veröffentlicht.\n".$url;
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
