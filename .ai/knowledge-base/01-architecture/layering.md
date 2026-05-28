---
source: architecture-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Layering

Importrichtung im PHP-Teil (innen → außen):

```
Models ← Services ← Filament/Pages, Filament/Resources, Jobs, Observers
Models ← Policies (für Authorization)
Models ← Notifications
Services ← Filament-Resources/Pages (Form-Helpers, Result-Aggregation)
Jobs ← Models (über Eloquent-Properties + Carbon)
```

## Erlaubte Imports — Beobachtung

- **`app/Models/*`** importiert: Carbon, Eloquent-Traits (`HasFactory`, `Notifiable`, `Bannable`), andere Models (Relations), Enums.
  - Beispiel: `app/Models/Abstracts/Poll.php` importiert direkt Jobs (`SendPollAcceptedEmailNotification::dispatch(...)`) — **Models dispatchen Jobs**. Wird in Phase-3.5-Healing als gangbar akzeptiert, ist aber Layer-Bruch (Model kennt Job-Klasse).
- **`app/Services/*`** importiert: Models, Builder, Carbon, Enums.
- **`app/Filament/Resources/*`** importiert: Models, Filament-Forms/Tables/Actions/Infolists, Notifications, Auth-Facade.
- **`app/Jobs/*`** importiert: Models, Notification-Klassen, Queue-Traits (`ShouldQueue`, `Dispatchable`, `InteractsWithQueue`, `Queueable`, `SerializesModels`).
- **`app/Observers/*`** importiert: Models, Notification-Facade.

## Beobachtete Layer-Bruchstellen

1. **Model dispatcht Job** — `app/Models/Abstracts/Poll.php` ruft `SendPoll*Notification::dispatch(...)` aus `approve()`/`deny()`. Sauberer: Observer fängt `updated`-Event, dispatcht Jobs. `MyPollObserver` macht das partial nur für `in_review`-Übergänge.
2. **Job kennt komplette Poll-Logik** — Each Job lädt Poll, prüft `userIsWithinTargetGroup`, sendet Notification. Job + Model + Service teilen sich Validation-Konstrukt.
3. **Filament-Page schreibt Validator-Logik** — `app/Filament/Resources/MyPollResource/Pages/CreateMyPoll.php` macht `Validator::make()` inline statt FormRequest. Filament-typisch, aber Schicht-Mix.
4. **`HandleInertiaRequests::share()`** leakt `Auth::user()?->toArray()` — Middleware sieht komplettes Model, schickt es an alle Inertia-Pages (Schicht-Bruch: Middleware sollte DTO bauen, nicht Model durchreichen). Siehe [`../02-conventions/data-and-types.md`](../02-conventions/data-and-types.md).

## Frontend-Layering

- **Filament-Admin** läuft komplett serverseitig über Livewire 3 (Filament-intern). State im Server.
- **Inertia-Public** liefert Vue-Components mit Props. `resources/js/Pages/Frontend/*.vue` consumert `$page.props`, kein Pinia/Vuex.
- Keine Bibliotheks-Vermischung — Filament und Inertia bedienen disjunkte URL-Spaces (`/pr0p0ll/*` vs `/`, `/impressum`, `/datenschutz`, `/nutzungsbedingungen`).

## Zyklen

Keine zyklischen Imports erkannt zwischen Modulen. Models hängen an Models (Relations sind erwartet). Jobs hängen an Models. Models hängen an Jobs (Dispatch-Layer-Bruch oben) — pragmatischer Mini-Zyklus über Klassen-Loader, kein Runtime-Loop.
