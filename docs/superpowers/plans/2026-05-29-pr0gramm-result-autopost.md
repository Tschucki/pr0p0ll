# pr0gramm Auswertungs-Autopost — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. Vor jedem Code-Edit das Skill `pr0p0ll-conventions` invoken.

**Goal:** Täglich genau eine länger als 2 Wochen geschlossene Umfrage ohne Post-Link automatisch als Bild-Post auf pr0gramm veröffentlichen, plus Admin-Button zum manuellen Anstoßen.

**Architecture:** Eligibility-Query-Scope auf `Poll` → Scheduled-Command `app:post-poll-result` (`->daily()`) wählt ältesten Kandidaten → dispatcht den `ShouldBeUnique`-Job `PostPollResultToPr0gramm` (login → Browsershot-Screenshot → pr0gramm upload+post mit Tags+Kommentar → Post-URL in `original_content_link` zurückschreiben). Der Admin-Button auf `Pr0PostCreator` dispatcht denselben Job sofort (Override). Logging über die `Log`-Facade; keine Audit-Tabelle, keine neue Spalte.

**Tech Stack:** Laravel 13, Filament 5, Livewire 4, PHP 8.3, Pest 4, Horizon 5 (Redis-Queue `default`), `spatie/browsershot ^5.4`, `tschucki/laravel-pr0gramm-api`.

**Spec:** `docs/superpowers/specs/2026-05-29-pr0gramm-result-autopost-design.md`

---

## Wichtige Repo-Fakten (vorab lesen)

- Concrete Poll-Modell für Queries: `App\Models\Polls\Poll` (extends `App\Models\Abstracts\Poll`, Table `polls`). `MyPoll`/`PublicPoll` teilen dieselbe Tabelle. Scope auf der Abstract-Klasse wirkt für alle.
- `original_content_link` (text, nullable) **ist** der „Post-Link". `null` = kein Link.
- `ResultPostConfig` (`app/Support/ResultPostConfig.php`) ist das Config-DTO. `PollResultScreenshotService::png(?ResultPostConfig): string` liefert PNG-Bytes (Browsershot).
- Results-Render-Route: `route('poll.results.render', ['poll' => id])`, Middleware `signed` (kein `auth`) → mit `URL::signedRoute(...)` öffentlich aufrufbar. Render-Controller macht `abort_unless($poll->hasEnded(), 404)`.
- **pr0gramm-Auth-Falle:** Die Facade `Tschucki\Pr0grammApi\Facades\Pr0grammApi` cached die Instanz (`getFacadeRoot`). Der Konstruktor liest den Cookie **einmalig** aus `config('services.pr0gramm.cookie')` (nicht gesetzt) bzw. `Session`. `login()` schreibt den Cookie nur in die Session, **nicht** in die statische Property der bereits gecachten Instanz. Lösung im Job: nach `login()` `Facade::clearResolvedInstance(\Tschucki\Pr0grammApi\Pr0grammApi::class)` → der nächste Facade-Zugriff re-instanziiert und liest den frischen Session-Cookie. **Achtung Test-Isolation:** Die statische Cookie-Property leakt zwischen Tests im selben Prozess — in `beforeEach` der pr0gramm-Tests `Facade::clearResolvedInstance(...)` aufrufen.
- API: `Pr0grammApi::Post()->upload(string $absPath): Response` (Response-JSON `{"key": "..."}`); `Pr0grammApi::Post()->post(key:, tags:, imageUrl:, siteUrl:, scheduleDate:, scheduleTime:, checkSimilar:, targetCollectionId:, comment:): Response` (Response-JSON enthält die neue Item-ID — Key defensiv parsen, siehe Job). Kommentar **inline** über den `comment`-Parameter, kein separater `Comment()->add()`.
- Test-Helper in `tests/Pest.php`: `makeClosedPoll(?User $owner = null, bool $resultsPublic = true): MyPoll`. Default `resultsPublic=true` → `closes_at = now()->subWeeks(3)`, `original_content_link = null`, `approved = true` ⇒ **eligible**. `resultsPublic:false` → `closes_at = now()->subDay()` ⇒ **nicht eligible**. Kein `PollFactory` — immer den Helper nutzen.
- Admin-User: `User::factory()->create(['admin' => true])` (`isAdmin()` = `(bool) $this->admin`).
- Es existiert **kein** `result_post_config['tags'|'comment']` bisher — wird in Task 2 ergänzt.

