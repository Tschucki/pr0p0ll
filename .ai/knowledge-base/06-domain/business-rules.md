---
source: domain-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Business-Rules

## Authorization / Access

- **Poll view** — Owner ODER Admin. `app/Policies/MyPollPolicy.php::view()`.
- **Poll create** — jeder eingeloggte User. `MyPollPolicy::create()` returns true.
- **Poll update** — Owner ODER Admin, **aber forbidden wenn `in_review` ODER `approved`**. `MyPollPolicy::update()`.
- **Poll delete** — Owner ODER Admin. `MyPollPolicy::delete()`.
- **Filament-Panel-Access** — `User::canAccessPanel(Panel)` aktuell allow-all (TODO-Kommentar im Code: später einschränken).
- **MyPoll Global-Scope** — `OwnPollScope` filtert auf `user_id = Auth::id()`. `MyPoll::all()` zeigt nur eigene Polls.
- **PublicPoll Global-Scope** — `PublicPollScope` filtert auf `approved = true AND visible_to_public = true`.
- **Target-Group-Filtering** — `Poll::userIsWithinTargetGroup(User)` delegiert an `TargetGroupService`. Prüft `target_group`-JSON gegen User-Demographie. Bei leerem Filter → für alle sichtbar.
- **Banned-User-Block** — `ForbidBannedUser`-Middleware auf Filament-Panel-Level. Gebannte User kommen nicht ins Admin-Panel.

## Validation

Validation läuft in Filament-Form-Builder UND zusätzlich inline `Validator::make()` — **keine FormRequest-Klassen**.

- **Poll title** — required, text.
- **Poll description** — nullable text.
- **closes_after** — required ClosesAfter-Enum-Value.
- **target_group** — nullable JSON (falls gesetzt, muss Schema matchen — geprüft im TargetGroupService).
- **Question title** — required text.
- **Question options** — required falls `QuestionType::hasOptions() === true`.
- **Answer unique** — DB-Unique-Constraint `(poll_id, question_id, user_id, anonymous_user_id)` verhindert Mehrfach-Stimmen.
- **User demographics** — optional, updatable via UserSettingsPage. Cooldown 2 Monate (`canUpdateDemographicData()`).

Validation-Errors werden als Filament-Notifications gezeigt (deutsch hardcoded).

## State-Transitions

### Draft → InReview

Triggered durch Form-Submit in MyPollResource (Filament). Setzt `in_review = true`. `MyPollObserver::updated()` mit Dirty-Check sendet `PollNeedsReview`-Notification an Admin-Collection (synchron via `Notification::send()`).

### InReview → Approved

Admin klickt Approve-Action in `AllPollsResource`. Ruft `Poll::approve()`:

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
    SendPollAcceptedEmailNotification::dispatch($this, $this->user);
    SendPollAcceptedPr0grammNotification::dispatch($this, $this->user);
    SendPollPublishedDiscordNotification::dispatch($this);
    SendPollAcceptedTelegramNotification::dispatch($this);

    foreach ($interestedUsersByChannel as $channel => $users) {
        foreach ($users as $user) {
            SendNewPollAvailable{$channel}Notification::dispatch($this, $user);
        }
    }
}
```

Dispatcht 4 Owner-Notifications + Bulk-Loop pro Channel. Notifications werden per `User::wantsNotification()` gefiltert (Settings-basiert).

### InReview → Denied

Admin klickt Deny mit Begründung. `Poll::deny($reason)`:

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

### Published → Closed

**Implizit** — kein State-Update. `isClosed()`-Methode prüft zur Laufzeit:

```php
public function isClosed(): bool
{
    if ($this->published_at !== null && $this->closes_after !== null) {
        return Carbon::make($this->published_at)?->add($this->closes_after)->isPast();
    }
    return false;
}
```

Kein automatischer Trigger bei Closure. Eventuell sollte `OWNPOLLHASENDED`-Notification über Scheduled-Job ausgelöst werden — aktuell unklar wer das versendet.

### Any State → Disabled

Admin disable-Aktion bei Regelbruch. `Poll::disable($reason)` — setzt alle visible_to_public/approved/published_at zurück.

## Berechnungen

- **`closes_at`** — `published_at + closes_after` (Carbon-Addition mit ClosesAfter-Enum-String). Stored als absoluter Timestamp bei `approve()`.
- **`isClosed()`** — `Carbon::make(published_at)?->add(closes_after)->isPast()`.
- **`resultsArePublic()`** — true wenn:
  1. `original_content_link IS NULL AND closes_at + 2 weeks < now()`, ODER
  2. `isClosed() AND original_content_link IS NOT NULL`.
- **`getAmountOfParticipantsByGender(Gender)`** — `answers.whereHas('anonymousUser').where('gender', $gender)->count()`. Distinct auf `anonymous_user_id`.
- **`getAverageAgeOfParticipants()`** — map each distinct answer to `birthday->age`, dann `avg()`, formatiert via `Number::format(..., precision: 0)`.
- **Result-Widgets** — `PollResultService::createResultWidget()` dispatcht je nach AnswerType:
  - SingleOptionAnswer/MultipleChoiceAnswer → BarChart (via ApexCharts).
  - BoolAnswer → Boolean-Chart (true/false-Counts).
  - TextAnswer → Text-Widget (Liste).

## Time / Scheduling

- **Poll-Dauer** — definiert via `closes_after` ClosesAfter-Enum (THREEDAYS … SIXWEEKS). Wird beim `approve()` zu absolutem `closes_at` gerechnet.
- **Demographic-Update-Cooldown** — `User::canUpdateDemographicData()` erlaubt Update nur wenn `last_data_change` > 2 Monate her. Beim Update wird `last_data_change` aktualisiert.
- **Notification-Delivery** — `SendPollAccepted*` direkt nach `approve()`. `SendNewPollAvailable*` async im Bulk-Loop. Versand-Routing über `NotificationSetting::enabled`.
- **Ergebnis-Veröffentlichung** — `resultsArePublic()` prüft `closes_at + 2 weeks` ODER `original_content_link`. Keine aktive Trigger-Logik, nur conditional Rendering.
- **Scheduler** — `app:login-to-pr0gramm` stündlich (API-Session warm halten), `ban:delete-expired` jede Minute.

## Lücken / Inkonsistenzen

- **`OWNPOLLHASENDED` und `PARTICIPATEDPOLLHASFINISHED` Notifications** — definiert im Enum, aber kein automatischer Trigger bei Poll-Closure erkennbar. Vermutlich fehlt Scheduled-Job.
- **`CREATEPOSTREMINDER`** — Job `SendCreatePostReminderNotification` existiert, aber Auslöser-Logik unklar.
- **`Filament::canAccessPanel`** — TODO-Kommentar: aktuell allow-all, sollte einschränken.
- **Anonymous-Voting + not_anonymous** — Beziehung zwischen `Poll::not_anonymous` (boolean default true) und AnonymousUser-Beteiligung in der UI-Logik nicht vollständig nachvollziehbar.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `06-domain/entities.md` — die Entities die diese Regeln constrain
- `02-conventions/error-handling.md` — wo Regel-Verletzungen raisen
- `02-conventions/testing.md` — Tests die diese Regeln pinnen

<!-- research:cross-refs-end -->
