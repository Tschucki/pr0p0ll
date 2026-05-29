<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\Email\ParticipatedPollResultPublishedEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

// Versendet eine E-Mail-Benachrichtigung an einen Teilnehmer, wenn die Auswertung seiner Umfrage auf pr0gramm veröffentlicht wurde.
class SendParticipatedPollResultPublishedEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;

    public int $backoff = 120;

    public function __construct(private Poll $poll, private User $user) {}

    public function handle(): void
    {
        $type = NotificationType::where('identifier', \App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED)->first();

        if ($type !== null
            && $this->user->email
            && $this->user->hasVerifiedEmail()
            && in_array('mail', $this->user->getNotificationRoutesForType($type), true)) {
            Notification::route('mail', [$this->user->email => $this->user->name])
                ->notify(new ParticipatedPollResultPublishedEmailNotification($this->poll));
        }
    }
}
