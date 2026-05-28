---
source: conventions-scout-api-routing
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# API und Routing

## Routing-Layer

Drei disjunkte Routing-Spaces:

1. **Inertia-SPA** (`/`, `/impressum`, `/datenschutz`, `/nutzungsbedingungen`) → Public-Marketing-Pages via Inertia.
2. **Filament-Admin** (`/pr0p0ll/*`) → Auto-Routes aus 5 Resources + Custom-Pages.
3. **OAuth-Flow** (`/oauth/start`, `/oauth/callback`, `/login`) → pr0gramm-Login.

`routes/api.php` ist **leer** — keine REST-API exponiert.

```php
// routes/web.php
Route::get('/', [FrontendController::class, 'landing'])->name('frontend.landing');
Route::get('/impressum', [FrontendController::class, 'imprint'])->name('frontend.imprint');

Route::middleware(['guest'])->group(function () {
    Route::get('/oauth/callback', [Pr0authController::class, 'callback'])->name('oauth.callback');
    Route::get('/oauth/start', [Pr0authController::class, 'start'])->name('oauth.start');
    Route::get('login', LoginRedirectController::class)->name('login');
});
```

Filament-Routes werden automatisch unter `/pr0p0ll/{slug}` registriert durch `Pr0p0llPanelProvider`.

## URL-Konventionen

- **Inertia-Pages**: deutsche URL-Slugs (`/impressum`, `/datenschutz`, `/nutzungsbedingungen`).
- **Filament-Resources**: ebenfalls deutsche Slugs via `$slug`-Property (z.B. `'umfragen'`, `'teilnehmen'`).
- **OAuth-Routes**: englische Konvention (`/oauth/start`, `/oauth/callback`) — typisch für OAuth.
- Plural/Singular: Resources eher singular im Slug (z.B. `'umfragen'` als Plural — Sprachgefühl).

Keine REST-Resource-Routes (`Route::resource()`) im App-Code — Filament-Auto-Routes ersetzen das.

## HTTP-Method-Semantik

Inertia-Routes: alle GET. POST-Submits für Form-Actions würden über Inertia.post() oder Controller-Actions laufen (im aktuellen Code nicht definiert).

Filament-CRUD intern: POST (create), PATCH (update), DELETE (destroy) durch Filament-Livewire-Komponenten. Keine expliziten PUT/PATCH-Definitionen in `web.php`.

## Status-Codes

| Szenario | Code | Quelle |
|----------|------|---|
| Inertia-Page render | 200 | `Inertia::render()` |
| Auth-Required-Redirect | 302 | `Authenticate::redirectTo()` → `route('login')` |
| OAuth-Callback-Erfolg | 302 | `callback()` → `redirect()->route('filament.pr0p0ll.pages.dashboard')` |
| Login-Failure (rate-limit > 5) | 302 + Notification | Filament-Login-Page overrides |
| Bereits authentifiziert + geschützte Route | 302 | `RedirectIfAuthenticated` → `RouteServiceProvider::HOME` |

## Request-Shape

**Keine FormRequest-Klassen** in `app/Http/Requests/` detektiert. Inertia-Pages haben aktuell keine Form-Submissions (nur Marketing-Inhalte). Filament-Forms validieren via Form-Builder + inline `Validator::make()`.

Shared-Inertia-Props via `HandleInertiaRequests::share()`:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => \Auth::user()?->toArray(),
        ],
    ]);
}
```

⚠️ **Security-Befund**: `->toArray()` leakt komplettes User-Model an Frontend. `$hidden`-Pattern auf User-Model fängt sensible Felder ab, aber Layer-Bruch bleibt.

## Response-Shape

**Inertia**: Page-Component-Name + Props-Object:

```php
return Inertia::render('Frontend/Landing', [
    'userCount' => (string) Number::format(number: User::count(), precision: 0, locale: 'de'),
    'pollCount' => (string) Number::format(number: Poll::where('approved', true)->count(), precision: 0, locale: 'de'),
]);
```

Zahlen werden serverseitig zu Strings via `Number::format(..., locale: 'de')` formatiert — Frontend kriegt fertig-formatierte deutsche Strings.

**Kein Envelope-Pattern** (z.B. `{ data: ..., errors: ... }`). Inertia handlet Serialisierung.

**Filament**: Livewire-HTML-Responses, intern.

## Pagination

In öffentlichen Routes nicht relevant. Filament-Resources nutzen Standard-Filament-Pagination mit `->paginated([10, 25, 50])`. Pattern repo-weit uniform.

## Error-Responses

Inertia liefert Page-Component + `$errors`-Bag im Validation-Fall (würde aus Session-Flash via `ShareErrorsFromSession`-Middleware kommen — Inertia bridge automatically).

Filament: Toast-Notifications:

```php
Notification::make('Login failed')
    ->title('Hä. Komisch')
    ->body('Irgendwas ist schief gelaufen.')
    ->send()
    ->warning();