---

## File Structure

**Create:**
- `app/Jobs/PostPollResultToPr0gramm.php` — der Posting-Job (login, screenshot, upload, post, write-back, logging).
- `app/Console/Commands/PostPollResult.php` — `app:post-poll-result`, wählt ältesten Kandidaten, dispatcht 1 Job.
- `tests/Feature/Results/PollEligibleForResultPostTest.php` — Scope + Predikat.
- `tests/Feature/Jobs/PostPollResultToPr0grammTest.php` — Job-Verhalten (Http/Storage fake).
- `tests/Feature/Console/PostPollResultCommandTest.php` — Command-Auswahl & 1/Tag.
- `tests/Feature/Filament/Pr0PostCreatorPostActionTest.php` — Admin-Button.

**Modify:**
- `app/Models/Abstracts/Poll.php` — `scopeEligibleForResultPost()` + `isEligibleForResultPost()`.
- `app/Support/ResultPostConfig.php` — `tags`/`comment`-Felder + `defaultTags()`/`defaultComment()` + alle Mapping-Methoden.
- `app/Filament/Pages/Pr0PostCreator.php` — Form-Felder `tags`/`comment` + Header-Action „Jetzt auf pr0gramm posten" + Methode `postToPr0gramm()`.
- `bootstrap/app.php` — Schedule-Eintrag.

---

## Task 1: Eligibility-Scope & -Predikat auf Poll

**Files:**
- Modify: `app/Models/Abstracts/Poll.php`
- Test: `tests/Feature/Results/PollEligibleForResultPostTest.php`

- [ ] **Step 1: Failing test schreiben**

`tests/Feature/Results/PollEligibleForResultPostTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Polls\Poll;

it('matches an approved poll closed more than two weeks ago without a post link', function () {
    $poll = makeClosedPoll(); // closes_at = now()-3w, original_content_link = null, approved

    expect($poll->isEligibleForResultPost())->toBeTrue()
        ->and(Poll::query()->eligibleForResultPost()->pluck('id'))->toContain($poll->getKey());
});

it('excludes polls that already have a post link', function () {
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/123']);

    expect($poll->fresh()->isEligibleForResultPost())->toBeFalse()
        ->and(Poll::query()->eligibleForResultPost()->pluck('id'))->not->toContain($poll->getKey());
});

it('excludes polls closed less than two weeks ago', function () {
    $poll = makeClosedPoll(resultsPublic: false); // closes_at = now()-1d

    expect($poll->isEligibleForResultPost())->toBeFalse()
        ->and(Poll::query()->eligibleForResultPost()->pluck('id'))->not->toContain($poll->getKey());
});
```

- [ ] **Step 2: Test laufen lassen — muss fehlschlagen**

Run: `./vendor/bin/pest tests/Feature/Results/PollEligibleForResultPostTest.php`
Expected: FAIL — `Call to undefined method ...::eligibleForResultPost()` / `isEligibleForResultPost()`.

- [ ] **Step 3: Methoden implementieren**

In `app/Models/Abstracts/Poll.php` (Imports `Illuminate\Database\Eloquent\Builder` und `Illuminate\Support\Carbon` sind bereits vorhanden). Methoden in der Klasse ergänzen, z.B. direkt nach `resultsArePublic()`:

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
    return (bool) $this->approved
        && $this->original_content_link === null
        && $this->closes_at !== null
        && Carbon::make($this->closes_at)->lessThanOrEqualTo(now()->subWeeks(2));
}
```

- [ ] **Step 4: Test laufen lassen — muss bestehen**

Run: `./vendor/bin/pest tests/Feature/Results/PollEligibleForResultPostTest.php`
Expected: PASS (3 passed).

- [ ] **Step 5: Pint & Commit**

```bash
./vendor/bin/pint --dirty --format agent
git add app/Models/Abstracts/Poll.php tests/Feature/Results/PollEligibleForResultPostTest.php
git commit -m "feat(poll): add result-post eligibility scope and predicate"
```

---

## Task 2: ResultPostConfig um Tags & Kommentar erweitern

**Files:**
- Modify: `app/Support/ResultPostConfig.php`
- Test: `tests/Feature/Results/ResultPostConfigTest.php` (existiert — ergänzen)

`tags`/`comment` sind nullable. `null` = „Auto-Default verwenden" (wird erst beim Posten dynamisch aufgelöst, damit der signierte Link frisch ist).

- [ ] **Step 1: Failing test schreiben** (ans Ende von `tests/Feature/Results/ResultPostConfigTest.php` anhängen)

```php
it('defaults tags and comment to null so the job can resolve them dynamically', function () {
    $poll = makeClosedPoll();

    $config = App\Support\ResultPostConfig::default($poll);

    expect($config->tags)->toBeNull()
        ->and($config->comment)->toBeNull();
});

