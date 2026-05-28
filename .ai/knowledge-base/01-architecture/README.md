---
source: architecture-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Architektur — Übersicht

Pr0p0ll folgt Standard-Laravel-Layout (`app/`, `routes/`, `tests/`, `config/`, `database/`) ergänzt um anwendungs-spezifische Subdirs: `app/Filament/` (Admin-Panel), `app/Services/` (Business-Logik), `app/Jobs/` (Queue-Worker), `app/Connectors/` (externe API-Adapter), `app/Observers/` (Model-Event-Hooks), `app/Enums/`. Frontend ist hybrid: Filament 3 (Livewire-basiert) für Admin, Inertia/Vue 3 für öffentliche Marketing-Seiten.

Sieben Dateien in diesem Ordner — gemeinsam vermitteln sie "wie macht's das Repo":

- [`layout.md`](layout.md) — Verzeichnis-Baum mit Annotationen.
- [`layering.md`](layering.md) — Importrichtung, erlaubte Schichten.
- [`data-flow.md`](data-flow.md) — Poll-Approval von Filament-Form bis Notification-Job.
- [`lifecycle.md`](lifecycle.md) — Boot/Request/Background/Shutdown.
- [`entry-points.md`](entry-points.md) — Routen, Resources, Jobs, Console.
- [`exemplars.md`](exemplars.md) — kanonische Files zum Erst-Lesen.

Erst lesen: dieser README, dann `exemplars.md`. Beim Schreiben einer neuen Filament-Resource: `layering.md` + `entry-points.md`. Beim Schreiben eines neuen Jobs: `data-flow.md`.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `00-overview.md` — Ein-Absatz-Zusammenfassung
- `02-conventions/README.md` — Conventions realisieren die Architektur

<!-- research:cross-refs-end -->
