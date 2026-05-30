<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendParticipatedPollResultPublishedEmailNotification;
use App\Jobs\SendParticipatedPollResultPublishedPr0grammNotification;
use App\Jobs\SendResultPublishedDiscordNotification;
use App\Jobs\SendResultPublishedTelegramNotification;
use App\Models\Abstracts\Poll;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

// Hinterlegt den pr0gramm-Post-Link am Poll und stößt alle Result-Published-Notifications an. Wird vom direkten
// Post-Pfad (item.id sofort vorhanden) und vom Resolver-Fallback (queueId-Polling) gemeinsam genutzt.
class ResultPostPublisher
{
    public function publish(Poll $poll, int $itemId): void
    {
        $poll->update(['original_content_link' => 'https://pr0gramm.com/new/'.$itemId]);

        SendResultPublishedTelegramNotification::dispatch($poll);
        SendResultPublishedDiscordNotification::dispatch($poll);

        $participatedType = NotificationType::where('identifier', \App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED)->first();

        if ($participatedType === null) {
            return;
        }

        $poll->participants()
            ->whereHas('notificationSettings', function (Builder $query) use ($participatedType): void {
                $query->where('notification_type_id', $participatedType->getKey())->where('enabled', true);
            })
            ->get()
            ->each(function (User $participant) use ($poll): void {
                SendParticipatedPollResultPublishedEmailNotification::dispatch($poll, $participant);
                SendParticipatedPollResultPublishedPr0grammNotification::dispatch($poll, $participant);
            });
    }
}
