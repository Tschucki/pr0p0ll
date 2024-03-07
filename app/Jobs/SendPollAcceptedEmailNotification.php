<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationType;
use App\Models\Polls\Poll;
use App\Models\User;
use App\Notifications\Email\PollAcceptedEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendPollAcceptedEmailNotification implements ShouldQueue
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
        if ($this->user->email && $this->user->hasVerifiedEmail() && in_array('mail', $this->user->getNotificationRoutesForType(NotificationType::where('identifier', \App\Enums\NotificationType::POLLACCEPTED)->first()), true)) {
            Notification::route('mail', [$this->user->email => $this->user->name])->notify(new PollAcceptedEmailNotification($this->poll));
        }
    }
}
