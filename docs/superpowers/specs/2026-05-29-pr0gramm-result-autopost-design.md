# Design: Automatisiertes Posten von Umfrage-Auswertungen auf pr0gramm

**Datum:** 2026-05-29
**Status:** Approved (Brainstorming)
**Stack (real):** Laravel 13 · Filament 5 · Livewire 4 · PHP 8.3 · Pest 4 · Horizon 5 (Redis-Queue `default`)

> Hinweis: `CLAUDE.md` / `.ai/knowledge-base` sind teilweise stale (nennen L10/F3). Maßgeblich ist der reale Stack oben.

---

## 1. Ziel

Täglich automatisiert **genau eine** Umfrage-Auswertung als Bild-Post auf pr0gramm veröffentlichen — sowie ein Admin-Button zum manuellen Anstoßen. Kein Spam: max. 1 automatischer Post pro Tag.

### Qualifikations-Bedingung (welche Umfragen)

Eine Umfrage qualifiziert, wenn **alle** gelten:

- `approved = true`
- `original_content_link IS NULL` (= **kein Post-Link hinterlegt**)
- `closes_at IS NOT NULL` **und** `closes_at <= now() - 2 Wochen` (= **länger als 2 Wochen geschlossen**)

`original_content_link` **ist** der „Post-Link". Nach erfolgreichem Posten wird die pr0gramm-Post-URL dorthin geschrieben → die Umfrage erfüllt die Bedingung nicht mehr und fällt automatisch aus dem Pool (Self-Exclusion, dient zugleich als Idempotenz-Anker).

---

## 2. Bereits vorhandene Infrastruktur (wird wiederverwendet)

| Baustein | Rolle |
|---|---|
| `polls.original_content_link` (text, nullable) | Post-Link; Write-back-Ziel. **Keine neue Spalte nötig.** |
| `polls.result_post_config` (JSON) | Per-Poll-Config (Titel, Beschreibung, Farbe, Demografie, pro-Frage-Charts) |
| `App\Support\ResultPostConfig` | DTO der Config (`default` / `fromArray` / `toArray` / `fromFlatForm` / `toFlatForm`) |
| `App\Services\PollResultScreenshotService::png(config): string` | Browsershot-Screenshot der Render-Page → PNG-Bytes |
| `App\Http\Controllers\PollResultRenderController` | Filament-freie Render-Page (Browsershot-Ziel) |
| `App\Filament\Pages\Pr0PostCreator` | Admin-Page: konfiguriert Post, speichert `result_post_config`, dispatcht `GenerateResultPostScreenshot` (nur Download-Bild). **Heimat des manuellen Buttons.** |
| `Tschucki\Pr0grammApi` (`Post()->upload/post`, `Comment()`) | pr0gramm-API (Bot-Account via `services.pr0gramm.username/password`) |
| Scheduler `bootstrap/app.php` → `withSchedule()` | L13-Scheduling-Ort (bereits 2 Einträge) |

### Cookie/Auth-Muster (wichtig)

`LoginToPr0gramm` (hourly) legt den Cookie in `Session` ab → im CLI/Queue-Kontext **nutzlos** (keine persistente Session). Das funktionierende Muster ist `NotificationChannels\Pr0gramm\Pr0grammChannel`: vor Nutzung `Pr0grammApi::loggedIn()` prüfen, sonst **frisch** `Pr0grammApi::login(config('services.pr0gramm.username'), config('services.pr0gramm.password'))`. → Posting-Job loggt sich selbst frisch ein.

---

## 3. Komponenten (neu / geändert)

### 3.1 Eligibility — `app/Models/Abstracts/Poll.php`

Neue, dedizierte Prädikate (sauberer Schnitt; `resultsArePublic()` mischt zwei Branches und wird nicht verändert):

```php
public function scopeEligibleForResultPost(Builder $query): Builder
{
    return $query
        ->where('approved', true)
        ->whereNull('original_content_link')
        ->whereNotNull('closes_at')
        ->where('closes_at', '<=', now()->subWeeks(2));
}

public function isEligibleForResultPost(): bool
{
    return $this->approved
        && $this->original_content_link === null
        && $this->closes_at !== null
        && Carbon::make($this->closes_at)->lte(now()->subWeeks(2));
}
```

`closes_at` kann bei Alt-Polls `null` sein → der Scope filtert sie korrekt heraus.

### 3.2 Config-Erweiterung — `App\Support\ResultPostConfig` + `Pr0PostCreator`-Form

Zwei neue Felder, im Admin editierbar, leer = Auto-Default:

