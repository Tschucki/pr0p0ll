---
source: conventions-scout-async
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Async und Concurrency

## Async-Style

PHP-Sync mit Background-Job-Processing über **Horizon + Redis**. Async kommt ausschließlich über Queue-Dispatch. Jobs werden mit `JobClass::dispatch(...)` aus Models/Observers eingereiht:

```php
// app/Models/Abstracts/Poll.php — innerhalb approve()
SendPollAcceptedEmailNotification::dispatch($poll, $user);
SendPollAcceptedPr0grammNotification::dispatch($poll, $user);
SendPollPublishedDiscordNotification::dispatch($poll);
SendPollAcceptedTelegramNotification::dispatch($poll);

// Bulk-Loop
foreach ($usersForMail as $user) {
    SendNewPollAvailableEmailNotification::dispatch($poll, $user);
}
```

Standard Queue-Connection: `redis`, Default-Queue: `default` (siehe `config/queue.php`).

## Concurrency-Primitives

Keine expliziten Locks (`Cache::lock()`), keine Database-Transactions in Observern/Jobs für Concurrency-Schutz. Isolation läuft über Horizon-Worker-Modell. Job-`ShouldBeUnique` als einziges Idempotenz-Primitiv:

```php
// app/Jobs/SendPollAcceptedTelegramNotification.php
class SendPollAcceptedTelegramNotification implements ShouldBeUnique, ShouldQueue
{
    public int $tries = 15;
    public int $backoff = 120;
    // uniqueId() → Default basiert auf Job-Class + Args
}
```

`SerializesModels`-Trait sorgt dafür, dass nur Model-ID übergeben wird (kein Frischhalten von Property-Werten zwischen Dispatch und Handle).

## Background-Jobs

Horizon-Setup uniform und nahe Laravel-Default:

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'maxProcesses' => 5,
            'memory' => 384,
            'tries' => 1,
            'timeout' => 60,
            'balance' => 'time',
        ],
    ],
],
```

5 parallele Worker prod, 60 Sekunden Job-Timeout, Worker-Memory-Limit 384MB.

Jobs alle in `app/Jobs/` (11 Klassen, alle Notification-bezogen). Uniformes Boilerplate-Pattern:

```php
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

Refactor-Chance: 11x identisches `$tries=15` / `$backoff=120` — Base-Job-Trait würde sich lohnen.

## Scheduled-Jobs

Scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('app:login-to-pr0gramm')->hourly();
    $schedule->command('ban:delete-expired')->everyMinute();
}
```

`app:login-to-pr0gramm` hält pr0gramm-API-Session warm (für Notification-Channel). `ban:delete-expired` räumt abgelaufene Bans aus cybercog/laravel-ban-Tabelle.

Cron-Hook: `* * * * * cd /path && php artisan schedule:run` (Forge-Standard).

## Parallelism

`Bus::batch()` wird **nicht aktiv genutzt** im App-Code. Statt Batch-Atomicity: einfache foreach-Loops für Bulk-Dispatch:

```php
foreach ($usersForMail as $user) {
    SendNewPollAvailableEmailNotification::dispatch($poll, $user);
}
foreach ($usersForPr0 as $user) {
    SendNewPollAvailablePr0grammNotification::dispatch($poll, $user);
}
```

Jeder Dispatch ist eigenständiger Job — Horizon-Worker-Pool verarbeitet parallel. Keine Failure-Aggregation über Batch-Callbacks.

## Time-Handling

Carbon (Nesbot, Laravel-Bundled) durchgehend. `now()`, `Carbon::make()`, `->add($interval)`, `->isPast()`:

```php
// app/Models/Abstracts/Poll.php
public function isClosed(): bool
{
    if ($this->published_at !== null && $this->closes_after !== null) {
        return Carbon::make($this->published_at)?->add($this->closes_after)->isPast();
    }
    return false;
}

public function approve(): void
{
    $this->update([
        'published_at' => now(),
        'closes_at' => now()->add($this->closes_after),
    ]);
}
```

`closes_after` ist ClosesAfter-Enum mit String-Value (`"+1 week"` etc.) — Carbon kann den direkt addieren.

**Test-Time-Injection** (`Carbon::setTestNow()`) nicht im App-Code, nicht in Tests aktiv genutzt — Tests laufen auf realer Zeit.

## Cancellation

Job-Timeout: 60 Sekunden (Horizon-Config). Kein `maxFailures`-Setting. Retry-After hardcoded `$backoff = 120`. Nach 15 Versuchen landen Jobs in `failed_jobs`-Tabelle. **Failed-Job-Alerts auskommentiert** in HorizonServiceProvider — kein Email/Slack-Ping.

## Beobachtete Pitfalls

- **MyPollObserver dispatcht keine Jobs** sondern sendet Notifications synchron (`Notification::send($admins, ...)`) — bei vielen Admins könnte das Request blockieren. Sollte zu Job konvertiert werden.
- **Keine Cache::lock()** bei Poll-State-Übergängen — concurrent `approve()` könnte 2x dispatchen (unwahrscheinlich da nur Admin via UI, aber theoretisch möglich).
- **Bulk-Dispatch ohne Bus::batch()** — keine atomare Failure-Behandlung.
- **Test-Coverage für Queue null** — `Queue::fake()` / `Bus::fake()` nicht genutzt.
- **Single-Queue-Risk**: Discord/Telegram-Stalls (externe APIs) können Mail-Pipeline auf gleicher `default`-Queue blockieren. Trennung in `mail`/`external-channels`-Queues wäre besser.
- **Boilerplate** — 11x identisches `$tries=15`/`$backoff=120` ohne Base-Job-Trait.

## "So funktioniert Concurrency hier" — Exemplar

1. **Poll-Approval-Spray**: `Poll::approve()` dispatcht 4 Owner-Notifications + Bulk-Loop pro Channel über alle interessierten User → Horizon-Worker-Pool verarbeitet parallel.
2. **MyPollObserver Sync-Push**: `updated()` mit Dirty-Check auf `in_review` → `Notification::send($admins, ...)` synchron (kein Job).
3. **Horizon-Worker-Pool**: 5 prod-Worker, 60s Timeout/Job, 15 Retries × 120s.

Relevante Files:
- `app/Models/Abstracts/Poll.php` — Dispatch-Quelle.
- `app/Jobs/*.php` — 11 Notification-Jobs, uniform.
- `app/Observers/MyPollObserver.php` — Sync-Notification.
- `config/horizon.php` — Worker-Config.
- `app/Console/Kernel.php` — Scheduler.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/lifecycle.md` — Background/Queue-Lifecycle-Context
- `01-architecture/data-flow.md` — Async-Daten-Flows
- `02-conventions/error-handling.md` — Async-Errors sind eigene Klasse
- `02-conventions/logging-and-observability.md` — Tracing über Async-Grenzen

<!-- research:cross-refs-end -->