it('round-trips tags and comment through toArray and fromArray', function () {
    $poll = makeClosedPoll();

    $config = App\Support\ResultPostConfig::fromArray([
        'tags' => 'pr0p0ll,Sonderfall',
        'comment' => 'Mein Kommentar',
    ], $poll);

    expect($config->tags)->toBe('pr0p0ll,Sonderfall')
        ->and($config->comment)->toBe('Mein Kommentar')
        ->and($config->toArray())->toMatchArray([
            'tags' => 'pr0p0ll,Sonderfall',
            'comment' => 'Mein Kommentar',
        ]);
});

it('treats blank tags and comment from the flat form as null', function () {
    $poll = makeClosedPoll();

    $config = App\Support\ResultPostConfig::fromFlatForm(['tags' => '', 'comment' => '  '], $poll);

    expect($config->tags)->toBeNull()
        ->and($config->comment)->toBeNull();
});

it('builds auto tags and an auto comment containing a signed results link', function () {
    $poll = makeClosedPoll();

    expect(App\Support\ResultPostConfig::defaultTags($poll))
        ->toContain('pr0p0ll', 'Umfrage', 'Auswertung');

    expect(App\Support\ResultPostConfig::defaultComment($poll))
        ->toContain('/umfragen/'.$poll->getKey().'/auswertung')
        ->toContain('signature=');
});
```

- [ ] **Step 2: Test laufen lassen — muss fehlschlagen**

Run: `./vendor/bin/pest tests/Feature/Results/ResultPostConfigTest.php`
Expected: FAIL — unbekannte Property `tags`/Methode `defaultTags`.

- [ ] **Step 3: Implementieren**

In `app/Support/ResultPostConfig.php`:

a) Imports oben ergänzen:

```php
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
```

b) Konstruktor um zwei nullable Felder erweitern:

```php
    public function __construct(
        public string $title,
        public ?string $description,
        public string $color,
        public bool $showDemographics,
        public array $aQuestions,
        public ?string $tags = null,
        public ?string $comment = null,
    ) {}
```

c) `default()` — `tags`/`comment` als named args ergänzen (beide `null`):

```php
        return new self(
            title: (string) $poll->title,
            description: $poll->description,
            color: '#ee4d2e',
            showDemographics: true,
            aQuestions: $aQuestions,
            tags: null,
            comment: null,
        );
```

d) `fromArray()` — letzten `return new self(...)` um `tags`/`comment` ergänzen (leere Strings → null):

```php
        return new self(
            title: (string) ($aStored['title'] ?? $default->title),
            description: $aStored['description'] ?? $default->description,
            color: (string) ($aStored['color'] ?? $default->color),
            showDemographics: (bool) ($aStored['showDemographics'] ?? $default->showDemographics),
            aQuestions: $aQuestions,
            tags: self::blankToNull($aStored['tags'] ?? null),
            comment: self::blankToNull($aStored['comment'] ?? null),
        );
```

e) `fromFlatForm()` — letzten `return new self(...)` analog ergänzen:

```php
        return new self(
            title: (string) ($aForm['title'] ?? $poll->title),
            description: $aForm['description'] ?? null,
            color: (string) ($aForm['color'] ?? '#ee4d2e'),
            showDemographics: (bool) ($aForm['show_demographics'] ?? true),
            aQuestions: $aQuestions,
            tags: self::blankToNull($aForm['tags'] ?? null),
            comment: self::blankToNull($aForm['comment'] ?? null),
        );
