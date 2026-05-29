<?php

declare(strict_types=1);

namespace App\Notifications\Pr0gramm;

use App\Models\Abstracts\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pr0gramm\Pr0grammChannel;

// Benachrichtigt einen Teilnehmer per pr0gramm-PN, wenn die Auswertung einer Umfrage veröffentlicht wurde.
class ParticipatedPollResultPublishedPr0grammNotification extends Notification
{
    use Queueable;

    public function __construct(private Poll $poll) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [Pr0grammChannel::class];
    }

    public function toPr0gramm(object $notifiable): string
    {
        $url = $this->poll->original_content_link
            ?? route('filament.pr0p0ll.resources.umfragen.results', ['record' => $this->poll->getKey()]);

        return 'Die Auswertung der Umfrage "'.$this->poll->title.'" wurde auf pr0gramm veröffentlicht.'."\n".'Zum Beitrag: '.$url;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
