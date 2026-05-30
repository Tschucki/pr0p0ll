<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tschucki\Pr0grammApi\Facades\Pr0grammApi;
use Tschucki\Pr0grammApi\Pr0grammApi as Pr0grammApiClient;

// Kapselt die pr0gramm-Bot-Session und das Auflösen eines Uploads in der Verarbeitungs-Queue zur finalen Item-ID.
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

    // Liefert die Item-ID des neuesten Bot-Uploads, der frühestens zum Post-Zeitpunkt erstellt wurde (sonst null,
    // solange pr0gramm das Bild noch verarbeitet und es noch nicht in den Uploads gelistet ist).
    public function findRecentUploadItemId(int $uploadedAfter): ?int
    {
        $cookie = config('services.pr0gramm.cookie') ?? Session::get('pr0gramm.cookie')[0] ?? null;

        $aItems = Http::withHeaders(['Cookie' => $cookie])
            ->get('https://pr0gramm.com/api/items/get', [
                'user' => config('services.pr0gramm.username'),
                'flags' => 31,
            ])
            ->json('items', []);

        // items/get liefert neueste zuerst — der erste Treffer ab dem Post-Zeitpunkt ist unser frischer Upload.
        foreach ($aItems as $aItem) {
            if ((int) ($aItem['created'] ?? 0) >= $uploadedAfter) {
                return (int) ($aItem['id'] ?? 0) ?: null;
            }
        }

        return null;
    }
}