```

f) `toFlatForm()` — zwei Keys ergänzen (leere Strings, damit Form-Felder leer/Placeholder zeigen):

```php
        $aForm = [
            'color' => $this->color,
            'title' => $this->title,
            'description' => $this->description,
            'show_demographics' => $this->showDemographics,
            'tags' => $this->tags ?? '',
            'comment' => $this->comment ?? '',
        ];
```

g) `toArray()` — zwei Keys ergänzen:

```php
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'color' => $this->color,
            'showDemographics' => $this->showDemographics,
            'questions' => $this->aQuestions,
            'tags' => $this->tags,
            'comment' => $this->comment,
        ];
    }
```

h) Neue Helper-Methoden (z.B. nach `toArray()`):

```php
    public static function defaultTags(Poll $poll): string
    {
        $titleTag = trim(str_replace(',', ' ', (string) $poll->title));

        return 'pr0p0ll,Umfrage,Auswertung'.($titleTag !== '' ? ','.$titleTag : '');
    }

    public static function defaultComment(Poll $poll): string
    {
        $link = URL::signedRoute('poll.results.render', ['poll' => $poll->getKey()]);

        return Str::of((string) $poll->title)->trim().' — alle Ergebnisse auf pr0p0ll: '.$link;
    }

    private static function blankToNull(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return ($value === null || $value === '') ? null : $value;
    }
```

- [ ] **Step 4: Test laufen lassen — muss bestehen**

Run: `./vendor/bin/pest tests/Feature/Results/ResultPostConfigTest.php`
Expected: PASS (alle, inkl. der bestehenden).

- [ ] **Step 5: Pint & Commit**

```bash
./vendor/bin/pint --dirty --format agent
git add app/Support/ResultPostConfig.php tests/Feature/Results/ResultPostConfigTest.php
git commit -m "feat(results): add editable tags and comment to result post config"
```

---

## Task 3: Job `PostPollResultToPr0gramm`

**Files:**
- Create: `app/Jobs/PostPollResultToPr0gramm.php`
- Test: `tests/Feature/Jobs/PostPollResultToPr0grammTest.php`

- [ ] **Step 1: Failing test schreiben**

`tests/Feature/Jobs/PostPollResultToPr0grammTest.php`:

```php
<?php

declare(strict_types=1);

use App\Jobs\PostPollResultToPr0gramm;
use App\Services\PollResultScreenshotService;
use App\Support\ResultPostConfig;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tschucki\Pr0grammApi\Pr0grammApi;

beforeEach(function () {
    // pr0gramm-Facade cached die Instanz inkl. statischem Cookie über Tests hinweg — zurücksetzen.
    Facade::clearResolvedInstance(Pr0grammApi::class);
    Storage::fake('local');

    // Screenshot-Service mocken, damit Browsershot/Chrome nicht echt läuft.
    $this->mock(PollResultScreenshotService::class)
        ->shouldReceive('png')
        ->andReturn('FAKEPNGBYTES');
});

function fakePr0grammHappyPath(): void
{
    // Bot gilt als eingeloggt → Job überspringt login(); Cookie via config bereitstellen,
    // damit der Facade-Konstruktor einen Cookie + Nonce hat.
    config(['services.pr0gramm.cookie' => 'me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D']);

    Http::fake([
        '*user/loggedin' => Http::response(['loggedIn' => true], 200),
        '*items/upload' => Http::response(['key' => 'UPLOADKEY'], 200),
        '*items/post' => Http::response(['itemId' => 4242], 200),
    ]);
}

it('uploads the screenshot, posts with tags and comment, and writes the post url back', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();
    $config = ResultPostConfig::fromArray([
        'tags' => 'pr0p0ll,Spezialtag',
        'comment' => 'Spezialkommentar',
    ], $poll);

    (new PostPollResultToPr0gramm($poll, $config->toArray()))->handle();

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/4242');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/post')
        && $request['tags'] === 'pr0p0ll,Spezialtag'
        && $request['comment'] === 'Spezialkommentar'
        && $request['key'] === 'UPLOADKEY');
});

it('falls back to auto tags and comment when none are configured', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();

    (new PostPollResultToPr0gramm($poll, ResultPostConfig::default($poll)->toArray()))->handle();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/post')
        && str_contains((string) $request['tags'], 'Auswertung')
        && str_contains((string) $request['comment'], '/auswertung'));
});

