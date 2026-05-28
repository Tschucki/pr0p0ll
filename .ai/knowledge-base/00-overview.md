---
source: synthesis
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Pr0p0ll — Knowledge-Base-Overview

Eine-Seiten-Zusammenfassung. Für Details: durch die Sections-Tree navigieren oder direkt einen der unten verlinkten Files öffnen.

## Was ist Pr0p0ll

Umfrage-Plattform für die pr0gramm.com-Community (deutschsprachig). User erstellen Polls mit 9 Question-Types, definieren Zielgruppen (öffentlich oder demografisch), Admin reviewt + approved, dann Bulk-Notification an interessierte User via Mail/Discord/Telegram/pr0gramm. Polls schließen automatisch nach `closes_after`-Frist.

Stack: **Laravel 10 + PHP 8.2** + **Filament 3** (Admin, Livewire 3 intern) + **Inertia/Vue 3** (Public-Marketing-Pages). Queues: **Horizon + Redis**. OAuth: **pr0gramm** via socialiteproviders/pr0gramm. Tests: **Pest + PHPUnit + Dusk**. Deployment: **Laravel Forge**. Lizenz: AGPL-3.0.

## Architektur (Ein-Satz-Form)

PHP-Code in `app/` mit Standard-Layout + Custom-Subdirs (Connectors, Services, Filament, Jobs). Frontend disjunkt: Filament-Admin unter `/pr0p0ll/*`, Inertia-Marketing unter `/`. Notification-Flow: Poll::approve() dispatcht 4 Owner-Jobs + Bulk-Loop pro Channel → Horizon-Worker-Pool (5 prod, 60s Timeout).

→ [`01-architecture/`](01-architecture/README.md)

## Conventions

- PSR-12 + Laravel-Pint mit `declare_strict_types: true` repo-weit.
- `camelCase` Methoden, `PascalCase` Klassen, Eigenheit: Array-Vars mit Präfix `$a`.
- Tests: Pest 2 mit `it()`-Makro. Filament/OAuth/Jobs **ungetestet** — Coverage-Gap.
- Validation: **keine FormRequest**, inline `Validator::make()` + Filament-Forms.
- Error-Handling: Laravel-Standard-Handler, Filament-Toast für User, hardcoded deutsche Strings.
- Logging: Monolog stack→daily, 14 Tage. Pulse aktiv. Nightwatch default-config. **Kein Sentry**.

→ [`02-conventions/`](02-conventions/README.md)

## Dependencies (Status-Snapshot)

**Sicherheit**:
- ⚠️ axios 1.15.0: 5 CVEs (HIGH, GHSA-pmwg-cvhr-8vh7). Update auf 1.16.1.
- ⚠️ phpseclib (transitive): CVE-2026-44167.
- ⚠️ symfony/html-sanitizer (transitive): CVE-2026-48761, CVE-2026-48760.

**Drift**:
- `inertiajs/inertia-laravel ^0.6.11` (Pre-1.0) + `@inertiajs/vue3 ^1.0.14` (1.x). Funktioniert via Abwärtskompatibilität.

→ [`03-dependencies/`](03-dependencies/README.md)

## Domain (Ein-Satz-Form)

Poll-Lifecycle: draft → in_review → (approved | denied) → published → closed. Approval triggert 4+N Notifications über 4 Channels. Anonyme Beteiligung über AnonymousUser-Pseudo-User. Demographic-Filter auf Ergebnis-Aggregation.

→ [`06-domain/`](06-domain/README.md)

## Health-Signale (Top 5 Gaps)

1. ⚠️ **Auth-Leak**: `HandleInertiaRequests::share()` schickt komplettes User-Model an Frontend.
2. ⚠️ **Keine Filament/OAuth/Job-Tests** — kritischer Code ungetestet.
3. ⚠️ **`.env` mit Dev-Client-Secret committed** — rotieren bei Production.
4. ⚠️ **Single Redis-Queue** — Telegram-Stalls blockieren Mail-Pipeline.
5. ⚠️ **11 Job-Klassen mit Boilerplate** — Base-Job-Trait würde reduzieren.

## KB-Struktur

```
knowledge-base/
├── 00-overview.md              ← Sie sind hier
├── README.md                   Hand-written Index
├── 01-architecture/            7 Files
├── 02-conventions/             10 Files (README + 9 sub-scouts)
├── 03-dependencies/            3 Files + usage/{filament,inertia-laravel,horizon,pr0gramm-socialite}.md
├── 06-domain/                  4 Files
├── .research.json              Decision-Record (Re-run-State)
└── .smoke-test.md              Phase-7.5 KB-Smoke-Test
```

29 KB-Files total. Caveman-Lite-Kompression auf alle Prosa angewendet.

## Nächste Schritte für Mit-Forscher

1. **Bug fixen**: Start in [`02-conventions/error-handling.md`](02-conventions/error-handling.md) + [`01-architecture/data-flow.md`](01-architecture/data-flow.md).
2. **Filament-Resource hinzufügen**: [`03-dependencies/usage/filament.md`](03-dependencies/usage/filament.md) Playbook.
3. **Neuen Job schreiben**: [`03-dependencies/usage/horizon.md`](03-dependencies/usage/horizon.md) Playbook + [`02-conventions/async-and-concurrency.md`](02-conventions/async-and-concurrency.md).
4. **Public-Page hinzufügen**: [`03-dependencies/usage/inertia-laravel.md`](03-dependencies/usage/inertia-laravel.md) Playbook.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/README.md` — Architektur in einem Absatz; die Übersicht hier fächert dorthin auf
- `06-domain/README.md` — was die Plattform tut, plain Deutsch
- `02-conventions/README.md` — wie das Team Code schreibt

<!-- research:cross-refs-end -->
