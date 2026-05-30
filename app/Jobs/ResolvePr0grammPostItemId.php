<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Services\Pr0grammBotService;
use App\Services\ResultPostPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

// items/post liefert (in prod) nur eine queueId, keine item.id. Dieser Job pollt die Bot-Uploads, bis das fertig
// verarbeitete Item dort gelistet ist (permanent, race-frei), und übergibt dessen finale Item-ID dem ResultPostPublisher.
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
        private int $uploadedAfter,
        private int $attempt = 1,
        private ?int $triggeredByUserId = null,
    ) {}

    public function handle(Pr0grammBotService $bot, ResultPostPublisher $publisher): void
    {
        $this->poll->refresh();

        if ($this->poll->original_content_link !== null) {
            return;
        }

        $bot->ensureLoggedIn();
        $itemId = $bot->findRecentUploadItemId($this->uploadedAfter);

        if ($itemId === null) {
            if ($this->attempt >= self::MAX_ATTEMPTS) {
                Log::error('pr0gramm-autopost: frischer Upload nach max Versuchen nicht in den Bot-Uploads gefunden.', [
                    'poll_id' => $this->poll->getKey(),
                    'uploaded_after' => $this->uploadedAfter,
                    'attempts' => $this->attempt,
                ]);

                $this->fail(new RuntimeException('pr0gramm-autopost: Item-ID für Poll '.$this->poll->getKey().' nicht auflösbar.'));

                return;
            }

            self::dispatch($this->poll, $this->uploadedAfter, $this->attempt + 1, $this->triggeredByUserId)
                ->delay(now()->addSeconds(self::RETRY_DELAY));

            return;
        }

        $publisher->publish($this->poll, $itemId);

        Log::info('pr0gramm-autopost: Item-ID über die Bot-Uploads aufgelöst.', [
            'poll_id' => $this->poll->getKey(),
            'item_id' => $itemId,
            'attempts' => $this->attempt,
        ]);
    }
}
