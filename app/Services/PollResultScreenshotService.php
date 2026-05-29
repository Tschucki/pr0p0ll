<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Abstracts\Poll;
use App\Support\ResultPostConfig;
use Spatie\Browsershot\Browsershot;

// Erzeugt einen server-seitigen PNG-Screenshot der Auswertung über Browsershot.
class PollResultScreenshotService
{
    public function png(Poll $poll, ?ResultPostConfig $config = null): string
    {
        $config ??= ResultPostConfig::fromArray($poll->result_post_config, $poll);
        $evaluation = (new PollResultService($poll))->buildEvaluation($config);

        $html = view('results.render', ['evaluation' => $evaluation])->render();

        $shot = Browsershot::html($html)
            ->windowSize(1100, 800)
            ->deviceScaleFactor(2)
            ->fullPage()
            ->noSandbox()
            ->setScreenshotType('png');

        if ($chromePath = config('pr0p0ll.chrome_path')) {
            $shot->setChromePath($chromePath);
        }
        if ($nodeBinary = config('pr0p0ll.node_binary')) {
            $shot->setNodeBinary($nodeBinary);
        }
        if ($npmBinary = config('pr0p0ll.npm_binary')) {
            $shot->setNpmBinary($npmBinary);
        }

        return $shot->screenshot();
    }
}
