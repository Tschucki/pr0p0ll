<?php

declare(strict_types=1);

namespace App\Notifications\Discord;

use App\Models\Abstracts\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

// Benachrichtigt den Discord-Kanal, wenn eine Auswertung automatisch auf pr0gramm veröffentlicht wurde.
class ResultPublishedDiscordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Poll $poll) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            DiscordChannel::class,
        ];
    }

    public function toDiscord(object $notifiable): DiscordMessage
    {
        $title = $this->poll->title;
        $link = $this->poll->original_content_link;

        $description = "Titel: {$title}\n\nDie Auswertung dieser Umfrage wurde automatisch auf pr0gramm veröffentlicht.";

        if ($link) {
            $description .= "\n\n{$link}";
        }

        $embed = [
            'type' => 'rich',
            'title' => '📊 Auswertung automatisch veröffentlicht',
            'description' => $description,
            'color' => 0xEE4D2E,
        ];

        if ($link) {
            $embed['url'] = $link;
        }

        return DiscordMessage::create()->embed($embed);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
