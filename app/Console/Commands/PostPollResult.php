<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PostPollResultToPr0gramm;
use App\Models\Polls\Poll;
use App\Support\ResultPostConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PostPollResult extends Command
{
    protected $signature = 'app:post-poll-result';

    protected $description = 'Postet die Auswertung der am längsten geschlossenen, noch nicht verlinkten Umfrage auf pr0gramm (max. 1 pro Lauf).';

    public function handle(): int
    {
        $poll = Poll::query()->eligibleForResultPost()->orderBy('closes_at')->first();

        if ($poll === null) {
            $this->info('Kein Kandidat zum Posten gefunden.');
            Log::info('pr0gramm-autopost: kein Kandidat im täglichen Lauf.');

            return self::SUCCESS;
        }

        $aConfig = ResultPostConfig::fromArray($poll->result_post_config, $poll)->toArray();
        PostPollResultToPr0gramm::dispatch($poll, $aConfig);

        $this->info('Auswertung von Umfrage #'.$poll->getKey().' wurde zum Posten eingereiht.');
        Log::info('pr0gramm-autopost: Job eingereiht.', ['poll_id' => $poll->getKey(), 'trigger' => 'cron']);

        return self::SUCCESS;
    }
}