```

## Authentication / Authorization Middleware

- **Sanctum** (`config/sanctum.php`) → Session-Cookie-Guard für SPA. Kein Bearer-Token-Use.
- **`auth`-Middleware** → Standard Laravel-Auth-Guard auf Filament-Routes (via Panel-Provider).
- **`guest`-Middleware** → OAuth-Routes (`/oauth/*`, `/login`).
- **`HandleInertiaRequests`** → global in `web`-Group via Kernel.php.
- **`ForbidBannedUser`** (cybercog/laravel-ban) → in Filament-Panel-Middleware-Stack registriert; gebannte User können Panel nicht betreten.

OAuth-Login-Flow:

```php
// app/Http/Controllers/Pr0authController.php
public function callback(): RedirectResponse
{
    $user = Socialite::driver('pr0gramm')->user();
    $user = User::updateOrCreate(['pr0gramm_identifier' => $user->user['identifier']], [
        // Sync von pr0gramm-Daten
    ]);
    Auth::login($user, true);
    return Redirect::route('filament.pr0p0ll.pages.dashboard');
}
```

## Versioning

Keine API-Versionierung — `routes/api.php` leer.

## Cross-cutting Middleware

- **CORS** (`config/cors.php`): `'paths' => ['api/*', 'sanctum/csrf-cookie']`. CSRF-Cookie-Endpoint für SPA.
- **CSRF** (`VerifyCsrfToken`): aktiv für alle web-Routes, `$except = []` (keine Ausnahmen).
- **Rate-Limit**: kein `throttle`-Middleware explizit auf Routes. Filament-Login-Page overrides hat eigenen 5-Versuche-Limit. RouteServiceProvider definiert Standard-`api`-Rate-Limiter (60/min), aber api.php nutzt ihn nicht.

## Idempotency

Nicht relevant — keine API-Endpoints, keine REST-Resource-Endpoints.

## "So werden Endpoints gebaut hier" — Exemplar

1. **Public Frontend-Page**:
   - Route in `routes/web.php`: `Route::get('/impressum', [FrontendController::class, 'imprint'])->name('frontend.imprint');`
   - Controller-Action in `app/Http/Controllers/Frontend/FrontendController.php`: `return Inertia::render('Frontend/Imprint', ['imprint' => $markdown]);`
   - Vue-Page in `resources/js/Pages/Frontend/Imprint.vue` mit Layout-Render-Function.

2. **OAuth-Flow**:
   - `Route::get('/oauth/start', ...)` mit `guest`-Middleware → `Pr0authController::start()` → `Socialite::driver('pr0gramm')->redirect()`.
   - 302 zu pr0gramm-Auth-Server.
   - Callback an `/oauth/callback` → User-Sync + `Auth::login()` → Redirect zu Filament-Dashboard.

3. **Filament-CRUD** (Admin):
   - Auto-Route `/pr0p0ll/{resource-slug}/create` durch Resource-Provider.
   - Filament-Form-Builder validiert.
   - Action erzeugt Record, Toast-Notification.

Relevante Files:
- `routes/web.php`, `routes/api.php`
- `app/Http/Controllers/Frontend/FrontendController.php`
- `app/Http/Controllers/Pr0authController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Providers/Filament/Pr0p0llPanelProvider.php`
- `config/sanctum.php`, `config/cors.php`

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/data-flow.md` — die Wire-Boundary des Request-Flows
- `01-architecture/entry-points.md` — HTTP-Entry-Points
- `02-conventions/data-and-types.md` — Request/Response-Types
- `02-conventions/error-handling.md` — wie Errors zur Wire kommen

<!-- research:cross-refs-end -->
