---
source: conventions-scout-error-handling
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Error-Handling

## Exception-Klassen-Hierarchie

Repo nutzt **nur Laravel-Standard-Handler**. `app/Exceptions/Handler.php`:

```php
public function register(): void
{
    $this->reportable(function (Throwable $e) {
        //
    });
}
```

`reportable()`-Callback leer — alle Exceptions laufen durch Default-Handling (Monolog → `storage/logs/laravel.log`). **Keine Custom-Exception-Klassen** in `app/Exceptions/`.

Exceptions kommen aus Dependencies: `Illuminate\Validation\ValidationException`, `InvalidArgumentException`, Filament-eigene.

## Wo Errors geworfen werden

**Services** werfen bei unbekanntem Enum-Wert:

```php
// app/Services/PollFormService.php — getComponent()
return match ($component) {
    QuestionType::COLOR->value => ColorPicker::make(...),
    // ... weitere Cases
    default => throw new \InvalidArgumentException('Unknown question type'),
};
```

**Filament-Pages** validieren inline und werfen `ValidationException`:

```php
// app/Filament/Resources/MyPollResource/Pages/CreateMyPoll.php — handleRecordCreation()
$validatedQuestions = Validator::make($questions->toArray(), [
    '*.title' => 'required|string',
    '*.options.*.title' => 'required|string',
])->validated();
```

## Wo Errors gefangen werden

**Filament-Page-Boundary** ist primärer Catch-Punkt:

```php
try {
    $validatedQuestions = Validator::make($questions->toArray(), [...])->validated();
    $poll = static::getModel()::create($data);
    $poll->questions()->createMany($validatedQuestions);
} catch (\Illuminate\Validation\ValidationException $e) {
    Notification::make()
        ->title('Komisch. Beim Validieren deiner Fragen ist ein Fehler aufgetreten.')
        ->danger()
        ->send();
}
```

`Handler::register()`-Callback nicht genutzt — keine Custom-Reporting-Logik.

## User-facing vs intern

**User-facing**: `Filament\Notifications\Notification` Toast mit `.danger()`/`.success()`/`.warning()`.

```php
Notification::make()
    ->title('Komisch. Beim Validieren deiner Fragen ist ein Fehler aufgetreten.')
    ->danger()
    ->send();
```

User-Strings **hardcoded deutsch** in Filament-Pages, KEINE i18n via lang/de/-Files für Filament-Inhalte (laravel-lang/common ist als Dep da, aber für Validation-Default-Messages).

**Intern**: `storage/logs/laravel.log` via Monolog (daily-Channel, 14 Tage). Siehe [`logging-and-observability.md`](logging-and-observability.md).

## Result-Types vs Exceptions

Exception-dominant — Laravel-Standard. Keine Result/Either-Pattern. Try/Catch lokal an Boundary.

## Validation-Errors

**FormRequest-Klassen nicht verwendet**. Stattdessen `Validator::make()` inline in Filament-Pages. Validierungsfehler werden als `.danger()`-Notification gezeigt, nicht in Inertia-`$errors`-Bag (da Filament kein Inertia).

Auf Inertia-Frontend-Seite (öffentliche Marketing-Pages) gibt es keine Form-Submissions — Validation-Pattern dort nicht etabliert.

## Error-Logging

`config/logging.php` Channels:

- `stack` (default) → leitet an `daily`
- `daily` → `storage_path('logs/laravel.log')`, Level `env('LOG_LEVEL', 'debug')`, Retention 14 Tage
- `slack` — konfiguriert, nur bei `critical`-Level + Webhook-URL
- `papertrail` — konfiguriert, optional

Kein Sentry / Bugsnag / Rollbar erkannt. **spatie/laravel-ignition** als Dev-Only-Exception-Page.

## Retries und Idempotenz

Jobs nutzen uniformes Retry-Pattern:

```php
// app/Jobs/SendNewPollAvailablePr0grammNotification.php
public int $tries = 15;
public int $backoff = 120;  // 2 Minuten zwischen Retries
```

15 Versuche × 120s = bis zu 30 Minuten. **Failed-Job-Handler nicht definiert** — Jobs landen nach Ablauf in `failed_jobs`-Tabelle (config/queue.php). **Horizon-Notification-Routing** für Failed-Jobs in `HorizonServiceProvider` ist auskommentiert — keine Failure-Alerts.

`SendPollAcceptedTelegramNotification` implementiert zusätzlich `ShouldBeUnique` — Idempotenz über Redis-Cache.

## "So fließen Errors hier" — Exemplar

1. Filament-Form-Submit → `CreateMyPoll::handleRecordCreation()` mit `Validator::make()`.
2. Validation fails → `ValidationException` geworfen.
3. Try/Catch konvertiert zu `.danger()`-Notification.
4. User sieht Toast "Komisch. Beim Validieren…"
5. Backend loggt Exception zu `laravel.log` (sofern nicht durch `reportable()`-Filter suppressed — Filter hier leer).

**Files**:
- `app/Filament/Resources/MyPollResource/Pages/CreateMyPoll.php`
- `app/Services/PollFormService.php`
- `app/Exceptions/Handler.php`
- `config/logging.php`

## Lücken

- Kein Sentry/Bugsnag — Produktions-Errors ohne Aggregation. Gap.
- Failed-Job-Routing in HorizonServiceProvider auskommentiert — keine Alerts bei Job-Failures.
- Hardcodierte deutsche User-Strings statt i18n.
- Pr0authController kein try/catch um `Socialite::driver('pr0gramm')->user()` — OAuth-Exceptions würden auf default-Handler landen.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/data-flow.md` — Errors fließen innerhalb des Daten-Flows
- `02-conventions/api-and-routing.md` — wo Errors auf die Leitung treffen
- `02-conventions/logging-and-observability.md` — wo Errors reportet werden

<!-- research:cross-refs-end -->
