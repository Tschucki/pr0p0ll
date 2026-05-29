<?php

declare(strict_types=1);

namespace App\Notifications\Telegram;

use App\Models\Abstracts\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

// Benachrichtigt den Telegram-Kanal, wenn eine Auswertung automatisch auf pr0gramm veröffentlicht wurde.
class ResultPublishedTelegramNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Poll $poll) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            TelegramChannel::class,
        ];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->to(config('services.telegram-bot-api.channel'))
            ->line('*📊 Auswertung automatisch veröffentlicht!*'."\n")
            ->line("*Titel: {$this->poll->title}*\n")
            ->line('Die Auswertung dieser Umfrage wurde automatisch auf pr0gramm veröffentlicht.'."\n")
            ->when(
                $this->poll->original_content_link,
                function (TelegramMessage $message) {
                    $message->line('*Link:* '.$this->poll->original_content_link."\n");
                    $message->button('Auf pr0gramm ansehen', $this->poll->original_content_link);
                }
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
