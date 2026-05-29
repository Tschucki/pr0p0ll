<?php

declare(strict_types=1);

namespace App\Notifications\Email;

use App\Models\Abstracts\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Benachrichtigt einen Teilnehmer per E-Mail, wenn die Auswertung einer Umfrage auf pr0gramm veröffentlicht wurde.
class ParticipatedPollResultPublishedEmailNotification extends Notification
{
    use Queueable;

    public function __construct(private Poll $poll) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->poll->original_content_link
            ?? route('filament.pr0p0ll.resources.umfragen.results', ['record' => $this->poll->getKey()]);

        return (new MailMessage)
            ->subject('📊 Auswertung veröffentlicht: '.$this->poll->title)
            ->greeting('Hallo,')
            ->line('Die Auswertung einer Umfrage, an der du teilgenommen hast, wurde soeben auf pr0gramm veröffentlicht.')
            ->line('Umfrage: "'.$this->poll->title.'"')
            ->action('Zum Beitrag', $url)
            ->line('Danke, dass du pr0p0ll nutzt.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
