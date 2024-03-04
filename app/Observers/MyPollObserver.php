<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Polls\MyPoll;
use App\Models\Polls\Poll;
use App\Models\User;
use App\Notifications\PollNeedsReviewNotification;
use Illuminate\Support\Facades\Notification;

class MyPollObserver
{
    /**
     * Handle the MyPoll "created" event.
     */
    public function created(MyPoll $myPoll): void
    {
        //
    }

    /**
     * Handle the MyPoll "updated" event.
     */
    public function updated(MyPoll $myPoll): void
    {
        // Check if the poll has been turned in for review
        if ($myPoll->isDirty('in_review') && $myPoll->isInReview()) {
            // Send a notification to the admins
            $poll = Poll::find($myPoll->getKey());
            Notification::send(User::admin()->get(), new PollNeedsReviewNotification($poll));
        }
    }

    /**
     * Handle the MyPoll "deleted" event.
     */
    public function deleted(MyPoll $myPoll): void
    {
        //
    }

    /**
     * Handle the MyPoll "restored" event.
     */
    public function restored(MyPoll $myPoll): void
    {
        //
    }

    /**
     * Handle the MyPoll "force deleted" event.
     */
    public function forceDeleted(MyPoll $myPoll): void
    {
        //
    }
}
