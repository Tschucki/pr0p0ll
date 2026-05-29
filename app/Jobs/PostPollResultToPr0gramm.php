<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Services\PollResultScreenshotService;
use App\Services\Pr0grammBotService;
use App\Support\ResultPostConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tschucki\Pr0grammApi\Facades\Pr0grammApi;

// Lädt die Auswertung als Bild zu pr0gramm hoch. Die Item-ID liefert pr0gramm erst nach asynchroner Bildverarbeitung,
// daher wird sie nicht hier erwartet, sondern per ResolvePr0grammPostItemId nachgelagert aufgelöst.
class PostPollResultToPr0gramm implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;

    public int $backoff = 120;

    public int $timeout = 180;

    public function __construct(
        private Poll $poll,
        private array $aConfig,
        private ?int $triggeredByUserId = null,
    ) {}

    public function uniqueId(): string
    {
        return 'post-poll-result-'.$this->poll->getKey();
    }

    public function handle(Pr0grammBotService $bot): void
    {
        $trigger = $this->triggeredByUserId === null ? 'cron' : 'admin:'.$this->triggeredByUserId;
        $this->poll->refresh();

        if (! $this->poll->isEligibleForResultPost()) {
            Log::info('pr0gramm-autopost: Poll nicht (mehr) qualifiziert, übersprungen.', [
                'poll_id' => $this->poll->getKey(),
                'trigger' => $trigger,
            ]);

            return;
        }

        $config = ResultPostConfig::fromArray($this->aConfig, $this->poll);
        $tags = $config->tags ?? ResultPostConfig::defaultTags($this->poll);
        $comment = $config->comment ?? ResultPostConfig::defaultComment($this->poll);
        $siteUrl = route('filament.pr0p0ll.resources.umfragen.results', ['record' => $this->poll->getKey()]);

        $bot->ensureLoggedIn();

        $relPath = 'result-screenshots/post-'.$this->poll->getKey().'.png';
        $png = app(PollResultScreenshotService::class)->png($this->poll, $config);
        Storage::disk('local')->put($relPath, $png);
        $absPath = Storage::disk('local')->path($relPath);

        try {
            Log::info('pr0gramm-autopost: lade Screenshot hoch.', ['poll_id' => $this->poll->getKey(), 'trigger' => $trigger]);
            $key = Pr0grammApi::Post()->upload($absPath)->json('key');

            if (! is_string($key) || $key === '') {
                throw new RuntimeException('pr0gramm-autopost: kein Upload-Key in der Antwort.');
            }

            Pr0grammApi::Post()->post(
                key: $key,
                tags: $tags,
                siteUrl: $siteUrl,
                comment: $comment,
            );

            // Upload markieren (verhindert Doppel-Post im Auflösungs-Fenster), dann die Item-ID nachgelagert pollen.
            $this->poll->update(['result_post_uploaded_at' => now()]);

            ResolvePr0grammPostItemId::dispatch(
                $this->poll,
                ResultPostConfig::titleTag($this->poll),
                now()->subMinutes(2)->timestamp,
                triggeredByUserId: $this->triggeredByUserId,
            )->delay(now()->addSeconds(ResolvePr0grammPostItemId::RETRY_DELAY));

            Log::info('pr0gramm-autopost: Screenshot hochgeladen, Item-ID-Auflösung angestoßen.', [
                'poll_id' => $this->poll->getKey(),
                'trigger' => $trigger,
            ]);
        } finally {
            Storage::disk('local')->delete($relPath);
        }
    }
}
