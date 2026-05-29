<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\Pr0gramm\ParticipatedPollResultPublishedPr0grammNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Versendet eine pr0gramm-PN an einen Teilnehmer, wenn die Auswertung seiner Umfrage auf pr0gramm veröffentlicht wurde.
class SendParticipatedPollResultPublishedPr0grammNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;

    public int $backoff = 120;

    public function __construct(private Poll $poll, private User $user) {}

    public function handle(): void
    {
        $type = NotificationType::where('identifier', \App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED)->first();

        if ($type !== null && in_array('pr0gramm', $this->user->getNotificationRoutesForType($type), true)) {
            $this->user->notify(new ParticipatedPollResultPublishedPr0grammNotification($this->poll));
        }
    }
}
