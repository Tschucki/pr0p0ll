---
source: dependency-usage-scout-pr0gramm-socialite
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# socialiteproviders/pr0gramm (^5.0) — Nutzung

`socialiteproviders/pr0gramm` zieht `laravel/socialite ^5.5` über `socialiteproviders/manager ^4.4` transitiv. Das Package wird vom Repo-Owner (Marcel Wagner) selbst gepflegt — Bus-Faktor 1, aber vertraut.

## Wo es genutzt wird

- `app/Http/Controllers/Pr0authController.php` — einziger Login-Controller (start + callback).
- `app/Http/Controllers/LoginRedirectController.php` — fängt default-Laravel-`/login`-Route ab.
- `app/Filament/Pages/Login.php` — overridet Filament-default-Login-Page.
- `app/Providers/EventServiceProvider.php` — registriert pr0gramm-Provider via `SocialiteWasCalled`-Listener.
- `config/services.php` — pr0gramm-Block mit Client-ID/Secret/Redirect.
- `app/Models/User.php` — `pr0gramm_identifier`-Spalte, `getPr0grammName()`-Methode.

## Top-APIs

- `Socialite::driver('pr0gramm')->redirect()` — leitet zu pr0gramm-Auth.
- `Socialite::driver('pr0gramm')->user()` — holt User aus Callback.
- `Event::listen([SocialiteWasCalled::class, [Pr0grammExtendSocialite::class, 'handle']])` — Provider-Registrierung.

Aus dem User-Object via `getId()` (= pr0gramm-Identifier) und `user['identifier']` / `user['name']` Daten lesen.

## Patterns

**Login-Init** in `Pr0authController::start()`:

```php
public function start()
{
    if (Auth::check()) {
        return redirect()->route('filament.pr0p0ll.pages.dashboard');
    }
    return Socialite::driver('pr0gramm')->redirect();
}
```

⚠️ `Auth::check()`-Branch ist **unerreichbar** — Route hat `guest`-Middleware, authentifizierte User werden vorher umgeleitet.

**Callback** mit inline User-Sync:

```php
public function callback(): RedirectResponse
{
    $user = Socialite::driver('pr0gramm')->user();
    $user = User::updateOrCreate([
        'pr0gramm_identifier' => $user->user['identifier'],
    ], [
        'name' => $user->user['name'],
        'password' => Hash::make(Str::random()),  // ⚠️ rotiert bei jedem Login
    ]);
    Auth::login($user, true);
    return Redirect::route('filament.pr0p0ll.pages.dashboard');
}
```

Kein try/catch um `->user()` — OAuth-Fehler landen direkt auf Default-Exception-Handler.

**Filament-Login-Override**:

```php
// app/Filament/Pages/Login.php
class Login extends \Filament\Pages\Auth\Login
{
    public function authenticate(): ?LoginResponse
    {
        if ($this->rateLimitExceeded()) {
            Notification::make()->title('Zu viele Versuche')->danger()->send();
            return null;
        }
        return redirect()->route('oauth.start');
    }
}
```

5-Retry-Limit eingebaut, Default-Filament-Form abgefangen, redirect zu OAuth-Start.

## Wrapper / Adapter (User-Sync)

User-Sync ist **inline** in `Pr0authController::callback()` — kein dedizierter Service. `updateOrCreate` keyed auf `pr0gramm_identifier`. Bei jedem Login wird Password gerollt (`Hash::make(Str::random())`) — verhindert klassisches Password-Login, ist aber auch unnötig wenn OAuth-only.

`banInfo` aus pr0gramm-Response wird **nicht ausgewertet** — gebannte pr0gramm-User können sich trotzdem einloggen (lokales Ban-System läuft separat über cybercog/laravel-ban).

## Konfiguration

`config/services.php`:

