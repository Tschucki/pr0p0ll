<?php

declare(strict_types=1);

namespace App\Notifications\Discord;

use App\Models\Polls\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class NewPollAvailableDiscordNotification extends Notification
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
    public function via(object $notifiable): array
    {
        return [
            DiscordChannel::class,
        ];
    }

    public function toDiscord($notifiable): DiscordMessage
    {
        $url = route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
            'record' => $this->poll,
        ]);
        $title = $this->poll->title;
        $description = $this->poll->description ?? '';
        $category = $this->poll->category?->title ?? '';
        $user = $this->poll->not_anonymous ? $this->poll->user->name : 'Anonym';

        return DiscordMessage::create()
            ->embed([
                'type' => 'rich',
                'title' => '📊 Neue Umfrage verfügbar!',
                'description' => "Titel: {$title}\n\nBeschreibung: {$description}\n\nKategorie: {$category}\n\nBenutzer: {$user}\n\n{$url}",
                'color' => 0xEE4D2E,
                'url' => $url,
            ]);
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
