---
source: architecture-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Entry-Points

Alle Stellen wo Ausführung beginnt.

## HTTP-Routes

### `routes/web.php`

- `GET /` → `FrontendController::landing` (Inertia: `Frontend/Landing`)
- `GET /impressum` → `FrontendController::imprint` (Inertia: `Frontend/Imprint`)
- `GET /datenschutz` → `FrontendController::privacy` (Inertia: `Frontend/Privacy`)
- `GET /nutzungsbedingungen` → `FrontendController::terms` (Inertia: `Frontend/Terms`)
- `GET /login` → `LoginRedirectController` (Filament-default-Login wird abgefangen, redirect zu OAuth-Start)
- `GET /oauth/start` → `Pr0authController::start` (middleware: `guest`)
- `GET /oauth/callback` → `Pr0authController::callback` (middleware: `guest`)

### `routes/api.php`

Leer. Keine API-Endpoints exponiert. Sanctum-Token-Auth nicht aktiv genutzt — Sanctum als Session-Cookie-Guard für SPA.

### `routes/channels.php`

Standard Laravel-Broadcasting-Routes — nicht customized.

### `routes/console.php`

Standard `inspire`-Command. Custom Commands liegen in `app/Console/Commands/`.

## Filament-Resources (Auto-Routed unter `/pr0p0ll/`)

- `AllPollsResource` — Admin-Vollansicht aller Polls. Routes: `/pr0p0ll/all-polls{,/create,/{record}/edit,/{record}}`.
- `CategoryResource` — Kategorien-Management.
- `MyPollResource` — User-Sicht auf eigene Polls.
- `PublicPollsResource` — Öffentliche Polls (browse + teilnehmen).
- `UserResource` — User-Management (admin-only).

## Filament-Pages (Auto-Routed unter `/pr0p0ll/`)

- `Login` — `app/Filament/Pages/Login.php` (überschreibt Filament-default-Login, redirect zu pr0gramm-OAuth).
- `FAQ`, `Leaderboard`, `PollResults`, `Pr0PostCreator`, `UserSettingsPage`.

## Console-Commands

`app/Console/Kernel.php` + `routes/console.php`:

- `app:login-to-pr0gramm` — geplant stündlich (Scheduler).
- `ban:delete-expired` — geplant jede Minute (Scheduler).
- Plus Standard-Artisan-Commands.

## Queue-Jobs (alle in `app/Jobs/`)

Alle implementieren `ShouldQueue`, `$tries = 15`, `$backoff = 120`. Dispatched aus `app/Models/Abstracts/Poll.php` (lifecycle-getriggert) und `app/Observers/MyPollObserver.php`.

- `SendPollAcceptedEmailNotification` — an Owner nach approve()
- `SendPollAcceptedPr0grammNotification` — an Owner nach approve()
- `SendPollAcceptedTelegramNotification` (auch `ShouldBeUnique`) — Channel-Broadcast
- `SendPollPublishedDiscordNotification` — Channel-Broadcast
- `SendPollDeclinedEmailNotification` — an Owner nach deny()
- `SendPollDeclinedPr0grammNotification` — an Owner nach deny()
- `SendNewPollAvailableEmailNotification` — Bulk an interessierte User
- `SendNewPollAvailablePr0grammNotification` — Bulk an interessierte User
- `SendNewPollAvailableDiscordNotification` — Channel-Broadcast
- `SendNewPollAvailableTelegramNotification` — Channel-Broadcast
- `SendCreatePostReminderNotification` — Erinnerung an Original-Content-Link nach Poll-End

## Observer-Hooks

- `MyPollObserver::updated()` — registriert in `EventServiceProvider`. Sendet Notification an Admin-Collection beim Übergang nach `in_review`.

## Scheduled-Jobs

Siehe Console-Commands. Cron-Trigger erfolgt via `* * * * * php artisan schedule:run` (Forge-Standard).

## Webhook-Endpoints

Keine. Plausible/Sentry/Bugsnag-Inbound-Webhooks nicht konfiguriert.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/lifecycle.md` — wann jeder Entry-Point feuert
- `02-conventions/api-and-routing.md` — HTTP-Entries im Detail

<!-- research:cross-refs-end -->
