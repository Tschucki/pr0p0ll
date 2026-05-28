---
source: dependency-usage-scout-horizon
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# laravel/horizon (^5.23) — Nutzung

## Wo es genutzt wird

- `config/horizon.php` — Supervisor-Config.
- `app/Providers/HorizonServiceProvider.php` — Authorization-Gate für `/horizon`-Dashboard.
- `app/Jobs/*.php` — 11 Notification-Job-Klassen, alle `ShouldQueue`.
- Job-Dispatch zentral aus `app/Models/Abstracts/Poll.php::approve()` / `::deny()`.

## Top-APIs

- `Horizon::auth()` — Dashboard-Gate (`HorizonServiceProvider::boot()`).
- `Horizon::routeMailNotificationsTo(...)`, `Horizon::routeSlackNotificationsTo(...)` — **auskommentiert**. Failed-Job-Alerts nicht aktiv.
- `Job::dispatch()`, `Job::dispatchAfterResponse()`, `Job::dispatchSync()` — nur `dispatch()` verwendet im Code.
- `Queue::fake()`, `Bus::fake()` — **nicht in Tests** vorhanden.

## Patterns

Jobs alle gleich strukturiert:

```php
// app/Jobs/SendNewPollAvailableEmailNotification.php
class SendNewPollAvailableEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;
    public int $backoff = 120;

    public function __construct(public Poll $poll, public User $user) {}

    public function handle(): void
    {
        if ($this->poll->userIsWithinTargetGroup($this->user) === false) {
            return;
        }
        Notification::route('mail', [$this->user->email => $this->user->name])
            ->notify(new NewPollAvailableEmailNotification($this->poll));
    }
}
```

11x identisches `$tries=15` / `$backoff=120`-Boilerplate. **Refactor-Chance**: Base-Job-Trait/-Klasse.

## Wrapper / Adapter

Kein Wrapper. Jobs erben direkt vom Laravel-Job-Trait-Stack.

## Konfiguration

```php
// config/horizon.php
'defaults' => [
    'supervisor-1' => [
        'connection' => 'redis',
        'queue' => ['default'],
        'maxProcesses' => 1,
        'memory' => 384,
        'tries' => 1,
        'timeout' => 60,
        'balance' => 'time',
        'balanceMaxShift' => 1,
        'balanceCooldown' => 3,
    ],
],
'environments' => [
    'production' => [
        'supervisor-1' => [
            'maxProcesses' => 5,
        ],
    ],
],
```

- Memory-Limit 384MB pro Worker.
- Auto-Scaling-Strategy `'time'` (Laravel-Default ist `'auto'` oder `'simple'`).
- 60s Job-Timeout.
- Production: 5 max-Worker.

`HorizonServiceProvider::gate()`:

```php
protected function gate(): void
{
    Gate::define('viewHorizon', function ($user) {
        return $user->isAdmin();
    });
}
```

Dashboard nur für Admins zugänglich, läuft hinter web-Middleware (kein Rate-Limit, kein IP-Allowlist).

## Beobachtete Pitfalls

- **Single-Queue-Risk**: alles auf `redis:default`. Telegram/Discord-API-Stalls (externe HTTP-Calls) können Mail-Pipeline blockieren. Trennung in `mail`/`external`/`internal`-Queues empfohlen.
- **Failed-Job-Alerts auskommentiert** — Jobs failen still in `failed_jobs`-Tabelle.
- **Boilerplate** — 11x `$tries=15` / `$backoff=120`. Base-Job-Trait würde reduzieren.
- **Keine Job-Tests** — `Queue::fake()` / `Bus::fake()` nirgends.

## Test-Strategie

Keine Job-Tests im Repo. Empfehlung:

```php
Queue::fake();
$poll->approve();
Queue::assertPushed(SendPollAcceptedEmailNotification::class);
```

oder für Bulk-Dispatch:

```php
Queue::assertPushedCount(SendNewPollAvailableEmailNotification::class, expectedCount: 5);
```

## Version-Pin-Notes

`^5.23` Caret. Läuft auf Laravel 10.10. `laravel/nightwatch ^1.8` instrumentiert Horizon-Jobs automatisch — Performance-Daten in Nightwatch-Dashboard.

## "So nutzt man Horizon in diesem Repo" — Playbook

1. **Neuer Job** unter `app/Jobs/{Name}Job.php`. `implements ShouldQueue`. Traits: `Dispatchable, InteractsWithQueue, Queueable, SerializesModels`.
2. **Retry-Konvention**: `public int $tries = 15; public int $backoff = 120;` (Repo-Standard). Bei externen APIs eventuell weniger Tries.
3. **Constructor** mit Models als typisierte Properties (`public Poll $poll, public User $user`). `SerializesModels` speichert nur IDs.
4. **handle()** mit Guard am Anfang (z.B. Target-Group-Check). Dann `Notification::route()->notify(...)` für Notification-Jobs.
5. **Dispatch** aus Model/Service via `JobClass::dispatch($model, $user)`. Aktuelles Pattern hängt Dispatches in `Poll::approve()`/`deny()`. Sauberer wäre Observer.
6. **Idempotenz** falls Job mehrfach getriggert werden kann: `implements ShouldBeUnique` (Beispiel: `SendPollAcceptedTelegramNotification`).
7. **Test** mit `Queue::fake()` (aktuell nirgends — Sollte etabliert werden).

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `03-dependencies/README.md` — Horizons Platz im Stack
- `01-architecture/exemplars.md` — Exemplar-Job-Klassen
- `02-conventions/async-and-concurrency.md` — Job-Konvention im Repo

<!-- research:cross-refs-end -->
