<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Abstracts\Poll;
use App\Models\User;
use App\Services\PollResultScreenshotService;
use App\Support\ResultPostConfig;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

// Erzeugt den Auswertungs-Screenshot async (Browsershot) und schickt dem User eine DB-Notification mit Download-Link.
class GenerateResultPostScreenshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;

    public int $backoff = 120;

    public int $timeout = 120;

    public function __construct(private Poll $poll, private User $user, private array $aConfig) {}

    public function handle(): void
    {
        $config = ResultPostConfig::fromArray($this->aConfig, $this->poll);
        $png = app(PollResultScreenshotService::class)->png($this->poll, $config);

        Storage::put(self::pathFor($this->poll->getKey()), $png);

        Notification::make()
            ->title('Auswertungs-Bild ist fertig')
            ->body('Dein Bild für „'.$config->title.'" steht zum Download bereit.')
            ->success()
            ->actions([
                Action::make('download')
                    ->label('Herunterladen')
                    ->url(route('poll.results.image', ['poll' => $this->poll->getKey()]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($this->user);
    }

    public static function pathFor(int|string $pollId): string
    {
        return 'result-screenshots/poll-'.$pollId.'.png';
    }
}
