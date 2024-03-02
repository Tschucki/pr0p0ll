<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Models\User;
use App\Notifications\PollAcceptedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendPollAcceptedPr0grammNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Poll $poll;

    private User $user;

    public int $tries = 15;

    public int $backoff = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(Poll $poll, User $user)
    {
        $this->poll = $poll;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Notification::route('pr0gramm', $this->user->name)->notify(new PollAcceptedNotification($this->poll));
    }
}
