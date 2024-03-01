<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Polls\Poll;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewPollAvailableNotification extends Notification
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
            'telegram',
            'pr0gramm',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $poll = Poll::first();
        $url = route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
            'record' => $poll,
        ]);

        return TelegramMessage::create()
            ->to(config('services.telegram-bot-api.channel'))
            ->line('*📊 Neue Umfrage verfügbar!*'."\n")
            ->line("*Titel: {$poll->title}*\n")
            ->when($poll->description, function (TelegramMessage $message) use ($poll) {
                $message->line('*Beschreibung:* '.$poll->description);
            })
            ->when($poll->category, function (TelegramMessage $message) use ($poll) {
                $message->line("*\nKategorie:* {$poll->category->title}");
            })
            ->when($poll->not_anonymous, function (TelegramMessage $message) use ($poll) {
                $pr0UserName = $poll->user->name;
                $pr0grammUserProfileUrl = 'https://pr0gramm.com/'.$pr0UserName;
                $message->line("*\nBenutzer:* [$pr0UserName]($pr0grammUserProfileUrl)");
            })
            ->line("*\nEndet in:* ".Carbon::make($poll->closes_after)?->diffForHumans()."\n")
            ->line("*\nURL:* [$url]($url)")
            ->button('Jetzt teilnehmen', $url);
    }

    public function toPr0gramm($notifiable): string
    {
        $url = route('filament.pr0p0ll.resources.public-polls.teilnehmen', [
            'record' => $this->poll,
        ]);

        return "Hallo, es wurde eine neue Umfrage veröffentlicht.\n" . $url;
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
