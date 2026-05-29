<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use Tschucki\Pr0grammApi\Facades\Pr0grammApi;
use Tschucki\Pr0grammApi\Pr0grammApi as Pr0grammApiClient;

// Kapselt die pr0gramm-Bot-Session und das Auflösen frisch hochgeladener Uploads zur Item-ID.
class Pr0grammBotService
{
    public function ensureLoggedIn(): void
    {
        if (Pr0grammApi::loggedIn()['loggedIn'] === true) {
            return;
        }

        Pr0grammApi::login(config('services.pr0gramm.username'), config('services.pr0gramm.password'));

        // Facade-Instanz inkl. statischem Cookie verwerfen, damit der nächste Zugriff den frischen Session-Cookie liest.
        Facade::clearResolvedInstance(Pr0grammApiClient::class);
    }

    // Sucht den neuesten Bot-Upload (created >= $uploadedAfter) der den Umfrage-Titel als Tag trägt und liefert dessen Item-ID.
    public function findRecentUploadItemId(string $expectedTitleTag, int $uploadedAfter): ?int
    {
        $expected = mb_strtolower(trim($expectedTitleTag));

        if ($expected === '') {
            return null;
        }

        $aItems = Pr0grammApi::Post()->get([
            'user' => config('services.pr0gramm.username'),
            'flags' => 31,
        ])->json('items', []);

        foreach ($aItems as $aItem) {
            $itemId = (int) ($aItem['id'] ?? 0);

            if ($itemId === 0 || (int) ($aItem['created'] ?? 0) < $uploadedAfter) {
                continue;
            }

            $aTags = Pr0grammApi::Post()->info($itemId)->json('tags', []);

            foreach ($aTags as $aTag) {
                if (mb_strtolower(trim((string) ($aTag['tag'] ?? ''))) === $expected) {
                    return $itemId;
                }
            }
        }

        return null;
    }
}