it('does nothing when the poll is no longer eligible', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/1']);

    (new PostPollResultToPr0gramm($poll, ResultPostConfig::default($poll)->toArray()))->handle();

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/1');
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'items/post'));
});

it('logs in when the bot session is not yet authenticated', function () {
    config(['services.pr0gramm.cookie' => null]);
    config(['services.pr0gramm.username' => 'bot', 'services.pr0gramm.password' => 'secret']);
    $poll = makeClosedPoll();

    Http::fake([
        '*user/loggedin' => Http::response(['loggedIn' => false], 200),
        '*user/login' => Http::response(['success' => true], 200, [
            'Set-Cookie' => 'me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D',
        ]),
        '*items/upload' => Http::response(['key' => 'UPLOADKEY'], 200),
        '*items/post' => Http::response(['itemId' => 99], 200),
    ]);

    (new PostPollResultToPr0gramm($poll, ResultPostConfig::default($poll)->toArray()))->handle();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'user/login'));
    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/99');
});
```

- [ ] **Step 2: Test laufen lassen — muss fehlschlagen**

Run: `./vendor/bin/pest tests/Feature/Jobs/PostPollResultToPr0grammTest.php`
Expected: FAIL — `Class "App\Jobs\PostPollResultToPr0gramm" not found`.

- [ ] **Step 3: Job implementieren**

`app/Jobs/PostPollResultToPr0gramm.php`:

```php
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

        Log::info('pr0gramm-autopost: lade Screenshot hoch.', ['poll_id' => $this->poll->getKey(), 'trigger' => $trigger]);
        $key = Pr0grammApi::Post()->upload($absPath)->json('key');

        if (! is_string($key) || $key === '') {
            throw new \RuntimeException('pr0gramm-autopost: kein Upload-Key in der Antwort.');
        }

        $response = Pr0grammApi::Post()->post(
            key: $key,
            tags: $tags,
            siteUrl: $siteUrl,
            comment: $comment,
        );

        $itemId = $response->json('itemId') ?? $response->json('item.id');

        if (! is_numeric($itemId)) {
            throw new \RuntimeException('pr0gramm-autopost: keine Item-ID in der Antwort: '.$response->body());
        }

        $postUrl = 'https://pr0gramm.com/new/'.$itemId;
        $this->poll->update(['original_content_link' => $postUrl]);

        Storage::disk('local')->delete($relPath);

        Log::info('pr0gramm-autopost: erfolgreich gepostet.', [
            'poll_id' => $this->poll->getKey(),
            'item_id' => $itemId,
            'post_url' => $postUrl,
            'trigger' => $trigger,
        ]);
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
```

- [ ] **Step 4: Test laufen lassen — muss bestehen**

Run: `./vendor/bin/pest tests/Feature/Jobs/PostPollResultToPr0grammTest.php`
Expected: PASS (4 passed).
> Falls der „logs in"-Test an der Nonce-Extraktion scheitert: Der Set-Cookie-Wert muss URL-encoded `"id":"<≥16 Zeichen>"` enthalten (siehe `me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D`).

- [ ] **Step 5: Pint & Commit**

```bash
./vendor/bin/pint --dirty --format agent
git add app/Jobs/PostPollResultToPr0gramm.php tests/Feature/Jobs/PostPollResultToPr0grammTest.php
git commit -m "feat(jobs): add PostPollResultToPr0gramm job"
```

---

## Task 4: Scheduled Command `app:post-poll-result`

**Files:**
- Create: `app/Console/Commands/PostPollResult.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/Console/PostPollResultCommandTest.php`

- [ ] **Step 1: Failing test schreiben**

`tests/Feature/Console/PostPollResultCommandTest.php`:

```php
<?php

declare(strict_types=1);

use App\Jobs\PostPollResultToPr0gramm;
use Illuminate\Support\Facades\Queue;