- `public string $tags` — Default: `"pr0p0ll,Umfrage,Auswertung,<Poll-Titel>"`
- `public ?string $comment` — Default: `"<Poll-Titel> — alle Ergebnisse auf pr0p0ll: <Results-Link>"`

Anzupassen: `default()`, `fromArray()`, `toArray()`, `fromFlatForm()`, `toFlatForm()` (flache Form-Keys `tags`, `comment`). Im `Pr0PostCreator`-Schema zwei Felder ergänzen (TextInput `tags`, Textarea `comment`), Platzhalter zeigt Auto-Wert.

### 3.3 Job — `app/Jobs/PostPollResultToPr0gramm.php`

```php
class PostPollResultToPr0gramm implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;
    public int $backoff = 120;
    public int $timeout = 180;

    public function __construct(
        private Poll $poll,
        private array $aConfig,
        private ?int $triggeredByUserId = null, // null = Cron
    ) {}

    public function uniqueId(): string { return 'post-result-'.$this->poll->getKey(); }
}
```

`handle()`-Ablauf:

1. **Guard:** `$this->poll->refresh(); if (! $poll->isEligibleForResultPost()) { Log::info(...); return; }` — Idempotenz gegen Doppelpost.
2. **Login:** `if (! Pr0grammApi::loggedIn()['loggedIn']) Pr0grammApi::login(config(username), config(password));`
3. **Screenshot:** `$png = (new PollResultScreenshotService($poll))->png($config);` → in Temp-Datei schreiben (`tempnam` / `Storage::put` + lokaler Pfad — `upload()` braucht einen Datei-Pfad).
4. **Upload:** `$key = Pr0grammApi::Post()->upload($tmpPath)` → `key` aus Response parsen. Log.
5. **Post (inkl. Kommentar in einem Call):**
   ```php
   $resp = Pr0grammApi::Post()->post(
       key: $key,
       tags: $config->tags,
       siteUrl: $resultsUrl,   // siehe offene Punkte
       comment: $config->comment,
   );
   ```
   `itemId` aus Response parsen. Log.
6. **Write-back (sofort, vor allem Weiteren):** `$poll->update(['original_content_link' => 'https://pr0gramm.com/new/'.$itemId]);` — schließt Poll aus dem Pool aus; Retry sieht Link gesetzt → Guard bricht ab.
7. **Cleanup:** Temp-Datei löschen.
8. **Logging:** `Log::info/warning/error` mit Kontext `['poll_id', 'item_id', 'step', 'trigger']` an jedem Schritt; Fehler werfen (Job-Retry) — aber Guard verhindert Doppelpost.

**Kommentar:** ausschließlich über den `comment`-Parameter von `post()`. Kein separater `Comment()->add()`-Call.

### 3.4 Scheduled Command — `app/Console/Commands/PostPollResult.php` (`app:post-poll-result`)

```php
$poll = Poll::query()->eligibleForResultPost()->orderBy('closes_at')->first();
if ($poll === null) { $this->info('Kein Kandidat.'); Log::info(...); return self::SUCCESS; }
PostPollResultToPr0gramm::dispatch($poll, ResultPostConfig::fromArray($poll->result_post_config, $poll)->toArray());
```

Registrierung in `bootstrap/app.php`:
```php
$schedule->command('app:post-poll-result')->daily()->withoutOverlapping();
```

→ Genau **1 Post/Tag** (ältester `closes_at` zuerst, FIFO). `withoutOverlapping()` gegen parallele Runs.

### 3.5 Manueller Admin-Button — `App\Filament\Pages\Pr0PostCreator`

Filament-`Action` „Jetzt auf pr0gramm posten":

- **Sichtbar** nur wenn `Auth::user()->isAdmin()` **und** `$this->record->isEligibleForResultPost()`.
- **`requiresConfirmation()`** — öffentlicher, irreversibler Post.
- Aktion: aktuelle Form-Config speichern (`fromFlatForm` → `result_post_config`), dann `PostPollResultToPr0gramm::dispatch($record, $config->toArray(), Auth::id())`.
- **Ignoriert das Tageslimit** (Admin-Override — bewusst getrennt vom Cron).
- Danach `Filament\Notifications\Notification::make()->title('Auswertung wird auf pr0gramm gepostet')->success()->send()`.

### 3.6 Logging

`Log::info/warning/error` mit strukturiertem Kontext (`poll_id`, `item_id`, `step`, `trigger=cron|admin:<id>`). Keine Audit-Tabelle, keine Zusatz-Spalte. (Awareness: Repo hat **keine** Failed-Job-Alerts — `Horizon::routeMailNotificationsTo` auskommentiert.)

