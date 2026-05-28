<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerateResultPostScreenshot;
use App\Models\Polls\Poll;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Liefert den vom Queue-Job erzeugten Auswertungs-Screenshot zum Download.
class PollResultImageController extends Controller
{
    public function __invoke(Poll $poll): StreamedResponse
    {
        $user = Auth::user();
        abort_unless(
            $user !== null && ($user->isAdmin() || $poll->user_id === $user->getKey() || $poll->resultsArePublic()),
            403,
        );

        $path = GenerateResultPostScreenshot::pathFor($poll->getKey());
        abort_unless(Storage::exists($path), 404);

        return Storage::download($path, 'auswertung-'.$poll->getKey().'.png');
    }
}