it('dispatches exactly one job for the longest-closed eligible poll', function () {
    Queue::fake();

    $older = makeClosedPoll();
    $older->update(['closes_at' => now()->subWeeks(6)]);
    $newer = makeClosedPoll();
    $newer->update(['closes_at' => now()->subWeeks(3)]);

    $this->artisan('app:post-poll-result')->assertSuccessful();

    Queue::assertPushed(PostPollResultToPr0gramm::class, 1);
    Queue::assertPushed(PostPollResultToPr0gramm::class, function (PostPollResultToPr0gramm $job) use ($older) {
        return (new ReflectionProperty($job, 'poll'))->getValue($job)->getKey() === $older->getKey();
    });
});

it('does nothing when there is no eligible poll', function () {
    Queue::fake();
    makeClosedPoll(resultsPublic: false); // closes_at = now()-1d → nicht eligible

    $this->artisan('app:post-poll-result')->assertSuccessful();

    Queue::assertNothingPushed();
});
```

- [ ] **Step 2: Test laufen lassen — muss fehlschlagen**

Run: `./vendor/bin/pest tests/Feature/Console/PostPollResultCommandTest.php`
Expected: FAIL — Command `app:post-poll-result` existiert nicht.

- [ ] **Step 3: Command implementieren**

`app/Console/Commands/PostPollResult.php`:

```php
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
```

- [ ] **Step 4: Test laufen lassen — muss bestehen**

Run: `./vendor/bin/pest tests/Feature/Console/PostPollResultCommandTest.php`
Expected: PASS (2 passed).

- [ ] **Step 5: Schedule registrieren**

In `bootstrap/app.php`, im `->withSchedule(function (Schedule $schedule): void { ... })`-Block die bestehenden Einträge ergänzen:

```php
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('app:login-to-pr0gramm')->hourly();
        $schedule->command('ban:delete-expired')->everyMinute();
        $schedule->command('app:post-poll-result')->daily()->withoutOverlapping();
    })
```

- [ ] **Step 6: Registrierung verifizieren**

Run: `php artisan schedule:list`
Expected: Zeile mit `app:post-poll-result` und Cadence `daily`.

- [ ] **Step 7: Pint & Commit**

```bash
./vendor/bin/pint --dirty --format agent
git add app/Console/Commands/PostPollResult.php bootstrap/app.php tests/Feature/Console/PostPollResultCommandTest.php
git commit -m "feat(console): add daily app:post-poll-result command and schedule"
```

---

## Task 5: Admin-Button auf `Pr0PostCreator`

**Files:**
- Modify: `app/Filament/Pages/Pr0PostCreator.php`
- Test: `tests/Feature/Filament/Pr0PostCreatorPostActionTest.php`

Der Button: Form-Felder `tags`/`comment` (editierbar, Placeholder = Auto-Default), Header-Action „Jetzt auf pr0gramm posten" (nur Admin + eligible, mit Confirmation), die `postToPr0gramm()` aufruft. Override ignoriert das Tageslimit.

- [ ] **Step 1: Failing test schreiben**

`tests/Feature/Filament/Pr0PostCreatorPostActionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Filament\Pages\Pr0PostCreator;
use App\Jobs\PostPollResultToPr0gramm;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('pr0p0ll'));
});

it('dispatches the post job when an admin triggers postToPr0gramm', function () {
    Queue::fake();
    $admin = User::factory()->create(['admin' => true]);
    $poll = makeClosedPoll($admin);

    Livewire::actingAs($admin)
        ->test(Pr0PostCreator::class, ['record' => $poll->getKey()])
        ->call('postToPr0gramm')
        ->assertHasNoErrors();

    Queue::assertPushed(PostPollResultToPr0gramm::class, 1);
});
```

- [ ] **Step 2: Test laufen lassen — muss fehlschlagen**

Run: `./vendor/bin/pest tests/Feature/Filament/Pr0PostCreatorPostActionTest.php`
Expected: FAIL — Methode `postToPr0gramm` existiert nicht.
> Falls stattdessen ein Panel-/Auth-Fehler auftritt: prüfen, dass die Panel-ID `'pr0p0ll'` korrekt ist (`app/Providers/Filament/Pr0p0llPanelProvider.php`, `->id(...)`), und ggf. anpassen.

- [ ] **Step 3: Implementieren** in `app/Filament/Pages/Pr0PostCreator.php`

a) Imports ergänzen (oben):

```php
use App\Jobs\PostPollResultToPr0gramm;
```

b) In `getPr0PostCreator()` im linken `Grid::make(1)->schema([...])` zwei Felder nach dem `Toggle::make('show_demographics')` und **vor** `...$this->getQuestionConfigFields()` einfügen:

```php
                        TextInput::make('tags')
                            ->label('Tags (kommagetrennt)')
                            ->placeholder(ResultPostConfig::defaultTags($this->record))
                            ->helperText('Leer lassen für automatische Tags.'),
                        Textarea::make('comment')
                            ->label('Kommentar')
                            ->placeholder(ResultPostConfig::defaultComment($this->record))
                            ->helperText('Leer lassen für einen automatischen Kommentar mit Link zur Auswertung.')
                            ->nullable(),
