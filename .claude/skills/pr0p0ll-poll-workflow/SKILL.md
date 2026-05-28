---
name: pr0p0ll-poll-workflow
description: Lädt das Poll-Approval-Workflow-Wissen aus der KB. Invoken bei jeder Arbeit am Poll-Lifecycle (Create, In-Review, Approve, Deny, Disable, Closed), an Notification-Dispatching aus Poll-State-Übergängen, an `MyPollObserver`, an `AbstractPoll::approve/deny/disable`, oder an dem Set der 11 Notification-Jobs in `app/Jobs/`.
---

# Poll-Approval-Workflow

Hier sind die Hard-Facts. Vor jeder Workflow-Änderung lies das.

## State-Lifecycle

```
Draft → InReview → (Approved | Denied) → Published → Closed
        ↑ Disable can be triggered from any state
```

Felder, die State definieren (alle in `app/Models/Abstracts/Poll.php`):

| Feld | Draft | InReview | Approved | Denied | Disabled |
|---|---|---|---|---|---|
| `approved` | false | false | **true** | false | false |
| `in_review` | false | **true** | false | false | false |
| `visible_to_public` | false | false | **true** | false | false |
| `published_at` | null | null | **now()** | null | null |
| `closes_at` | null | null | **now()+closes_after** | null | null |
| `admin_notes` | null | null | null | **reason** | **reason** |

**Closed** ist kein State-Update — computed via `Poll::isClosed()` = `Carbon::make(published_at)->add(closes_after)->isPast()`.

## State-Transition-Methoden (einzige sanktionierte Wege)

### `Poll::approve()` — InReview → Approved

```php
public function approve(): void
{
    $this->update([
        'approved' => true,
        'in_review' => false,
        'visible_to_public' => true,
        'published_at' => now(),
        'closes_at' => now()->add($this->closes_after),
    ]);

    // Owner-Notifications (sofort, sync-dispatched)
    SendPollAcceptedEmailNotification::dispatch($this, $this->user);
    SendPollAcceptedPr0grammNotification::dispatch($this, $this->user);
    SendPollPublishedDiscordNotification::dispatch($this);
    SendPollAcceptedTelegramNotification::dispatch($this);

    // Bulk-Versand an interessierte User pro Channel
    foreach ($interestedUsersByChannel as $channel => $users) {
        foreach ($users as $user) {
            SendNewPollAvailable{Channel}Notification::dispatch($this, $user);
        }
    }
}
```

### `Poll::deny(string $reason)` — InReview → Denied

```php
public function deny(string $reason): void
{
    $this->update([
        'approved' => false,
        'in_review' => false,
        'visible_to_public' => false,
        'published_at' => null,
        'admin_notes' => $reason,
    ]);
    SendPollDeclinedEmailNotification::dispatch($this, $this->user);
    SendPollDeclinedPr0grammNotification::dispatch($this, $this->user);
}
```

### `Poll::disable(string $reason)` — Any → Disabled

```php
public function disable(string $reason): void
{
    $this->update([
        'approved' => false,
        'in_review' => false,
        'visible_to_public' => false,
        'published_at' => null,
        'admin_notes' => $reason,
    ]);
}
```

## Trigger-Pfade

| Übergang | Trigger | Code-Pfad |
|---|---|---|
| Draft → InReview | Filament-Form-Submit setzt `in_review=true` | `MyPollResource::Pages\CreateMyPoll::handleRecordCreation()` |
| InReview → Sync-Admin-Notify | `MyPollObserver::updated()` mit Dirty-Check | `app/Observers/MyPollObserver.php` |
| InReview → Approved | Filament-Action in AllPollsResource | ruft `$poll->approve()` |
| InReview → Denied | Filament-Action mit Begründung | ruft `$poll->deny($reason)` |
| Published → Closed | **Implizit, kein Code** | nur `Poll::isClosed()` read-only |
| Any → Disabled | Filament-Action für Admin | ruft `$poll->disable($reason)` |

## 11 Notification-Jobs (Übersicht)

Alle in `app/Jobs/`. Uniform: `$tries=15`, `$backoff=120`.

**Owner-direkt** (nach approve/deny):
1. `SendPollAcceptedEmailNotification`
2. `SendPollAcceptedPr0grammNotification`
3. `SendPollAcceptedTelegramNotification` (`ShouldBeUnique`)
4. `SendPollPublishedDiscordNotification`
5. `SendPollDeclinedEmailNotification`
6. `SendPollDeclinedPr0grammNotification`

