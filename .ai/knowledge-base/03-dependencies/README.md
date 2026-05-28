---
source: dependencies-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Dependencies — Übersicht

Pr0p0ll mischt zwei Package-Manager: **Composer** (PHP) + **npm** (JS/Vue). Lockfiles aktiv: `composer.lock`, `package-lock.json`. Monorepo: nein.

## Stack-Summary

- **PHP**: 28 Direct-Deps in `require`, 13 in `require-dev`. Caret-Pinning durchgängig. `minimum-stability: beta` mit `prefer-stable: true`.
- **JS**: 5 Runtime-Deps, 14 Dev/Build-Deps. Caret-Pinning ebenfalls. `package-lock.json` ist v3 (npm 9+).

## Vulnerabilities (Audit-Snapshot)

| Package | Version | Severity | CVE / GHSA | Nature |
|---------|---------|----------|------------|---|
| axios | 1.15.0 | HIGH | GHSA-pmwg-cvhr-8vh7 | NO_PROXY-Bypass, CVSS 7.2 |
| axios | 1.15.0 | MODERATE | weitere 4 | inkl. SSRF-Varianten |
| phpseclib (transitive) | * | HIGH | CVE-2026-44167 | OID-Amplification-DoS |
| symfony/html-sanitizer (transitive) | * | MODERATE | CVE-2026-48761, CVE-2026-48760 | UrlAttributeSanitizer-Bypass |

**Empfohlene Maßnahme**: axios auf 1.16.1, transitive Vulns über Composer/npm-Update der Top-Deps.

## Outdated Majors

**JS**:
- `@inertiajs/vue3`: 1.3.0 → 3.3.0 (überspringt v2-Cycle).
- `@vitejs/plugin-vue`: 5.2.4 → 6.0.7.
- `eslint`: 8.57.1 → 10.4.0.

**Composer**:
- `barryvdh/laravel-debugbar`: v3.16.5 → v4.2.8.
- `brick/math` (transitive): 0.12.3 → 0.17.2.
- `paratest`: v7.4.9 → v7.20.0.

`inertiajs/inertia-laravel` ist auf `^0.6.11` gepinnt (Pre-1.0). Drift zum Client `@inertiajs/vue3 ^1.0.14`. Funktioniert weil 1.x-Client abwärtskompatibel mit 0.6-Server, aber langfristig sollten beide synchron upgraded werden.

## Suspicious / Unmaintained

Keine direkt geflaggt — alle Top-Deps zeigen Aktivität innerhalb 12 Monaten.

`socialiteproviders/pr0gramm ^5.0` wird vom Repo-Owner selbst gepflegt — vertretbar, aber Bus-Faktor 1.

## Tooling-Notes

Caret-Pinning erlaubt automatische Minor-Updates. `composer.lock` und `package-lock.json` sind committed. `composer.json` setzt `minimum-stability: beta` — relevant für Filament-3-Beta-Plugins. Kein `*` oder `latest` als Pin.

Peer-Konflikte: keine kritischen erkannt. Vue 3.4.15 + @vue/server-renderer 3.4.15 in Lockstep (Pflicht für SSR).

## Cross-Reference

- [`php-deps.md`](php-deps.md) — composer.json-Auflösung.
- [`js-deps.md`](js-deps.md) — package.json-Auflösung.
- [`usage/filament.md`](usage/filament.md) — Filament-Nutzung im Repo.
- [`usage/inertia-laravel.md`](usage/inertia-laravel.md) — Inertia-Server + Client.
- [`usage/horizon.md`](usage/horizon.md) — Queue-Nutzung.
- [`usage/pr0gramm-socialite.md`](usage/pr0gramm-socialite.md) — OAuth-Integration.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `00-overview.md` — die Stack-Summary

<!-- research:cross-refs-end -->
