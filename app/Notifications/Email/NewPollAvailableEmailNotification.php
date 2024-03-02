<?php

declare(strict_types=1);

namespace App\Notifications\Email;

use App\Models\Polls\PublicPoll;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;
use NotificationChannels\Pr0gramm\Pr0grammChannel;
use NotificationChannels\Telegram\TelegramMessage;

class NewPollAvailableEmailNotification extends Notification
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
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📊 Neue Umfrage verfügbar! #' . $this->poll->getKey())
            ->greeting('Hallo '.$notifiable->name.',')
            ->line('Es ist eine neue Umfrage auf Pr0p0ll verfügbar. Titel "'.$this->poll->title.'" wurde abgelehnt.')
            ->line('Titel: ')
            ->line($this->poll->title)
            ->action('Zur Umfrage', url(route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
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
