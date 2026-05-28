---
source: conventions-scout-configuration
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Konfiguration

## Env-Var-Loading

`env()` nur in `config/*.php` aufgerufen — App-Code liest via `config('xyz.abc')`. Best-Practice eingehalten:

```php
'env' => env('APP_ENV', 'production'),
'debug' => (bool) env('APP_DEBUG', false),
```

Keine `env()`-Calls außerhalb `config/` detektiert.

## Config-Files

22 Files in `config/`:

- **app.php** — Name, Version, Env, Debug-Flag, Service-Provider-Liste, Aliases.
- **services.php** — Third-Party-Credentials (Mailgun, Postmark, pr0gramm-OAuth, Telegram, Discord).
- **database.php** — Default MySQL, optional SQLite/PostgreSQL.
- **logging.php** — Monolog-Channels (stack → daily, plus slack/papertrail).
- **horizon.php** — Redis-Queue-Monitoring, Supervisor-Config, Memory-Limit 384MB.
- **mail.php, queue.php, cache.php, session.php, auth.php, broadcasting.php** — Standard Laravel.
- **pr0p0ll.php** — App-spezifisch: `'beta_users' => explode(',', env('BETA_USERS', ''))`.
- **plausible.php** — Analytics-Domain + API-Key.
- **pulse.php** — Recorder-Pipeline, Retention 7 Tage.
- **ban.php, blade-icons.php, cors.php, filesystems.php, hashing.php, sanctum.php, view.php, debugbar.php** — Package-Configs.

```php
// config/services.php
'pr0gramm' => [
    'client_id' => env('PR0GRAMM_CLIENT_ID'),
    'client_secret' => env('PR0GRAMM_CLIENT_SECRET'),
    'redirect' => env('PR0GRAMM_REDIRECT_URI'),
    'username' => env('PR0GRAMM_USERNAME'),
    'password' => env('PR0GRAMM_PASSWORD'),
],
```

`config/filament.php` ist **nicht publiziert** — Panel-Konfiguration läuft komplett über `Pr0p0llPanelProvider`.

## Secrets

Lokal: `.env` (gitignored). Template: `.env.example`. Produktion: Laravel-Forge setzt Vars per UI/API direkt auf Server-Env. Laravel maskiert automatisch Keys mit Pattern `*_KEY`, `*_SECRET`, `*_PASSWORD`, `*_TOKEN` in Logs.

Notification-Tokens benötigt:
- `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHANNEL_CHAT_ID`
- `DISCORD_BOT_TOKEN`, `DISCORD_CHANNEL_ID`
- `PR0GRAMM_CLIENT_ID`, `PR0GRAMM_CLIENT_SECRET` (OAuth)
- `PR0GRAMM_USERNAME`, `PR0GRAMM_PASSWORD` (für API-Login-Job)

⚠️ Achtung — Scout-Befund: `.env` enthält committed Dev-Client-Secret (Dev-OAuth-Config). Bei Production-Deployment unbedingt rotieren.

## Feature-Flags

Hand-rolled in `config/pr0p0ll.php`:

```php
'beta_users' => explode(',', env('BETA_USERS', '')),
```

Default `.env.example`: `BETA_USERS=PimmelmannJones,Rundesballi`. Einfache Beta-Liste, keine externe Flag-Library (LaunchDarkly/GrowthBook/Unleash). Verwendung: in Filament/Pages-Logik gegen `config('pr0p0ll.beta_users')` prüfen.

## Per-Environment-Config

`APP_ENV` Standard-Werte: `local` (Dev), `staging`, `production`. Unterscheidung via `app()->environment(...)` oder `config('app.env')`. Keine `.env.local`-Sondernamen — Forge setzt Prod-Vars direkt.

## Boot-Time-Validation

**Gap**: Keine fail-fast-Validation in ServiceProvider-`boot()`-Methoden. `AppServiceProvider::boot()` ist nahezu leer. Keine `config:validate`-Command, keine Pflicht-Env-Liste. Bei fehlendem `PR0GRAMM_CLIENT_ID` läuft App hoch, OAuth scheitert bei erster Anfrage.

Empfehlung (für Awareness): Check in `AppServiceProvider::boot()` für Prod-Env, der erforderliche Vars validiert.

## Required Env-Vars (aus `.env.example`)

| Var | Required? | Default | Beschreibung |
|-----|-----------|---------|---|
| `APP_NAME` | ✓ | — | Applikationsname |
| `APP_ENV` | ✓ | `production` | local/staging/production |
| `APP_KEY` | ✓ | — | Laravel-Encryption-Key (32 byte) |
| `APP_DEBUG` | ✓ | `false` | Debug-Flag |
| `APP_URL` | ✓ | — | Canonical-URL |
| `DB_CONNECTION` | ✓ | `mysql` | DB-Driver |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | ✓ | — | DB-Credentials |
| `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` | ✓ | — | Horizon-Queue + Cache |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` | ✓ | — | Mail-Versand |
| `PR0GRAMM_CLIENT_ID`, `PR0GRAMM_CLIENT_SECRET`, `PR0GRAMM_REDIRECT_URI` | ✓ | — | OAuth |
| `PR0GRAMM_USERNAME`, `PR0GRAMM_PASSWORD` | ✓ | — | API-Login-Job |
| `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHANNEL_CHAT_ID` | ◯ | — | Telegram-Notifications |
| `DISCORD_BOT_TOKEN`, `DISCORD_CHANNEL_ID` | ◯ | — | Discord-Notifications |
| `PLAUSIBLE_SITE_ID`, `PLAUSIBLE_API_KEY` | ◯ | — | Analytics |
| `BETA_USERS` | ◯ | `''` | Komma-Liste Beta-Tester |
| `LOG_CHANNEL` | ◯ | `stack` | Monolog-Channel |
| `LOG_LEVEL` | ◯ | `debug` | Log-Level |

## "So wird Config gelesen hier" — Exemplar

1. **Database** → `config/database.php` liest `env('DB_HOST')` → PDO-Connection via Laravel-Manager.
2. **Services** → `config/services.php` definiert `pr0gramm`-Block → `Socialite::driver('pr0gramm')` greift via Service-Container.
3. **Horizon** → `config/horizon.php` Supervisor-Definition für `redis`-Connection, Memory 384MB, 5 max-Worker prod.
4. **Logging** → `config/logging.php` Stack → Daily → `storage/logs/laravel.log`.
5. **Filament** → kein `config/filament.php` — `Pr0p0llPanelProvider::panel()` ist Konfigurations-Truth.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/lifecycle.md` — Boot-Time-Config-Validation

<!-- research:cross-refs-end -->
