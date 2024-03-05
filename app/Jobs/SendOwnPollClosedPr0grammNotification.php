<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationType;
use App\Models\Polls\Poll;
use App\Models\User;
use App\Notifications\Pr0gramm\PollAcceptedPr0grammNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOwnPollClosedPr0grammNotification implements ShouldQueue
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
        if (in_array('pr0gramm', $this->user->getNotificationRoutesForType(NotificationType::where('identifier', \App\Enums\NotificationType::OWNPOLLHASENDED)->first()), true)) {
            $this->user->notify(new PollAcceptedPr0grammNotification($this->poll));
        }
    }
}
