---
source: dependencies-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# PHP-Dependencies

Manifest: `composer.json` (Version 0.0.6, License MIT, type project).

## Runtime — `require`

### Framework + Core

- `php ^8.2` mit Extensions: `ext-bcmath`, `ext-intl`, `ext-pcntl`, `ext-sodium`, `ext-zip`.
- `laravel/framework ^10.10` — Application-Framework.
- `laravel/sanctum ^3.3` — Session-Cookie-Guard für SPA + optional Token-Auth.
- `laravel/tinker ^2.8` — REPL.

### Admin-Panel + Forms

- `filament/filament ^3.2` (installiert 3.3.50) — Admin-Panel.
- `filament/notifications ^3.2` — DB-Notifications für Filament.
- `leandrocfe/filament-apex-charts ^3.1` — Charts in Admin-Dashboard.
- `yepsua/filament-rating-field ^0.6` — Rating-Component für Forms.
- `widiu7omo/filament-bandel ^2.0` — Ban-Aktionen für Filament-User-Resource.

### Frontend-Bridge

- `inertiajs/inertia-laravel ^0.6.11` — Inertia-Server-Adapter (Pre-1.0).
- `tightenco/ziggy ^1.8` — Generiert JS-Routes (im Blade via `@routes`-Direktive).

### Queues + Monitoring

- `laravel/horizon ^5.23` — Redis-Queue-Dashboard.
- `laravel/pulse ^1.0` — Application-Metrics.
- `laravel/nightwatch ^1.8` — Performance + Error-Tracking (Default-Config).

### Notifications

- `laravel-notification-channels/discord ^1.5` — Discord-Webhook-Channel.
- `laravel-notification-channels/telegram ^4.0` — Telegram-Bot-Channel.
- `tschucki/laravel-notification-channel-pr0gramm ^0.0.1` — eigener pr0gramm-Direct-Message-Channel (Repo-Owner pflegt).

### Authentication

- `socialiteproviders/pr0gramm ^5.0` — OAuth-Provider (vom Repo-Owner gepflegt). Bringt `laravel/socialite` transitiv.

### Domain-Features

- `cybercog/laravel-ban ^4.9` — Ban-System (User-Trait Bannable).
- `flowframe/laravel-trend ^0.2.0` — Time-Series-Aggregation für Charts.
- `njoguamos/laravel-plausible ^1.2` — Plausible-Analytics-API-Wrapper.

### HTTP

- `guzzlehttp/guzzle ^7.2` — HTTP-Client (Laravel-Standard).

## Dev — `require-dev`

### Testing

- `pestphp/pest ^2.34` + `pestphp/pest-plugin-laravel ^2.3` — Test-Runner.
- `phpunit/phpunit ^10.1` — Pest-Unterbau.
- `laravel/dusk ^8.0` — Browser-Tests via Chrome.
- `mockery/mockery ^1.4.4` — Mocking.
- `fakerphp/faker ^1.9.1` — Test-Fixtures.
- `nunomaduro/collision ^7.0` — bessere CLI-Error-Output.

### Quality + Debugging

- `laravel/pint ^1.0` — PHP-Formatter (Strict-Types-Enforcement).
- `barryvdh/laravel-debugbar ^3.9` — Dev-Debugbar.
- `spatie/laravel-ignition ^2.0` — Dev-Error-Page.
- `roave/security-advisories: dev-latest` — verhindert Install kompromittierter Pakete.

### Dev-Environment

- `laravel/sail ^1.18` — Docker-Dev-Environment (vermutlich nicht aktiv genutzt; Forge-Deployment).
- `laravel-lang/common ^6.1` — i18n-Übersetzungs-Helpers.

## Lockfile-Notes

`composer.lock` ist 634KB groß — typisch Laravel-Mid-Size-App. `minimum-stability: beta` + `prefer-stable: true` lässt beta-Releases von Filament-Plugins zu, behält Stable-Default für andere. `allow-plugins`: `pestphp/pest-plugin`, `php-http/discovery`.

Post-Autoload-Dump Scripts:
```json
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover --ansi",
    "@php artisan filament:upgrade"
]
```

`filament:upgrade` läuft automatisch — nach jedem `composer install/update` synchronisiert Filament seine Asset-Files.

PSR-4 Autoload:
```json
"App\\": "app/",
"Database\\Factories\\": "database/factories/",
"Database\\Seeders\\": "database/seeders/",
"Tests\\": "tests/"  // autoload-dev
```

## Cross-Reference

Per-Dep-Usage-Files (wie das Repo die Top-Deps nutzt):

- [`usage/filament.md`](usage/filament.md)
- [`usage/inertia-laravel.md`](usage/inertia-laravel.md)
- [`usage/horizon.md`](usage/horizon.md)
- [`usage/pr0gramm-socialite.md`](usage/pr0gramm-socialite.md)

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `03-dependencies/usage/filament.md` — Filament-Per-Dep-Analyse
- `03-dependencies/usage/inertia-laravel.md` — Inertia-Per-Dep-Analyse
- `03-dependencies/usage/horizon.md` — Horizon-Per-Dep-Analyse
- `03-dependencies/usage/pr0gramm-socialite.md` — Socialite-Per-Dep-Analyse

<!-- research:cross-refs-end -->
