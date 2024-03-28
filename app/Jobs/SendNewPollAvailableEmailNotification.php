<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationType;
use App\Models\Polls\Poll;
use App\Models\User;
use App\Notifications\Email\NewPollAvailableEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendNewPollAvailableEmailNotification implements ShouldQueue
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
        if ($this->poll->userIsWithinTargetGroup($this->user) === false) {
            return;
        }
        if ($this->user->email === null) {
            return;
        }
        if ($this->user->hasVerifiedEmail() === false) {
            return;
        }
        if (in_array('mail', $this->user->getNotificationRoutesForType(NotificationType::where('identifier', \App\Enums\NotificationType::NEWPOLLPUBLISHED)->first()), true) === false) {
            return;
        }

        Notification::route('mail', [$this->user->email => $this->user->name])->notify(new NewPollAvailableEmailNotification($this->poll));
    }
}
