---
source: architecture-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Lifecycle

## Boot

`public/index.php` ist HTTP-Entry. Lädt `bootstrap/app.php`, das die Application-Instanz erzeugt und alle ServiceProvider registriert.

Wichtige Provider in `config/app.php` + `app/Providers/`:

- `AppServiceProvider` — minimal, registriert nur Plausible-Connector-Alias.
- `AuthServiceProvider` — definiert Gates + `Policy`-Mapping (`MyPoll → MyPollPolicy`).
- `EventServiceProvider` — registriert pr0gramm-Socialite-Provider via `SocialiteWasCalled`-Listener; bindet Model-Observer (`MyPoll::observe(MyPollObserver::class)`).
- `HorizonServiceProvider` — `Horizon::auth()` Gate für `/horizon`-Dashboard (admin-only).
- `RouteServiceProvider` — bindet `routes/web.php`, `routes/api.php`, lädt Rate-Limiter (Standard, keine custom).
- `Pr0p0llPanelProvider` — Filament-Panel-Registration: id `pr0p0ll`, path `/pr0p0ll`, default-Panel, OAuth-Login-Page, databaseNotifications, plugins (FilamentApexCharts), brandColor `#ee4d2e`.

## Request

HTTP-Request durchläuft folgende Middleware-Pipeline (siehe `app/Http/Kernel.php`):

- **Global**: `TrustProxies`, `HandleCors`, `PreventRequestsDuringMaintenance`, `ValidatePostSize`, `TrimStrings`, `ConvertEmptyStringsToNull`.
- **web-Group**: `EncryptCookies`, `AddQueuedCookiesToResponse`, `StartSession`, `ShareErrorsFromSession`, `VerifyCsrfToken`, `SubstituteBindings`, `HandleInertiaRequests` (custom-Share).
- **Filament-Panel** zusätzlich: `Cog\Laravel\Ban\Http\Middleware\ForbidBannedUser` — banished User gelangen nicht ins Panel.

Routes-Dispatch via Laravel-Router. Response von Inertia (Page+Props) oder Filament (HTML/JSON).

## Background

`Horizon` läuft als persistenter Daemon-Prozess (typisch via Forge + Supervisor). Liest Jobs aus Redis-Queue `default`, spawnt bis 5 Worker (production-Config in `config/horizon.php`), Job-Timeout 60s.

`Laravel-Scheduler` läuft via Cron-Eintrag `* * * * * cd /path && php artisan schedule:run`. `app/Console/Kernel.php` definiert zwei Tasks:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('app:login-to-pr0gramm')->hourly();
    $schedule->command('ban:delete-expired')->everyMinute();
}
```

- `app:login-to-pr0gramm` — hält pr0gramm-API-Session warm (vermutlich für Notification-Channel).
- `ban:delete-expired` — räumt abgelaufene Bans (cybercog/laravel-ban) jede Minute.

`Laravel-Pulse` läuft im Background (Recording-Pipeline aus `config/pulse.php`), schreibt Metriken in DB (nicht Redis), Trim auf 7 Tage Retention.

## Shutdown

Standard Laravel-Lifecycle — `terminate()`-Hooks auf Middleware. `Horizon` terminate-Signale werden vom Daemon erkannt, Worker beenden aktuelle Jobs sauber. Kein custom Shutdown-Code im Repo.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/entry-points.md` — alle Stellen wo Ausführung beginnt
- `02-conventions/async-and-concurrency.md` — Background-, Scheduled- und Queue-Arbeit
- `02-conventions/configuration.md` — Boot-Time-Config-Lesen

<!-- research:cross-refs-end -->
