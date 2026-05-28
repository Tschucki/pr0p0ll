<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Polls\Poll;
use App\Services\PollResultService;
use App\Support\ResultPostConfig;
use Illuminate\Contracts\View\View;

// Filament-freie Render-Seite der Auswertung (signierte URL), künftiges Browsershot-Ziel.
class PollResultRenderController extends Controller
{
    public function __invoke(Poll $poll): View
    {
        abort_unless($poll->hasEnded(), 404);

        $config = ResultPostConfig::fromArray($poll->result_post_config, $poll);
        $evaluation = (new PollResultService($poll))->buildEvaluation($config);

        return view('results.render', ['evaluation' => $evaluation]);
    }
}
