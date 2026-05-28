---
source: domain-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Entities

## Poll (AbstractPoll / MyPoll / PublicPoll)

Zentrale Entität. Umfrage mit n Fragen, erstellt von User. Approval-Workflow + Notification-Lifecycle.

**File**: `app/Models/Abstracts/Poll.php` (AbstractPoll), `app/Models/Polls/MyPoll.php`, `app/Models/Polls/PublicPoll.php`.

**Felder**:
- `id` PK
- `user_id` FK → User (Ersteller)
- `title` text, fulltext-Index
- `description` text nullable, fulltext-Index
- `closes_after` string (Enum-Value, z.B. `"+1 week"`)
- `not_anonymous` boolean default true
- `original_content_link` text nullable
- `visible_to_public` boolean default false
- `in_review` boolean nullable
- `approved` boolean default false
- `published_at` datetime nullable
- `closes_at` datetime nullable (computed bei approve)
- `admin_notes` text nullable (Ablehnungs-Grund)
- `category_id` FK → Category nullable
- `target_group` json nullable (Demographics-Filter)
- `created_at`, `updated_at`

**Relations**:
- `user()` → BelongsTo User
- `questions()` → HasMany Question
- `answers()` → HasMany Answer
- `participants()` → BelongsToMany User via `participants_2_polls` (Pivot mit `rating`)
- `category()` → BelongsTo Category

**Lifecycle-States**:
- **Draft**: `approved=false, in_review=false, visible_to_public=false, published_at=null`
- **InReview**: `in_review=true`
- **Approved**: `approved=true, in_review=false, visible_to_public=true, published_at=now()`
- **Denied**: `approved=false, in_review=false, visible_to_public=false, published_at=null, admin_notes=reason`
- **Published**: `approved=true, published_at set, closes_at computed`
- **Closed**: `published_at + closes_after < now()` (read-only via `isClosed()`)

**Key-Methoden**:
- `approve()`, `deny($reason)`, `disable($reason)` — State-Transitions + Dispatches
- `isApproved()`, `isInReview()`, `isVisibleForPublic()`, `isClosed()`, `hasEnded()`
- `userIsWithinTargetGroup(User $user)` — delegiert an TargetGroupService
- `resultsArePublic()` — Logik für Ergebnis-Sichtbarkeit
- `getAmountOfParticipantsByGender(Gender)`, `getAverageAgeOfParticipants()` — Statistik

## Question

Frage innerhalb eines Polls. Gehört zu genau einem Poll, hat einen QuestionType.

**File**: `app/Models/Question.php`

**Felder**:
- `id` PK
- `poll_id` FK → Poll
- `question_type_id` FK → QuestionType
- `title` text
- `description` text nullable
- `position` integer (Reihenfolge)
- `options` json nullable (Array für Multiple-Choice)
- `blocks` json nullable
- `created_at`, `updated_at`

**Relations**:
- `poll()` → BelongsTo Poll
- `questionType()` → BelongsTo QuestionType
- `answers()` → HasMany Answer
- `answerType()` → computed AnswerType-Instanz via `questionType->answerType()`

**Methoden**:
- `hasOptions()` — true wenn options-JSON nicht leer

## Answer

Konkrete Nutzer-Antwort. Polymorphe Relation auf AnswerType-Subklasse.

**File**: `app/Models/Answer.php`

**Felder**:
- `id` PK
- `poll_id` FK → Poll
- `question_id` FK → Question
- `user_id` FK → User nullable (für registrierte Antworten)
- `answerable_type` string (Klassenname, z.B. `"App\Models\AnswerTypes\BoolAnswer"`)
- `answerable_id` bigint (FK zu AnswerType-spezifischer Tabelle)
- `anonymous_user_id` FK → AnonymousUser nullable
- `created_at`, `updated_at`
- **Unique-Key**: `(poll_id, question_id, user_id, anonymous_user_id)`

**Relations**:
- `poll()`, `question()`, `user()`, `anonymousUser()`, `answerable()` (MorphTo)

**Methoden**:
- `scopeFilter($query, array $filters)` — WHERE-Clauses für Demographic-Filter (nationality, region, gender, min_age, max_age)

## User

pr0gramm-authentifizierter Nutzer. Bannable, FilamentUser, MustVerifyEmail.

**File**: `app/Models/User.php`

**Felder**:
- `id` PK
- `name` string (pr0gramm-Username)
- `email` string unique
- `email_verified_at` datetime nullable
- `password` string nullable (rotiert bei jedem OAuth-Login)
- `birthday` date nullable
- `nationality` string nullable (z.B. ISO-3166-1)
- `gender` string (Gender-Enum-Value)
- `region` string nullable
- `admin` boolean default false
- `pr0gramm_identifier` string (OAuth-ID)
- `last_data_change` datetime nullable (Cooldown-Tracking)
- `banned_at` datetime nullable (Bannable-Trait)
- `created_at`, `updated_at`

