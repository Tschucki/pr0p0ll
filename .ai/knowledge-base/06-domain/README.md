---
source: domain-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Domain — Übersicht

## Was Pr0p0ll ist

Pr0p0ll ist Umfrage-Plattform für die deutsche pr0gramm.com-Community. Nutzer erstellen Umfragen ("Polls") mit verschiedenen Frage-Typen, definieren Zielgruppen (öffentlich oder demographisch eingegrenzt) und werten Ergebnisse nach demographischen Filtern aus. Authentifizierung läuft ausschließlich über pr0gramm-OAuth — kein klassisches Register/Login.

Plattform hat Approval-Workflow: User submittet Poll → Admin reviewt → Approve/Deny. Nach Approval läuft Bulk-Notification an alle interessierten User über vier Channels: Mail, Discord, Telegram, pr0gramm-Direct-Message. Polls haben Lebensdauer (`closes_after`) und schließen automatisch nach ablaufender Frist.

Teilnehmer können registriert oder anonym antworten — anonyme Antworten speichern nur Demographie (Geburtsdatum, Nationalität, Geschlecht, Region) ohne User-Identität. Demographische Filter auf Ergebnissen ermöglichen Subgruppen-Analysen.

Stack: Laravel 10 + Filament 3 (Admin) + Inertia/Vue 3 (Public-Marketing-Pages).

## Schlüssel-Capabilities

1. **Poll erstellen** — Filament-Page `MyPollResource::Pages\CreateMyPoll` + `FrontendController` für SPA-Edge. Submittet Title, Description, Questions (n), Target-Group-JSON, ClosesAfter-Enum.
2. **9 Question-Types** — `app/Models/AnswerTypes/`: Bool, Text, MultipleChoice, SingleOption, Color, Date, DateTime, Time, Number. Polymorphe Answer-Relation.
3. **Zielgruppen-Targeting** — JSON-Filter (Gender, Nationality, Region, Age-Range). `TargetGroupService::userIsWithinTargetGroup()` werted aus.
4. **Approval-Workflow** — Felder `in_review`, `approved`, `admin_notes`. Methoden `approve()`, `deny()`, `disable()` in `app/Models/Abstracts/Poll.php`. Triggert Notifications.
5. **Poll-Partizipation** — User oder AnonymousUser antwortet via Filament-Page `PollParticipation`. Unique-Constraint `(poll_id, question_id, user_id, anonymous_user_id)`.
6. **Ergebnis-Filterung** — `PollResultService` aggregiert Answers nach Demographie. Widgets: BarChart (Multiple-Choice/SingleOption), Boolean-Chart, Text-List.
7. **Notification-Settings** — User × Channel × NotificationType. 6 Types: NEWPOLLPUBLISHED, POLLDECLINED, POLLACCEPTED, OWNPOLLHASENDED, PARTICIPATEDPOLLHASFINISHED, CREATEPOSTREMINDER.
8. **Multi-Channel-Versand** — 11 Jobs in `app/Jobs/`: pro Event × Channel ein Job. Horizon dispatcht parallel.
9. **User-Ban-System** — `Bannable`-Trait via cybercog/laravel-ban. Filament-Aktionen für Ban/Unban. Scheduler räumt expired Bans jede Minute.
10. **Kategorien** — `Category`-Model, FK auf `polls.category_id`. Filament editierbar.
11. **Demographische Anonymisierung** — `User::createAnonymousUser()` erzeugt AnonymousUser aus eigenen Demografie-Daten. Cooldown: `canUpdateDemographicData()` erlaubt Updates nur alle 2 Monate.
12. **Statistik-Widgets** — `StatsOverview` Widget, `NeedsDataReviewWidget`. `Leaderboard`-Page.

## Wie diesen Ordner lesen

- [`glossary.md`](glossary.md) — alphabetische Domain-Begriffe (Poll, Question, Answer, AnonymousUser, ClosesAfter etc.) mit Code-Mapping.
- [`entities.md`](entities.md) — Primary-Entities (Poll, Question, Answer, User, AnonymousUser, Category, QuestionType, Notification-Stack). Felder, Relationen, Lifecycle-States.
- [`business-rules.md`](business-rules.md) — Authorization, Validation, State-Transitions, Berechnungen, Time/Scheduling.

Bei neuer Filament-Resource: erst `entities.md` für relevante Entity-Beziehungen lesen. Bei Business-Logik-Änderung: `business-rules.md` für betroffene Regeln prüfen.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `00-overview.md` — Domain-Zusammenfassung fügt sich hier ein
- `06-domain/glossary.md` — alphabetische Begriffe
- `06-domain/entities.md` — Hauptmodelle
- `06-domain/business-rules.md` — Invarianten aus Code/Tests

<!-- research:cross-refs-end -->
