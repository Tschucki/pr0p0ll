<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Notifications\Discord\ResultPublishedDiscordNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

// Sendet eine Discord-Kanal-Benachrichtigung, wenn eine Poll-Auswertung automatisch auf pr0gramm veröffentlicht wurde.
class SendResultPublishedDiscordNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;

    public int $backoff = 120;

    public function __construct(private readonly Poll $poll) {}

    public function handle(): void
    {
        Notification::route('discord', config('services.discord.channel_id'))
            ->notify(new ResultPublishedDiscordNotification($this->poll));
    }
}