**Relations**:
- `polls()` → HasMany Poll
- `participations()` → BelongsToMany Poll via `participants_2_polls` (Pivot `rating`)
- `answers()` → HasMany Answer
- `notificationSettings()` → HasMany NotificationSetting
- `notificationPreference()` → HasOne NotificationPreference (**deprecated**)

**Hidden**: `password`, `email`, `birthday`, `nationality`, `gender`, `region`.

**Casts**: `email_verified_at`, `last_data_change`, `banned_at` → `datetime`; `birthday` → `date`; `password` → `hashed`.

**Key-Methoden**:
- `isAdmin()` — `admin === true`
- `canAccessPanel(Panel)` — Filament-Access (TODO: aktuell allow-all)
- `canUpdateDemographicData()` — `last_data_change` > 2 Monate her
- `getDemographicData()` → Array `[birthday, nationality, gender, region]`
- `createAnonymousUser()` → erzeugt AnonymousUser aus eigenen Daten
- `getNotificationSettingsForForm()` → nested Array für UI
- `updateNotificationSettings(array)` → batch `updateOrCreate`
- `wantsNotification(NotificationChannel, NotificationType)` → bool
- `getPr0grammName()` → pr0gramm-Username

## AnonymousUser

Pseudo-User für anonyme Poll-Beteiligung.

**File**: `app/Models/AnonymousUser.php`

**Felder**:
- `id`, `birthday`, `nationality`, `gender`, `region`, `created_at`, `updated_at`

**Relations**:
- `answers()` → HasMany Answer

**Methoden**:
- `getAgeAttribute()` — Accessor für `birthday->age`

## QuestionType

Template-Typ für Fragen.

**File**: `app/Models/QuestionType.php`

**Felder**:
- `id`, `name`, `answer_type` (Klassennamen-String), `component` (radio/checkbox-list/textarea/…), `disabled` boolean default false

**Relations**:
- `questions()` → HasMany Question

**Methoden**:
- `answerType()` → `new $this->answer_type` (instanziiert die AnswerType-Klasse)
- `hasOptions()` → true wenn `component in ['radio', 'checkbox-list']`
- `scopeActive($query)` → `where disabled = false`

## AnswerType-Subklassen

Alle erben von `App\Models\Abstracts\AnswerType` und haben `morphOne(Answer::class, 'answerable')`.

| Subklasse | Feld | Beschreibung |
|-----------|------|--------------|
| `BoolAnswer` | `answer_value` boolean | Ja/Nein |
| `TextAnswer` | `answer_value` string | Freitext |
| `SingleOptionAnswer` | `option_id` FK | Single-Select (Radio) |
| `MultipleChoiceAnswer` | `option_ids` JSON | Multi-Select |
| `ColorAnswer` | `answer_value` string (hex) | Farbwahl |
| `DateAnswer` | `answer_value` date | Datum |
| `DateTimeAnswer` | `answer_value` datetime | Datum + Zeit |
| `TimeAnswer` | `answer_value` time | Uhrzeit |
| `NumberAnswer` | `answer_value` numeric | Zahl |

## Category

**File**: `app/Models/Category.php`

**Felder**: `id`, `name`, `description` nullable, `icon` nullable, `enabled` boolean default true, `created_at`, `updated_at`.

**Relations**: `publicPolls()` → HasMany PublicPoll.

## Notification-Stack

### NotificationChannel
**File**: `app/Models/NotificationChannel.php`. Felder: `name` (z.B. "Discord"), `route` (z.B. `"discord"`), `created_at`, `updated_at`.

### NotificationType (Enum + DB-Tabelle)
**Enum**: `app/Enums/NotificationType.php`. Values: NEWPOLLPUBLISHED, POLLACCEPTED, POLLDECLINED, OWNPOLLHASENDED, PARTICIPATEDPOLLHASFINISHED, CREATEPOSTREMINDER. HasLabel implementiert.

### NotificationSetting
**File**: `app/Models/NotificationSetting.php`. Junction: `user_id`, `notification_channel_id`, `notification_type_id`, `enabled` boolean.

Pattern: `User::wantsNotification(Channel, Type)` prüft `NotificationSetting::enabled` für die Kombination.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `02-conventions/data-and-types.md` — die Typ/Validierungs-Shape dieser Entities
- `06-domain/business-rules.md` — Regeln die diese Entities constrain

<!-- research:cross-refs-end -->