**Bulk an interessierte User** (post-approve):
7. `SendNewPollAvailableEmailNotification`
8. `SendNewPollAvailablePr0grammNotification`
9. `SendNewPollAvailableDiscordNotification`
10. `SendNewPollAvailableTelegramNotification`

**Lifecycle-Ende**:
11. `SendCreatePostReminderNotification` — Erinnerung an Original-Content-Link nach Poll-End.

## Bekannte Gaps

KB markiert offene Punkte rund um den Workflow:

1. **`OWNPOLLHASENDED`** + **`PARTICIPATEDPOLLHASFINISHED`** Notification-Types existieren im Enum, aber kein automatischer Trigger bei `isClosed()` erkennbar. **Wahrscheinlich fehlt ein Scheduled-Job.** Bei Closure-Logik-Arbeit: erste Empfehlung ist Scheduler-Hook prüfen.
2. **`CREATEPOSTREMINDER`** Job existiert, Auslöser-Logik unklar.
3. **Closed-Auto-Detection** ohne State-Update macht Reporting auf `OWNPOLLHASENDED` schwierig.
4. **`not_anonymous` Boolean** + AnonymousUser-Participation: Beziehung in UI nicht klar dokumentiert.

## Authorization (Policy-Truth)

`app/Policies/MyPollPolicy.php`:

- `view` — Owner ODER Admin.
- `create` — alle eingeloggten User.
- `update` — Owner ODER Admin, **aber forbidden wenn `in_review || approved`**.
- `delete` — Owner ODER Admin.

`PublicPollPolicy` für read-side: `view` prüft Target-Group-Match via `Poll::userIsWithinTargetGroup($user)`.

## Target-Group-Filter

Speichert `target_group` als JSON-Spalte mit Demographics-Schema:

```json
{
    "gender": "F",
    "nationality": "DE",
    "region": "Bayern",
    "min_age": 18,
    "max_age": 30
}
```

`TargetGroupService::userIsWithinTargetGroup(array $aBuilderData, ?User $user)` evaluiert. Bei leerem Filter → für alle sichtbar.

## Test-Strategie (KB markiert ALS LÜCKE)

```php
// tests/Feature/PollApprovalTest.php (existiert nicht — würde hier landen)
it('approves and dispatches notifications', function () {
    Queue::fake();
    $poll = Poll::factory()->inReview()->create();
    $poll->approve();
    expect($poll->refresh()->approved)->toBeTrue()
        ->and($poll->in_review)->toBeFalse()
        ->and($poll->published_at)->not->toBeNull()
        ->and($poll->closes_at)->not->toBeNull();
    Queue::assertPushed(SendPollAcceptedEmailNotification::class);
});

it('denies and stores reason', function () {
    Queue::fake();
    $poll = Poll::factory()->inReview()->create();
    $poll->deny('Spam-Inhalt');
    expect($poll->refresh()->approved)->toBeFalse()
        ->and($poll->admin_notes)->toBe('Spam-Inhalt');
    Queue::assertPushed(SendPollDeclinedEmailNotification::class);
});
```

## Anti-Patterns

- ❌ **State-Felder direkt updaten** (`$poll->update(['approved' => true])`) — Notification-Dispatch wird umgangen.
- ❌ **Neue Notification-Type ohne Mapping in `User::wantsNotification()`** — Channel-Routing greift nicht.
- ❌ **Job-Dispatch ohne `userIsWithinTargetGroup`-Guard** — Notifications gehen an alle, ignorieren Target-Filter.
- ❌ **Loop ohne `Bus::batch()`** für Bulk → erkanntes Risiko, kein Failure-Aggregation.

## Cross-References

- [`.ai/knowledge-base/01-architecture/data-flow.md`](../../../.ai/knowledge-base/01-architecture/data-flow.md) — End-to-End-Diagramm.
- [`.ai/knowledge-base/06-domain/business-rules.md`](../../../.ai/knowledge-base/06-domain/business-rules.md) — alle State-Übergänge.
- [`.ai/knowledge-base/03-dependencies/usage/horizon.md`](../../../.ai/knowledge-base/03-dependencies/usage/horizon.md) — Queue-Konvention.
- [`.ai/knowledge-base/02-conventions/async-and-concurrency.md`](../../../.ai/knowledge-base/02-conventions/async-and-concurrency.md) — Job-Pattern + Pitfalls.
