# Pr0p0ll — Knowledge-Base

Diese KB ist projekt-spezifisches Wissen über Pr0p0ll: Architektur, Conventions, Dependencies, Domain. Generiert von `/research` (Lean Scope, Caveman-Lite, Deutsch). Bei jeder Session, die in diesem Repo arbeitet, **diesen README zuerst lesen** — er führt zu allen anderen Files.

## Erst-Lesen

Wenn du dieses Repo zum ersten Mal anschaust:

1. [`00-overview.md`](00-overview.md) — Eine-Seiten-Zusammenfassung.
2. [`01-architecture/README.md`](01-architecture/README.md) — wie der Code strukturiert ist.
3. [`06-domain/README.md`](06-domain/README.md) — was die Plattform tut.

## Vollständige Struktur

### `01-architecture/` — Code-Struktur

- [`README.md`](01-architecture/README.md) — Index + Shape-Zusammenfassung.
- [`layout.md`](01-architecture/layout.md) — Verzeichnis-Tree mit Annotationen.
- [`layering.md`](01-architecture/layering.md) — Importrichtung, erlaubte Schichten.
- [`data-flow.md`](01-architecture/data-flow.md) — Kanonischer Poll-Approval-Flow end-to-end.
- [`lifecycle.md`](01-architecture/lifecycle.md) — Boot/Request/Background/Shutdown.
- [`entry-points.md`](01-architecture/entry-points.md) — Routen, Resources, Jobs, Console.
- [`exemplars.md`](01-architecture/exemplars.md) — kanonische Files zum Erst-Lesen.

### `02-conventions/` — Code-Standards

- [`README.md`](02-conventions/README.md) — Index.
- [`code-style.md`](02-conventions/code-style.md) — Naming, Pint, declare(strict_types=1), Array-Var-Präfix `$a`.
- [`testing.md`](02-conventions/testing.md) — Pest + PHPUnit + Dusk. Layout, Factories, Mocking, Coverage-Gaps.
- [`error-handling.md`](02-conventions/error-handling.md) — Laravel-Default-Handler, Filament-Notifications für User.
- [`data-and-types.md`](02-conventions/data-and-types.md) — Eloquent-Casts, Migrations als Truth, Auth-Leak-Awareness.
- [`configuration.md`](02-conventions/configuration.md) — env() nur in config/, hand-rolled Feature-Flags.
- [`logging-and-observability.md`](02-conventions/logging-and-observability.md) — Monolog + Pulse + Nightwatch + Plausible.
- [`async-and-concurrency.md`](02-conventions/async-and-concurrency.md) — Horizon, 11 Notification-Jobs uniform.
- [`api-and-routing.md`](02-conventions/api-and-routing.md) — Inertia + Filament + OAuth, api.php leer.
- [`state-management.md`](02-conventions/state-management.md) — Inertia $page.props als Shared-State, kein Pinia.

### `03-dependencies/` — Composer + npm

- [`README.md`](03-dependencies/README.md) — Übersicht + Vulnerability-Snapshot.
- [`php-deps.md`](03-dependencies/php-deps.md) — composer.json-Auflösung.
- [`js-deps.md`](03-dependencies/js-deps.md) — package.json-Auflösung.
- [`usage/filament.md`](03-dependencies/usage/filament.md) — Filament-Nutzung im Repo + Playbook.
- [`usage/inertia-laravel.md`](03-dependencies/usage/inertia-laravel.md) — Inertia-Nutzung + Auth-Leak-Hinweis.
- [`usage/horizon.md`](03-dependencies/usage/horizon.md) — Queue-Setup + Job-Playbook.
- [`usage/pr0gramm-socialite.md`](03-dependencies/usage/pr0gramm-socialite.md) — OAuth-Flow + Playbook.

### `06-domain/` — Was die Plattform tut

- [`README.md`](06-domain/README.md) — Pr0p0ll in plain Deutsch + Schlüssel-Capabilities.
- [`glossary.md`](06-domain/glossary.md) — alphabetische Domain-Begriffe.
- [`entities.md`](06-domain/entities.md) — Primary-Entities mit Felder/Relations/Lifecycle.
- [`business-rules.md`](06-domain/business-rules.md) — Authorization, Validation, State-Transitions, Berechnungen.

## Metadaten

- [`.research.json`](.research.json) — Decision-Record für Re-Runs (welche Scouts liefen, welche Profile-Choices, Validation-Results).
- [`.smoke-test.md`](.smoke-test.md) — KB-Smoke-Test-Report (Phase 7.5).

## Consuming this KB

Future sessions can ask `context-mode` to index this directory. From a session in this repo:

- `Skill(research)` reads this `README.md`.
- Or, ask `context-mode` directly: "index .ai/knowledge-base/, then query for 'how the auth flow works'."

`context-mode` requires `npm install -g context-mode` once per machine. See https://github.com/mksglu/context-mode for setup.

## Re-Run

Falls sich das Repo signifikant ändert, `/research` erneut ausführen. Skill liest dann `.research.json` und macht einen Diff statt clean-Schreibens. Korrekturen aus dieser Sitzung werden auf alle Scouts in zukünftigen Runs prepended.

---

Erstellt: 2026-05-28 · Profile: Lean · Caveman: Lite · Sprache: Deutsch · context-mode: registriert.
