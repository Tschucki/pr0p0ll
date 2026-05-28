---
source: architecture-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Verzeichnis-Layout

## Top-Level

```
pr0p0ll/
├── app/                  PHP-Applikationscode (PSR-4 → App\)
├── bootstrap/            Laravel-Boot (cache/, app.php)
├── config/               24 Config-PHP-Files
├── database/             Migrations, Factories, Seeders
├── lang/                 i18n-Files (laravel-lang/common)
├── public/               Webroot, kompilierte Assets via Vite
├── resources/            JS/Vue/Blade/CSS-Quellen + markdown/
├── routes/               api.php, channels.php, console.php, web.php
├── storage/              Logs, Sessions, Cache, Uploads
├── tests/                Pest + PHPUnit + Dusk
└── vendor/               Composer-Deps
```

## app/ (custom-erweitert)

```
app/
├── Connectors/           Externe API-Adapter (Pr0gramm-API u.a.)
├── Console/              Artisan-Commands + Kernel
├── Enums/                Domain-Enums (Gender, Nationality, ClosesAfter, NotificationType, QuestionType …)
├── Exceptions/           Laravel-Standard Handler.php (custom-Exceptions: keine)
├── Filament/             Admin-Panel
│   ├── Actions/          Custom Filament-Actions (AddOriginalContentLinkAction)
│   ├── Pages/            Standalone-Pages (Login, FAQ, Leaderboard, PollResults, Pr0PostCreator, UserSettingsPage)
│   ├── Resources/        5 Resources: AllPolls, Category, MyPoll, PublicPolls, User
│   └── Widgets/          StatsOverview, NeedsDataReviewWidget
├── Http/
│   ├── Controllers/      Frontend\FrontendController, Pr0authController, LoginRedirectController
│   ├── Middleware/       HandleInertiaRequests + Laravel-Standard
│   ├── Requests/         FormRequests (nicht detektiert — Validation inline)
│   └── Kernel.php
├── Jobs/                 11 Notification-Jobs (Send*Email/Pr0gramm/Discord/Telegram-Notification)
├── Models/               Eloquent-Models
│   ├── Abstracts/        AbstractPoll, AbstractAnswerType
│   ├── AnswerTypes/      BoolAnswer, TextAnswer, MultipleChoiceAnswer, SingleOptionAnswer, ColorAnswer, DateAnswer, DateTimeAnswer, TimeAnswer, NumberAnswer
│   └── Polls/            MyPoll, PublicPoll
├── Notifications/        Laravel-Notification-Klassen (für Channels Mail/Discord/Telegram/Pr0gramm)
├── Observers/            MyPollObserver
├── Policies/             MyPollPolicy
├── Providers/            AppServiceProvider, AuthServiceProvider, EventServiceProvider, HorizonServiceProvider, RouteServiceProvider
│   └── Filament/         Pr0p0llPanelProvider (einziges Panel, id=pr0p0ll, path=/pr0p0ll)
└── Services/             TargetGroupService, PollFormService, PollResultService, …
```

## Abweichungen vom Laravel-Default

- `app/Connectors/` — kein Laravel-Standard; trennt externe HTTP-Clients (z.B. pr0gramm-API für Login-Job).
- `app/Services/` — bewusst statt fat-Controller/Model.
- `app/Models/Abstracts/` + `app/Models/Polls/` + `app/Models/AnswerTypes/` — STI-ähnliches Pattern: AbstractPoll mit globalen Scopes (OwnPollScope, PublicPollScope) für MyPoll/PublicPoll.
- `app/Filament/` (statt der älteren `app/Filament/Resources/`) — Filament-3-Standard-Layout.
- Keine `app/Http/Controllers/API/` — `routes/api.php` ist leer.
- Keine `app/Http/Resources/` — Inertia rendert direkt Page-Components.

## Was "zu groß" hier ist

`app/Models/Abstracts/Poll.php` (~200 LOC) trägt Workflow-Methoden (`approve()`, `deny()`, `disable()`) + Notification-Dispatch + Statistik-Methoden. Klassischer Fat-Model — Dispatch ließe sich in Observer auslagern (bestehender `MyPollObserver` macht das partial).

Filament-Resources schwanken zwischen 80-250 LOC, was Form-Builder + Table-Builder erklärt — keine Auslagerung üblich in Filament-Welt.

Vue-Pages unter `resources/js/Pages/Frontend/` sind klein (Landing/Imprint/Privacy/Terms), nur 4 Stück.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `03-dependencies/README.md` — welche Deps die Schichten verdrahten

<!-- research:cross-refs-end -->
