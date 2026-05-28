---
source: conventions-scout-logging
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Logging und Observability

## Logger

Laravel-Standard-Monolog via `config/logging.php`. Default-Channel `stack` leitet an `daily`. Log-Datei `storage/logs/laravel.log` mit täglicher Rotation, 14 Tage Retention:

```php
// config/logging.php
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily'],
    'ignore_exceptions' => false,
],

'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,
],
```

Slack-Channel konfiguriert (Webhook + critical-Level), Papertrail-Handler verfügbar — beide werden bei fehlendem ENV nicht aktiv.

Log-Emissionen im App-Code z.B.:

```php
// in MyPollResource — Try-Catch
Log::info($throwable->getMessage());
```

Deprecations-Logs in `null`-Channel umgeleitet (werden geschluckt).

## Log-Levels

- **debug** — Default-Level, breitester Output.
- **info** — Allgemeine Events (z.B. caught Validation-Errors).
- **warning** — Selten genutzt im App-Code.
- **error / critical** — `critical` triggert Slack-Webhook (sofern konfiguriert).

## Log-Shape

Monolog-Default-Format (nicht JSON). `replace_placeholders: true` aktiv. Keine PSR-Log-Context-Standards explizit gesetzt. Redaction für sensible Felder läuft über Laravel-Standard-Maskierung (z.B. `password`, `*_token`, `*_secret`-Pattern in `app.cipher`-Logs).

## Metrics — Laravel Pulse

Vollständig aktiviert (`PULSE_ENABLED=true` Default). Dashboard unter `/pulse` (Gate via PulseServiceProvider-Authorize-Logik).

Aktive Recorder:

```php
// config/pulse.php
'recorders' => [
    Recorders\CacheInteractions::class => [...],
    Recorders\Exceptions::class => [
        'enabled' => true,
        'location' => env('PULSE_EXCEPTIONS_LOCATION', true),
    ],
    Recorders\Queues::class => [...],
    Recorders\SlowJobs::class => [
        'threshold' => env('PULSE_SLOW_JOBS_THRESHOLD', 1000),
    ],
    Recorders\SlowQueries::class => [
        'threshold' => env('PULSE_SLOW_QUERIES_THRESHOLD', 1000),
        'location' => env('PULSE_SLOW_QUERIES_LOCATION', true),
    ],
    Recorders\SlowOutgoingRequests::class => [...],
    Recorders\UserRequests::class => [
        'ignore' => ['#^/pulse$#', '#^/telescope#'],
    ],
],
```

Speicher: Database (nicht Redis). Trim auf 7 Tage Retention.

## Tracing — Nightwatch

`laravel/nightwatch ^1.8` installiert, **keine `config/nightwatch.php` publiziert** — läuft mit Defaults. Out-of-the-box Performance + Error-Tracking. Custom-Spans nicht im Code definiert. Horizon-Jobs werden automatisch instrumentiert.

## Error-Reporting

- **Dev**: `spatie/laravel-ignition` Error-Page mit Stack-Traces.
- **Prod**: Default-Logger (daily) — **kein Sentry/Bugsnag/Rollbar**. Production-Errors landen nur in `storage/logs/laravel.log`.

Gap: keine zentrale Error-Aggregation. Manuelles Tailen der Logs oder Slack-critical-Alerts (sofern Webhook gesetzt).

## Audit-Logging

**Nicht dediziert eingerichtet**. `cybercog/laravel-ban` trackt nur Ban-Reasons in eigener Tabelle, kein generisches Audit-Log. User-Actions im Filament-Panel nicht strukturiert getrackt. Kein `spatie/laravel-activitylog` oder ähnliches.

## Frontend-Observability

**Plausible** über `njoguamos/laravel-plausible` für Backend-Abfragen (API-Key, Site-ID via Env). Dashboard-Integration für `visitors`, `pageviews`, `bounce_rate`, `visit_duration`. Frontend-JS-Snippet vermutlich im `app.blade.php` (Plausible-Tracker-Script).

**Vue-Frontend**: Kein globaler Vue-Error-Handler. `resources/js/app.js` initialisiert Inertia + MotionPlugin, ohne `app.config.errorHandler`. Frontend-Errors landen nicht zentral.

```js
// resources/js/app.js
createApp({ render: () => h(App, props) })
    .use(plugin)
    .use(MotionPlugin)
    .mount(el);
```

## "So fließen Signale aus" — Exemplar

- `/pulse` — Pulse-Dashboard (Exceptions, SlowQueries, UserRequests, Cache, Queues, SlowOutgoingRequests).
- `/horizon` — Horizon-Dashboard (Job-Throughput, Failed-Jobs, Worker-Status).
- `storage/logs/laravel.log` — Daily-Rotation, 14 Tage.
- Slack-Webhook — `critical`-Level (bei konfigurierter URL).
- Plausible-API — Pageview-Analytics (extern).

## Lücken

- Kein Sentry/Bugsnag → Production-Errors nicht aggregiert.
- Kein Vue-Error-Handler → Frontend-Errors nicht reported.
- Nightwatch ohne Custom-Spans → APM-Daten generisch.
- Kein Audit-Log → User-Action-Trail fehlt.
- `Horizon::routeMailNotificationsTo` auskommentiert → keine Failed-Job-Alerts.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `02-conventions/error-handling.md` — Error-Reporting überlappt
- `02-conventions/async-and-concurrency.md` — Tracing über Async-Grenzen hinweg

<!-- research:cross-refs-end -->
