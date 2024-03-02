<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Polls\Poll;
use App\Notifications\Discord\NewPollAvailableDiscordNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendPollPublishedDiscordNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Poll $poll;

    /**
     * Create a new job instance.
     */
    public function __construct(Poll $poll)
    {
        $this->poll = $poll;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Notification::route('discord', config('services.discord.channel_id'))->notify(new NewPollAvailableDiscordNotification($this->poll));
    }
}
