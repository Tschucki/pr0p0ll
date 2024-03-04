<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Polls\Poll;
use App\Models\User;
use App\Notifications\Telegram\NewPollAvailableTelegramNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendPollAcceptedTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Poll $poll;

    private User $user;

    public int $tries = 15;

    public int $backoff = 120;

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
        Notification::route('telegram', config('services.telegram-bot-api.channel'))->notify(new NewPollAvailableTelegramNotification($this->poll));
    }
}
