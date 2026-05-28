---
source: synthesis
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Conventions — Index

Neun fokussierte Files plus dieser README. Jeder Sub-Scout liest 5-15 Files seines Bereichs und kondensiert Repo-spezifische Konventionen mit Path:line-Excerpts.

- [`code-style.md`](code-style.md) — Naming, Pint-Config, declare(strict_types=1), Array-Var-Präfix `$a`.
- [`testing.md`](testing.md) — Pest 2 + PHPUnit 10 + Dusk 8. Layout, Factories, Mocking, Coverage.
- [`error-handling.md`](error-handling.md) — Laravel-Standard-Handler, Filament-Notification für User, Validation inline.
- [`data-and-types.md`](data-and-types.md) — Eloquent-Casts, Migrations als Source-of-Truth, BigInt-Autoincrement-IDs, Carbon.
- [`configuration.md`](configuration.md) — env() nur in config/, hand-rolled Feature-Flags via Beta-Users-Env-Var.
- [`logging-and-observability.md`](logging-and-observability.md) — Monolog stack→daily, Pulse aktiv, Nightwatch default, Plausible Backend-API.
- [`async-and-concurrency.md`](async-and-concurrency.md) — Horizon Redis-Queue, 5 Worker prod, uniform tries=15/backoff=120.
- [`api-and-routing.md`](api-and-routing.md) — Inertia für Public + Filament für Admin. api.php leer. Sanctum-Session.
- [`state-management.md`](state-management.md) — Vue 3 + Inertia. Kein Pinia/Vuex. $page.props.auth.user als Shared-State.

Bei neuer Konvention zuerst hier nachschauen. Bei Konflikt mit Skill-Pattern: Repo-Konvention gewinnt (das ist der Sinn dieses Ordners).

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/exemplars.md` — Files die Conventions in Aktion zeigen

<!-- research:cross-refs-end -->
