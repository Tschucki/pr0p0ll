---
source: domain-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Glossary

## A

- **Answer** — Konkrete Nutzer-Antwort auf eine Question. Morphs auf AnswerType-Subklasse (BoolAnswer, TextAnswer, …). `app/Models/Answer.php`. Unique-Constraint `(poll_id, question_id, user_id, anonymous_user_id)` — eine Antwort pro Frage pro User.
- **AnswerType** — Abstract Base für alle Antwort-Subklassen. MorphOne-Relation zu Answer. Instanzen via `question_type.answer_type`-Spalte (Klassennamen-Strings wie `"App\Models\AnswerTypes\BoolAnswer"`). `app/Models/Abstracts/AnswerType.php`.
- **AnonymousUser** — Pseudo-User für anonyme Beteiligung. Speichert nur Demografie (birthday, nationality, gender, region). `app/Models/AnonymousUser.php`. Erzeugt via `User::createAnonymousUser()`.

## B

- **Bannable** — Trait aus cybercog/laravel-ban, vom User-Model genutzt. Spalte `banned_at`. `ban:delete-expired`-Command räumt jede Minute.
- **BoolAnswer** — AnswerType für Ja/Nein-Fragen. Feld `answer_value` (boolean). Cast: `'answer_value' => 'boolean'`. `app/Models/AnswerTypes/BoolAnswer.php`.

## C

- **Category** — Taxonomie für Polls. Felder: `name`, `description`, `icon`, `enabled`. Filament editierbar. `app/Models/Category.php`.
- **ClosesAfter** — Enum für Poll-Abschluss-Dauer (THREEDAYS=`"+3 days"`, ONEWEEK, …, SIXWEEKS). Deutsch gelabelt ("Nach 1 Woche"). `app/Enums/ClosesAfter.php`. Wird beim `approve()` zu absolutem `closes_at`-Timestamp gerechnet.
- **closes_at** — Absoluter Timestamp des Poll-Endes. Computed beim `approve()` aus `published_at + closes_after`. `polls.closes_at` datetime.

## G

- **Gender** — Enum (MALE=`'M'`, FEMALE=`'F'`). HasLabel: "Männlich" / "Weiblich". `app/Enums/Gender.php`.

## H

- **HasLabel** — Filament-Contract aus `Filament\Support\Contracts\HasLabel`. Wird von allen Domain-Enums implementiert für Select-Options.

## M

- **MyPoll** — Subklasse von AbstractPoll. OwnPollScope filtert nur User-eigene Polls (`user_id = Auth::id()`). `app/Models/Polls/MyPoll.php`. Filament-MyPollResource nutzt diese Klasse.
- **MultipleChoiceAnswer** — AnswerType für Mehrfach-Auswahl. Feld `option_ids` (Array/JSON). PollResultService aggregiert zu BarChart.

## N

- **NotificationChannel** — Routing-Ziel (mail, discord, telegram, pr0gramm). `app/Models/NotificationChannel.php`. Feld `route` enthält Channel-Identifier.
- **NotificationPreference** — User-Präferenz (1:1 Relation). **Deprecated** zugunsten NotificationSetting.
- **NotificationSetting** — Junction zwischen User × NotificationChannel × NotificationType. Feld `enabled`. `app/Models/NotificationSetting.php`.
- **NotificationType** — Event-Typ (NEWPOLLPUBLISHED, POLLACCEPTED, POLLDECLINED, OWNPOLLHASENDED, PARTICIPATEDPOLLHASFINISHED, CREATEPOSTREMINDER). `app/Enums/NotificationType.php`.

## O

- **OwnPollScope** — Global Scope auf MyPoll, filtert auf `user_id = Auth::id()`. Sorgt dafür dass `MyPoll::all()` immer nur eigene Polls liefert.

## P

- **Poll** (AbstractPoll) — Zentrale Entität. Felder: `title`, `description`, `user_id`, `approved`, `in_review`, `visible_to_public`, `published_at`, `closes_at`, `closes_after` (Enum), `not_anonymous`, `admin_notes`, `category_id`, `target_group` (JSON). `app/Models/Abstracts/Poll.php`. Lifecycle: draft → in_review → (approved | denied) → published → closed.
- **PublicPoll** — Subklasse von AbstractPoll mit PublicPollScope (`approved = true`). Für öffentliche Browse-Experience.
- **PublicPollScope** — Global Scope, filtert auf `approved = true AND visible_to_public = true`.
- **pr0gramm** — Quell-Community. OAuth-Auth über `socialiteproviders/pr0gramm`. `pr0gramm_identifier`-Spalte auf User.

## Q

- **Question** — Frage innerhalb eines Polls. Felder: `title`, `description`, `position`, `options` (JSON für MC), `blocks` (JSON). FK auf `poll_id` + `question_type_id`. `app/Models/Question.php`.
- **QuestionType** — Template-Typ (z.B. "bool", "text", "multiple-choice"). Felder: `name`, `answer_type` (Klassennamen-String), `component` (radio/checkbox-list/textarea), `disabled`. `app/Models/QuestionType.php`.

## S

- **SingleOptionAnswer** — AnswerType für Single-Select (Radio). Feld `option_id`. Aggregation zu BarChart.

## T

- **TargetGroupService** — Business-Logik für Matching User gegen Poll-Target-Group-JSON (Gender, Nationality, Region, Age-Range). `app/Services/TargetGroupService.php`. Genutzt in Policy + Rendering + Job-Guard.
- **target_group** — JSON-Spalte auf polls. Definiert demografischen Filter. Wenn null/leer → öffentlich für alle.
- **TextAnswer** — AnswerType für freien Text. Feld `answer_value` (string). `app/Models/AnswerTypes/TextAnswer.php`.

## U

- **User** — pr0gramm-authentifizierter Nutzer. Bannable, FilamentUser, MustVerifyEmail. Felder: `name` (pr0gramm-Username), `email`, `birthday`, `nationality`, `gender`, `region`, `admin` (boolean), `pr0gramm_identifier`, `last_data_change`, `banned_at`. Relations: `polls()`, `participations()`, `answers()`, `notificationSettings()`. `app/Models/User.php`.

## Y

- **yes_no field** — Synonym für BoolAnswer/Bool-QuestionType. Aus letztem Bugfix-Commit ("fix issue with yes_no fields") sichtbar.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `06-domain/entities.md` — Entities definieren oft Begriffe
- `02-conventions/data-and-types.md` — Typen benennen dieselben Konzepte

<!-- research:cross-refs-end -->
