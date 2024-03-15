<?php

declare(strict_types=1);

namespace App\Notifications\Telegram;

use App\Models\Polls\Poll;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class NewPollAvailableTelegramNotification extends Notification
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
            TelegramChannel::class,
        ];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $url = route('filament.pr0p0ll.resources.umfragen.teilnehmen', [
            'record' => $this->poll,
        ]);

        return TelegramMessage::create()
            ->to(config('services.telegram-bot-api.channel'))
            ->line('*📊 Neue Umfrage verfügbar!*'."\n")
            ->line("*Titel: {$this->poll->title}*\n")
            ->when($this->poll->description, function (TelegramMessage $message) {
                $message->line('*Beschreibung:* '.$this->poll->description);
            })
            ->when($this->poll->category, function (TelegramMessage $message) {
                $message->line("*\nKategorie:* {$this->poll->category->title}");
            })
            ->when($this->poll->not_anonymous, function (TelegramMessage $message) {
                $pr0UserName = $this->poll->not_anonymous ? $this->poll->user->name : 'Anonym';
                $pr0grammUserProfileUrl = 'https://pr0gramm.com/user/'.$pr0UserName;
                $message->line("*\nBenutzer:* [$pr0UserName]($pr0grammUserProfileUrl)");
            })
            ->line("*\nEndet in:* ".Carbon::make($this->poll->closes_after)?->diffForHumans()."\n")
            ->line("*\nURL:* [$url]($url)")
            ->button('Jetzt teilnehmen', $url);
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
