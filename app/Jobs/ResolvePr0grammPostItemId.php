<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Models\NotificationType;
use App\Models\User;
use App\Services\Pr0grammBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

// pr0gramm liefert beim Upload keine Item-ID (Bild geht erst in die Verarbeitungs-Queue). Dieser Job pollt die
// Bot-Uploads bis der frische Post auftaucht, hinterlegt den Post-Link und stößt die Result-Notifications an.
class ResolvePr0grammPostItemId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const int MAX_ATTEMPTS = 15;

    public const int RETRY_DELAY = 20;

    public int $tries = 15;

    public int $backoff = 120;

    public int $timeout = 60;

    public function __construct(
        private Poll $poll,
        private string $expectedTitleTag,
        private int $uploadedAfter,
        private int $attempt = 1,
        private ?int $triggeredByUserId = null,
    ) {}

    public function handle(Pr0grammBotService $bot): void
    {
        $this->poll->refresh();

        if ($this->poll->original_content_link !== null) {
            return;
        }

        $bot->ensureLoggedIn();
        $itemId = $bot->findRecentUploadItemId($this->expectedTitleTag, $this->uploadedAfter);

        if ($itemId === null) {
            if ($this->attempt >= self::MAX_ATTEMPTS) {
                Log::error('pr0gramm-autopost: Item-ID nach max Versuchen nicht gefunden.', [
                    'poll_id' => $this->poll->getKey(),
                    'expected_tag' => $this->expectedTitleTag,
                    'attempts' => $this->attempt,
                ]);

                $this->fail(new RuntimeException('pr0gramm-autopost: Item-ID für Poll '.$this->poll->getKey().' nicht auflösbar.'));

                return;
            }

            self::dispatch($this->poll, $this->expectedTitleTag, $this->uploadedAfter, $this->attempt + 1, $this->triggeredByUserId)
                ->delay(now()->addSeconds(self::RETRY_DELAY));

            return;
        }

        $this->finalize($itemId);
    }

    private function finalize(int $itemId): void
    {
        $postUrl = 'https://pr0gramm.com/new/'.$itemId;
        $this->poll->update(['original_content_link' => $postUrl]);

        SendResultPublishedTelegramNotification::dispatch($this->poll);
        SendResultPublishedDiscordNotification::dispatch($this->poll);

        $participatedType = NotificationType::where('identifier', \App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED)->first();

        if ($participatedType !== null) {
            $this->poll->participants()
                ->whereHas('notificationSettings', function (Builder $query) use ($participatedType): void {
                    $query->where('notification_type_id', $participatedType->getKey())->where('enabled', true);
                })
                ->get()
                ->each(function (User $participant): void {
                    SendParticipatedPollResultPublishedEmailNotification::dispatch($this->poll, $participant);
                    SendParticipatedPollResultPublishedPr0grammNotification::dispatch($this->poll, $participant);
                });
        }

        Log::info('pr0gramm-autopost: Item-ID aufgelöst.', [
            'poll_id' => $this->poll->getKey(),
            'item_id' => $itemId,
            'post_url' => $postUrl,
            'attempts' => $this->attempt,
        ]);
    }
}
