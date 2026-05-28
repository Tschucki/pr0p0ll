---
source: architecture-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Exemplars — kanonische Files zum Erst-Lesen

Sieben Dateien die "so macht's das Repo" zeigen. Erstes Mal im Codebase: lies in dieser Reihenfolge.

## 1. `app/Models/Abstracts/Poll.php`

**Warum**: Trägt Workflow (`approve`, `deny`, `disable`), Statistik-Methoden, Notification-Dispatch. Zentraler Schmelzpunkt der Domain. Auch typischer Layer-Bruch (Model dispatcht Jobs).

```php
public function approve(): void
{
    $this->update([
        'published_at' => now(),
        'closes_at' => now()->add($this->closes_after),
    ]);
    SendPollAcceptedEmailNotification::dispatch($this, $this->user);
    // ... + Bulk-Loop
}
```

## 2. `app/Services/TargetGroupService.php`

**Warum**: Idealtypisch nach Team-Style — `declare(strict_types=1)`, static Methoden, Early-Return, Eloquent-Builder-Chain. Eigenheit: Array-Vars mit Präfix `$a`.

```php
public static function userIsWithinTargetGroup(array $aBuilderData = [], ?User $user = null): bool
{
    if (empty($aBuilderData) || $user === null) {
        return true;
    }
    $aTargetGroupData = self::builderDataToArray($aBuilderData);
    return self::baseQuery($aTargetGroupData)->where('id', $user->getKey())->exists();
}
```

## 3. `app/Filament/Resources/MyPollResource.php`

**Warum**: Filament-Resource-Form mit nested Repeater für Questions + Options. Typische Form-Builder-Chain.

## 4. `app/Filament/Resources/MyPollResource/Pages/CreateMyPoll.php`

**Warum**: Zeigt Validator::make()-Pattern inline statt FormRequest. Try/Catch um ValidationException → Filament-Notification. Repo-Konvention für Form-Validation.

## 5. `app/Jobs/SendNewPollAvailableEmailNotification.php`

**Warum**: Job-Template. Alle 11 Jobs im Repo folgen dem Muster: `ShouldQueue`-Implementation, uniform `$tries=15` / `$backoff=120`, `handle()` enthält Guard + Notification::route()->notify().

```php
public int $tries = 15;
public int $backoff = 120;

public function handle(): void
{
    if ($this->poll->userIsWithinTargetGroup($this->user) === false) {
        return;
    }
    Notification::route('mail', [$this->user->email => $this->user->name])
        ->notify(new NewPollAvailableEmailNotification($this->poll));
}
```

## 6. `app/Observers/MyPollObserver.php`

**Warum**: Observer-Pattern für Model-Lifecycle-Events. Reagiert auf `updated()` mit Dirty-Check für State-Transitions.

## 7. `app/Http/Middleware/HandleInertiaRequests.php`

**Warum**: Zeigt Share-Pattern für Inertia-Frontend. **Achtung Schicht-Bruch**: leakt komplettes User-Model an Frontend. Lesen zur Awareness, nicht zur Nachahmung.

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => ['user' => \Auth::user()?->toArray()],
    ]);
}
```

## Bonus

- `app/Enums/ClosesAfter.php` — Enum-Pattern mit HasLabel-Trait für Filament-Select-Options.
- `app/Providers/Filament/Pr0p0llPanelProvider.php` — Filament-Konfiguration als Single-Source-of-Truth fürs Panel.
- `pint.json` — Style-Truth (siehe [`../02-conventions/code-style.md`](../02-conventions/code-style.md)).

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `02-conventions/README.md` — die Conventions, die diese Exemplars verkörpern

<!-- research:cross-refs-end -->
