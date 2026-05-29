<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Services\PollResultScreenshotService;
use App\Support\ResultPostConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tschucki\Pr0grammApi\Facades\Pr0grammApi;
use Tschucki\Pr0grammApi\Pr0grammApi as Pr0grammApiClient;

// Postet die Auswertung eines Polls als Bild-Beitrag auf pr0gramm und hinterlegt den Post-Link beim Poll.
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

    public function handle(): void
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
        $siteUrl = URL::signedRoute('poll.results.render', ['poll' => $this->poll->getKey()]);

        $this->ensureLoggedIn($trigger);

        $relPath = 'result-screenshots/post-'.$this->poll->getKey().'.png';
        $png = app(PollResultScreenshotService::class, ['poll' => $this->poll])->png($config);
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

            $itemId = $response->json('itemId') ?? $response->json('item.id');

            if (! is_numeric($itemId)) {
                throw new RuntimeException('pr0gramm-autopost: keine Item-ID in der Antwort: '.$response->body());
            }

            $postUrl = 'https://pr0gramm.com/new/'.$itemId;
            $this->poll->update(['original_content_link' => $postUrl]);

            SendResultPublishedTelegramNotification::dispatch($this->poll);
            SendResultPublishedDiscordNotification::dispatch($this->poll);

            Log::info('pr0gramm-autopost: erfolgreich gepostet.', [
                'poll_id' => $this->poll->getKey(),
                'item_id' => $itemId,
                'post_url' => $postUrl,
                'trigger' => $trigger,
            ]);
        } finally {
            Storage::disk('local')->delete($relPath);
        }
    }

    private function ensureLoggedIn(string $trigger): void
    {
        if (Pr0grammApi::loggedIn()['loggedIn'] === true) {
            return;
        }

        Log::info('pr0gramm-autopost: Bot-Login.', ['trigger' => $trigger]);
        Pr0grammApi::login(config('services.pr0gramm.username'), config('services.pr0gramm.password'));

        // Facade-Instanz inkl. statischem Cookie verwerfen, damit der nächste Zugriff den frischen Session-Cookie liest.
        Facade::clearResolvedInstance(Pr0grammApiClient::class);
    }
}