```

c) In `getHeaderActions()` als weitere Action (nach `download`) ergänzen:

```php
            Action::make('postToPr0gramm')
                ->label('Jetzt auf pr0gramm posten')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Auswertung auf pr0gramm posten?')
                ->modalDescription('Die Auswertung wird als öffentlicher Beitrag auf pr0gramm veröffentlicht. Das kann nicht rückgängig gemacht werden.')
                ->visible(fn (): bool => Auth::user()?->isAdmin() && $this->record->isEligibleForResultPost())
                ->action(fn () => $this->postToPr0gramm()),
```

d) Neue public Methode (z.B. nach `getHeaderActions()`):

```php
    public function postToPr0gramm(): void
    {
        $config = ResultPostConfig::fromFlatForm($this->data, $this->record);
        $this->record->update(['result_post_config' => $config->toArray()]);

        PostPollResultToPr0gramm::dispatch($this->record, $config->toArray(), Auth::id());

        Notification::make('post_queued')
            ->success()
            ->title('Wird auf pr0gramm gepostet')
            ->body('Die Auswertung wird im Hintergrund veröffentlicht. Der Post-Link wird danach automatisch bei der Umfrage hinterlegt.')
            ->send();
    }
```

- [ ] **Step 4: Test laufen lassen — muss bestehen**

Run: `./vendor/bin/pest tests/Feature/Filament/Pr0PostCreatorPostActionTest.php`
Expected: PASS (1 passed).

- [ ] **Step 5: Pint & Commit**

```bash
./vendor/bin/pint --dirty --format agent
git add app/Filament/Pages/Pr0PostCreator.php tests/Feature/Filament/Pr0PostCreatorPostActionTest.php
git commit -m "feat(filament): add manual pr0gramm post button to Pr0PostCreator"
```

---

## Task 6: Gesamtlauf & Abschluss

- [ ] **Step 1: Betroffene Tests gesammelt laufen lassen**

Run: `./vendor/bin/pest tests/Feature/Results tests/Feature/Jobs tests/Feature/Console tests/Feature/Filament --compact`
Expected: alle grün.

- [ ] **Step 2: Pint über alles**

Run: `./vendor/bin/pint --format agent`
Expected: keine offenen Style-Fehler.

- [ ] **Step 3: Volle Suite (nach Rückfrage beim User)**

Run: `./vendor/bin/pest --compact`
Expected: keine Regressions. (Dusk-Browser-Tests separat: `php artisan dusk` — nur wenn vom User gewünscht.)

- [ ] **Step 4: Abschluss-Commit, falls Pint noch etwas geändert hat**

```bash
git add -A
git commit -m "chore: pint formatting for pr0gramm autopost feature" || true
```

---

## Hinweise / bekannte Risiken (aus Spec übernommen)

- **`siteUrl`** zeigt auf die signierte Results-Render-Route (öffentlich, kein `auth`). Falls die Auswertung doch nicht öffentlich erreichbar sein soll, `siteUrl`-Argument im Job entfernen.
- **Response-Keys** `key` / `itemId` gegen echte pr0gramm-API verifizieren (`php artisan tinker`, Bot eingeloggt). Der Job parst defensiv (`itemId` ⟶ `item.id`).
- **Keine Failed-Job-Alerts** im Repo (`Horizon::routeMailNotificationsTo` auskommentiert) — Fehler nur in `laravel.log`.
- **Single Queue `default`** — der Browsershot+HTTP-Job kann die Mail-Pipeline kurz blocken; bei Problemen separate Queue erwägen (nicht Teil dieses Plans).
```
