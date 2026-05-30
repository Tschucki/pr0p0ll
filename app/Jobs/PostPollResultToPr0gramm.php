<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Services\PollResultScreenshotService;
use App\Services\Pr0grammBotService;
use App\Services\ResultPostPublisher;
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

// Lädt die Auswertung als Bild zu pr0gramm hoch. items/post liefert die item.id i.d.R. direkt zurück; fehlt sie
// (Bild noch in Verarbeitung), wird die Auflösung per queueId an ResolvePr0grammPostItemId nachgelagert.
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

    public function handle(Pr0grammBotService $bot, ResultPostPublisher $publisher): void
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

            $response = Pr0grammApi::Post()->post(
                key: $key,
                tags: $tags,
                siteUrl: $siteUrl,
                comment: $comment,
            );

            $itemId = $response->json('item.id');

            if (is_numeric($itemId)) {
                $publisher->publish($this->poll, (int) $itemId);

                Log::info('pr0gramm-autopost: gepostet, item.id direkt aus der Antwort.', [
                    'poll_id' => $this->poll->getKey(),
                    'item_id' => (int) $itemId,
                    'trigger' => $trigger,
                ]);

                return;
            }

            // In prod liefert items/post keine item.id, sondern nur success + queueId. queueId belegt die Akzeptanz;
            // die finale Item-ID holen wir anschließend aus den Bot-Uploads (das fertige Item ist dort permanent gelistet).
            if ($response->json('success') !== true || ! is_numeric($response->json('queueId'))) {
                throw new RuntimeException('pr0gramm-autopost: Post nicht akzeptiert (weder item.id noch erfolgreiche queueId): '.$response->body());
            }

            // Post-Zeitpunkt mit Puffer für Server-Clock-Skew — der frische Upload hat created >= diesem Wert.
            $uploadedAfter = now()->subMinutes(2)->timestamp;

            // Upload markieren (verhindert Doppel-Post im Auflösungs-Fenster), dann die Item-ID nachladen.
            $this->poll->update(['result_post_uploaded_at' => now()]);

            ResolvePr0grammPostItemId::dispatch(
                $this->poll,
                $uploadedAfter,
                triggeredByUserId: $this->triggeredByUserId,
            )->delay(now()->addSeconds(ResolvePr0grammPostItemId::RETRY_DELAY));

            Log::info('pr0gramm-autopost: gepostet, Item-ID-Auflösung über Bot-Uploads angestoßen.', [
                'poll_id' => $this->poll->getKey(),
                'queue_id' => (int) $response->json('queueId'),
                'trigger' => $trigger,
            ]);
        } finally {
            Storage::disk('local')->delete($relPath);
        }
    }
}
