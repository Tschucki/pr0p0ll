---
source: dependency-usage-scout-filament
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# filament/filament (^3.2, installiert 3.3.50) — Nutzung

Projekt-spezifische Notizen. Generisch Filament-Docs in `04-frameworks/` würden bei aktiviertem context7-Fetching landen — aktuell off.

## Wo es genutzt wird

- `app/Providers/Filament/Pr0p0llPanelProvider.php` — einziger Panel-Provider, ID `pr0p0ll`, Path `/pr0p0ll`, Default-Panel.
- `app/Filament/Resources/` — 5 Resources: `AllPollsResource`, `CategoryResource`, `MyPollResource`, `PublicPollsResource`, `UserResource`. Jede mit Unterordner `Pages/`, teils `Widgets/`.
- `app/Filament/Pages/` — Custom Pages: `Login`, `FAQ`, `Leaderboard`, `PollResults`, `Pr0PostCreator`, `UserSettingsPage`.
- `app/Filament/Widgets/` — `NeedsDataReviewWidget`, `StatsOverview`.
- `app/Filament/Actions/AddOriginalContentLinkAction.php` — Custom-Action-Klasse.
- Kein `Clusters/`-Verzeichnis. Keine eigene `config/filament.php`.

## Top-APIs (Häufigkeits-Ordnung)

- `Filament\Notifications\Notification` (~15) — Toast-Feedback nach Actions. Pattern: `Notification::make()->title('…')->body('…')->send();`.
- `Filament\Actions\Action` (~11) — Row/Page-Actions mit `->visible(fn ($record) => …)` und `->url(fn (...) => route(…))`.
- `Filament\Infolists\Components\TextEntry`, `Filament\Infolists\Infolist`, `Section`, `RepeatableEntry` — read-only Detail-Views via `public static function infolist(Infolist $infolist): Infolist`.
- `Filament\Forms\Form`, `Forms\Components\TextInput`, `Textarea`, `Select`, `Toggle`, `Placeholder`, `Grid`, `DatePicker`, `Section` — Form-Builder.
- `Filament\Tables\Table`, `Tables\Columns\TextColumn`, `IconColumn` — Tabellen-Builder.
- `Filament\Resources\Resource`, `Resources\Pages\ListRecords`, `ViewRecord`, `EditRecord`, `CreateRecord` — Resource-Base.
- `Filament\Widgets\Widget`, `StatsOverviewWidget` — Dashboard-Widgets.
- `Filament\Facades\Filament` — `Filament::auth()`, `Filament::getUrl()`, `Filament::registerRenderHook(…)`.
- `Filament\Support\Contracts\HasLabel` — von Enums (Gender, Nationality, ClosesAfter etc.) implementiert für Select-Options.

## Patterns

Alle 5 Resources erben direkt von `Filament\Resources\Resource`. Statisch typisierte deutsche Labels via `$label` / `$pluralLabel`. Navigation in zwei Gruppen `'Administration'` und `'Umfragen'`. Routes deutsch geslugged (`umfragen`, `teilnehmen`, `results`).

Tabellen-Pattern uniform:

```php
TextColumn::make('title')->label('Titel')->sortable()->searchable()->toggleable()
TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')
TextColumn::make('questions_count')->counts('questions')
```

`bulkActions` immer `BulkActionGroup` mit `DeleteBulkAction`. Pagination einheitlich `->paginated([10, 25, 50])`.

Form-Pattern (z.B. CategoryResource):

```php
Forms\Components\Section::make('')->schema([
    Forms\Components\TextInput::make('title')->autofocus()->required()->maxLength(255)->label('Titel'),
    Forms\Components\Toggle::make('enabled')->label('Aktiv')->default(true),
    Forms\Components\Textarea::make('description')->label('Beschreibung'),
]),
```

Access-Control:

```php
public static function canAccess(): bool
{
    return \Auth::user()->isAdmin();
}
```

(AllPollsResource, UserResource — admin-only.)

## Wrapper / Adapter

**Kein Base-Resource, kein Trait**. Jede Resource extendet `Filament\Resources\Resource` direkt. Kein `app/Filament/Concerns/`. Einzige echte Subclass-Anpassung: `app/Filament/Pages/Login.php` als `extends \Filament\Pages\Auth\Login` für pr0gramm-OAuth-Redirect.

`AddOriginalContentLinkAction` ist eigene Action-Klasse, lebt unter `app/Filament/Actions/`.

## Konfiguration

Single-Panel-Setup in `Pr0p0llPanelProvider`. Wesentliche Calls:

```php
$panel
    ->default()
    ->id('pr0p0ll')
    ->path('pr0p0ll')
    ->login(Login::class)
    ->databaseNotifications()
    ->emailVerification(isRequired: false)
    ->colors(['primary' => '#ee4d2e'])
    ->viteTheme('resources/css/filament/pr0p0ll/theme.css')
    ->brandLogo(fn () => view('filament.admin.logo'))
    ->defaultThemeMode(ThemeMode::Dark)
    ->plugins([FilamentApexChartsPlugin::make()])
    ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
    ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
    ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
    ->middleware([
        ...DefaultPanel::middlewares(),
        \Cog\Laravel\Ban\Http\Middleware\ForbidBannedUser::class,
    ])
    ->renderHook('panels::global-search.after', fn () => view('filament.header.aftersearch'));
```

Branding: Primary `#ee4d2e` (pr0gramm-orange), Dark-Mode-Default. Ban-Middleware auf Panel-Level eingehängt.

## Beobachtete Pitfalls

- `app/Filament/Resources/PublicPollsResource.php:137` — `// TODO: Add Cache` in `getNavigationBadge()`. Badge ruft `PublicPoll::all()` für Target-Group-Count auf — N+1 / vollständige Iteration ohne Cache.
- `app/Filament/Resources/MyPollResource.php:208` — `// TODO: Add statistics (participation rate, etc.)`.
- `app/Filament/Actions/AddOriginalContentLinkAction.php:23` — `// TODO Send Notifications`.
- `PublicPollsResource::form()` ist leer (`->schema([])`) — Resource ist read-only-orientiert; Create läuft über `MyPollResource` + `Pr0PostCreator`-Page.
- Plugin-Imports verstreut in Resources (`Yepsua\Filament\Forms\Components\Rating` in `PublicPollsResource/Pages/PollParticipation.php:25`, `Widiu7omo\FilamentBandel\Actions\{Ban,Unban}Action` in `UserResource.php:16-17`) — keine Abstraktion. Plugin-Upgrade muss alle Call-Sites prüfen.

## Test-Strategie

**Keine Filament-Tests**. Kein `tests/Feature/Filament/`-Verzeichnis, keine Files unter `tests/` matchen `*Filament*`. Resources sind ungetestet.

Empfehlung beim Hinzufügen: Livewire-Component-Tests mit Filament-Testing-Traits (`livewire(ListResource::class)->assertCanSeeTableRecords(...)`, etc.). Pest-Setup vorhanden, müsste nur Filament-Test-Verzeichnis angelegt werden.

## Version-Pin-Notes

`composer.json`: `"filament/filament": "^3.2"`, `"filament/notifications": "^3.2"`. Installiert: 3.3.50 (composer.lock). Caret-Pin erlaubt Minor-Updates. Post-Install-Hook `@php artisan filament:upgrade` läuft automatisch.

Begleit-Plugins kompatibel zu Filament 3:
- `leandrocfe/filament-apex-charts ^3.1`
- `widiu7omo/filament-bandel ^2.0`
- `yepsua/filament-rating-field ^0.6`

Migration auf Filament 4 würde alle drei Plugins + Login-Subclass + Forms/Tables-Namespace brechen.

## "So nutzt man Filament in diesem Repo" — Playbook

1. Resource erstellen unter `app/Filament/Resources/{Name}Resource.php`, direkt `extends Filament\Resources\Resource`. Auto-Discovery greift, keine Panel-Provider-Änderung nötig.
2. Sprache: alle `$label`, `$pluralLabel`, `->label(...)` deutsch. Routes via `$slug` ebenfalls deutsch (z.B. `'umfragen'`).
3. Navigation in eine der bestehenden Gruppen einsortieren: `'Administration'` (intern) oder `'Umfragen'` (User-facing). Icon = `heroicon-o-*`.
4. Tabellen-Konvention übernehmen: `TextColumn::make()->label()->sortable()->searchable()->toggleable()`, Zeitstempel mit `->dateTime('d.m.Y H:i')->suffix(' Uhr')`. Bulk via `BulkActionGroup`. Pagination `->paginated([10, 25, 50])`.
5. Access-Control mit `public static function canAccess(): bool { return \Auth::user()->isAdmin(); }`. Feedback nach Actions immer per `Filament\Notifications\Notification::make()->title(...)->body(...)->send()` (deutsche Strings).
6. Banned-User-Schutz läuft automatisch via Panel-Middleware `ForbidBannedUser` — kein Custom-Check nötig.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `03-dependencies/README.md` — Filaments Platz im Stack
- `01-architecture/exemplars.md` — Exemplar-Filament-Resources
- `02-conventions/api-and-routing.md` — Filament-Auto-Routes-Konvention

<!-- research:cross-refs-end -->