---

## 4. Daten- & Kontrollfluss

```
Cron (täglich)
  └─ app:post-poll-result
       └─ Poll::eligibleForResultPost()->orderBy(closes_at)->first()   (genau 1)
            └─ dispatch PostPollResultToPr0gramm (trigger=cron)

Admin (Pr0PostCreator-Button, jederzeit, Override)
  └─ Config speichern → dispatch PostPollResultToPr0gramm (trigger=admin:<id>)

PostPollResultToPr0gramm (Queue, ShouldBeUnique je Poll)
  guard(eligible?) → login → screenshot → upload → post(tags,siteUrl,comment)
  → original_content_link = pr0gramm-URL  → cleanup → log
```

---

## 5. Fehlerbehandlung & Idempotenz

- **Eligibility-Guard** am Job-Start (nach `refresh()`): nicht-mehr-eligible → no-op + Log.
- **`ShouldBeUnique`** (uniqueId = poll id): keine zwei gleichzeitigen Jobs für dieselbe Umfrage.
- **Write-back direkt nach `post()`**: gefährliches Fenster (Post erstellt, DB-Write schlägt fehl) minimal; Retry-Guard greift, sobald Link gesetzt.
- **Restrisiko** (Post ok, Update wirft → Retry vor Write): sehr klein, in Kauf genommen — dokumentiert.
- **Login-Fehler / API-Fehler / Browsershot-Fehler**: Exception → Job-Retry (`tries=15`, `backoff=120`).
- **Leerer Pool** im Command: sauberer No-op + Log, kein Fehler.

---

## 6. Tests (Pest, neu)

`tests/Feature/Jobs/PostPollResultToPr0grammTest.php` und `tests/Feature/...` mit `Http::fake()` (pr0gramm-Endpunkte), `Storage::fake()`, Browsershot/`PollResultScreenshotService` gemockt:

- Eligibility-Scope: nur `approved` + Link `null` + `closes_at` > 2 Wochen.
- Job: postet, parst `itemId`, schreibt `original_content_link` zurück, Kommentar im Post-Call enthalten.
- Idempotenz: Job mit bereits gesetztem Link → no-op.
- Command: wählt ältesten `closes_at`, dispatcht **genau 1**; leerer Pool → no-op.
- Button: nur Admin + eligible sichtbar; dispatcht Job mit `trigger=admin`; ignoriert Tageslimit.

(Repo-Konvention: `tests/Pest.php` bindet Traits per Verzeichnis — kein manuelles `uses(...)`.)

---

## 7. Konventionen (einzuhalten)

- `declare(strict_types=1);` Kopf jeder PHP-Datei; Pint-Preset `laravel`.
- Array-Vars `$a`-Präfix (`$aConfig`), `camelCase` Methoden, explizite Return-Types, Early-Return-Guards.
- Job: vier Standard-Traits, `tries=15`/`backoff=120`.
- Filament 5: deutsche Labels, `canAccess`/Visible-Gates, `requiresConfirmation`, Notification deutsch.
- Keine FormRequest, kein `env()` außerhalb `config/`, `.env` nicht anfassen.
- Poll-State-Felder nicht direkt mutieren — Ausnahme: `original_content_link` ist **kein** Status-Feld, wird hier bewusst direkt geschrieben (löst keinen State-Übergang / Notification aus).

---

## 8. Offene Detail-Punkte (Default gesetzt, nicht blockierend)

- **`siteUrl`**: Default = Link zur Results-Render-Page. Diese ist potenziell login/signed-gated → nicht-eingeloggte pr0gramm-User landen ggf. an einer Wand. Alternative: weglassen oder auf pr0p0ll-Home zeigen. *Default: Link rein, Caveat hier notiert; bei Implementierung Public-Erreichbarkeit prüfen.*
- **Response-Parsing**: genaue Keys von `upload()` (`key`) und `post()` (`itemId`) gegen echte API-Response verifizieren (tinker/Http::fake-Fixture).
- **Temp-Datei-Pfad**: lokaler Pfad für `upload()` (z.B. `storage_path('app/tmp/...')`), nach Post löschen.

---

## 9. Nicht im Scope (YAGNI)

- Audit-Tabelle / Posting-Historie-UI (bewusst verworfen).
- Mehr als 1 Auto-Post pro Tag / Batch.
- Separate Queue für externe API-Stalls (bekanntes Repo-Gap, hier nicht adressiert).
- Änderung an `resultsArePublic()` / Poll-State-Workflow / `HandleInertiaRequests::share()`.
- Failed-Job-Alerting.