```php
'pr0gramm' => [
    'client_id' => env('PR0GRAMM_CLIENT_ID'),
    'client_secret' => env('PR0GRAMM_CLIENT_SECRET'),
    'redirect' => env('PR0GRAMM_REDIRECT_URI'),
    'username' => env('PR0GRAMM_USERNAME'),
    'password' => env('PR0GRAMM_PASSWORD'),
],
```

Provider-Registrierung in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    SocialiteWasCalled::class => [
        Pr0grammExtendSocialite::class . '@handle',
    ],
];
```

`Pr0grammExtendSocialite` kommt aus dem Package und macht die eigentliche Driver-Registrierung beim Socialite-Manager.

Routes:

```php
Route::middleware(['guest'])->group(function () {
    Route::get('/oauth/callback', [Pr0authController::class, 'callback'])->name('oauth.callback');
    Route::get('/oauth/start', [Pr0authController::class, 'start'])->name('oauth.start');
    Route::get('login', LoginRedirectController::class)->name('login');
});
```

## Beobachtete Pitfalls

- ⚠️ **`.env` committed Dev-Client-Secret** — bei Production-Deployment rotieren.
- **Password-Rotation bei jedem Login** — unnötig wenn OAuth-only.
- **`banInfo` ignoriert** — pr0gramm-gebannte User können sich einloggen.
- **Kein try/catch um `Socialite::driver()->user()`** — Provider-Errors landen auf Default-Handler.
- **`oauth.start` mit `guest`-Middleware** macht den `Auth::check()`-Branch unerreichbar.
- **Keine OAuth-Tests** — `Socialite::shouldReceive()` würde Flow durchspielen.

## Test-Strategie

Keine OAuth-Tests im Repo. Pattern dafür:

```php
// tests/Feature/Auth/OAuthTest.php (existiert nicht)
$socialiteUser = (new SocialiteUser)
    ->map(['id' => '123', 'name' => 'tschucki']);

Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

$this->get('/oauth/callback')
    ->assertRedirect(route('filament.pr0p0ll.pages.dashboard'));

$this->assertDatabaseHas('users', ['pr0gramm_identifier' => '123']);
```

## Version-Pin-Notes

`^5.0` Caret. Provider-Package läuft auf socialiteproviders/manager 4.x. Sehr stabil — selten Updates nötig wenn pr0gramm-OAuth-API stabil bleibt.

## "So nutzt man pr0gramm-OAuth in diesem Repo" — Playbook

1. **Login-Trigger**: `<a href="/login">` oder `route('oauth.start')` (gleiche URL).
2. **Server-Flow**:
   - `/login` → `LoginRedirectController` → redirect zu `/oauth/start`.
   - `/oauth/start` → `Socialite::driver('pr0gramm')->redirect()`.
   - pr0gramm-Server → callback an `/oauth/callback`.
   - `Pr0authController::callback()` syncronisiert User per `pr0gramm_identifier`.
   - `Auth::login($user, true)` + redirect zu Filament-Dashboard.
3. **Filament-Login** ist überschrieben — Form führt zu OAuth statt eigener Authentifizierung.
4. **Bei neuem User-Feld aus pr0gramm-Response**: in `Pr0authController::callback()` Mapping erweitern, ggf. neue Migration.
5. **Bei Provider-Konfig-Wechsel**: `config/services.php` + Env-Vars + redirect-URI in pr0gramm-Dev-Console aktualisieren.

Relevante Files:
- `app/Http/Controllers/Pr0authController.php`
- `app/Http/Controllers/LoginRedirectController.php`
- `app/Filament/Pages/Login.php`
- `app/Providers/EventServiceProvider.php`
- `config/services.php`
- `app/Models/User.php`
- `routes/web.php`

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `03-dependencies/README.md` — Sozialites Platz im Stack
- `01-architecture/exemplars.md` — Pr0authController als Login-Exemplar
- `02-conventions/api-and-routing.md` — OAuth-Routes + Middleware-Stack

<!-- research:cross-refs-end -->
