---
source: conventions-scout-testing
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Testing

## Wie Tests laufen

Test-Runner: **Pest 2.34** mit Laravel-Plugin auf Top von PHPUnit 10. Aufruf direkt: `./vendor/bin/pest` oder `composer test` (Skript nicht in `composer.json` definiert — eigentlich Lücke). Browser-Tests separat via `php artisan dusk`.

Layout:

```
tests/
├── Browser/             Dusk-Tests (Chrome-Automation)
├── Feature/             HTTP-Tests gegen App-Routes
├── Unit/                Model + Service-Logik isoliert
├── CreatesApplication.php
├── DuskTestCase.php
├── Pest.php             Pest-Konfiguration (uses() für Test-Hierarchie)
└── TestCase.php         Base
```

`phpunit.xml` definiert Suites für Unit/Feature und Coverage-Scope (`<source><include><directory>app</directory></include></source>`), keine Threshold-Konfiguration.

## Test-Typen

- **Feature** (~2 Files) — HTTP-Calls gegen Routes. Beispiel: `tests/Feature/PagesAvailability/FrontendTest.php` prüft Landing/Imprint/Privacy/Terms.
- **Unit** (~1 File) — Model-Methoden + Service-Layer.
- **Browser/Dusk** (~4 Files) — UI-Workflows via Headless-Chrome (`tests/Browser/PollCreationTest.php`, `DashboardTest.php`, `LoginTest.php`).
- **Filament-Tests**: **keine vorhanden** (kein `tests/Feature/Filament/`-Verzeichnis). Filament-Resources laufen ungetestet.
- **Job-Tests** mit `Queue::fake()`/`Bus::fake()`: **keine vorhanden**.
- **OAuth-Tests** mit `Socialite::shouldReceive()`: **keine vorhanden**.

## Test-Struktur

Pest-`it()`-Makro für Feature/Unit:

```php
// tests/Feature/PagesAvailability/FrontendTest.php:5-8
it('has landingpage page', function () {
    $response = $this->get(route('frontend.landing'));
    $response->assertStatus(200);
});
```

Browser-Tests via `test()` + `$this->browse(Browser $browser)`-Closure:

```php
// tests/Browser/PollCreationTest.php:7-29
test('poll creation works', function () {
    Artisan::call('db:seed');
    $this->browse(function (Browser $browser) {
        $browser->loginAs(\App\Models\User::first());
        $browser->visit('/pr0p0ll/my-polls');
        // ... type, press, assertSee
    });
});
```

`tests/Pest.php` appliziert Trait-Bindung automatisch:
- Feature/Unit: `RefreshDatabase` + `TestCase`-Base.
- Browser: `DuskTestCase` + `DatabaseMigrations`.

## Fixtures und Factories

Factories in `database/factories/`. UserFactory nutzt Faker + Enum-Cases:

```php
// database/factories/UserFactory.php:29-45
return [
    'name' => fake()->name(),
    'email' => fake()->unique()->safeEmail(),
    'email_verified_at' => now(),
    'password' => static::$password ??= Hash::make('password'),
    'pr0gramm_identifier' => fake()->userName(),
    'nationality' => Nationality::cases()[...],
    'region' => Region::cases()[...],
    'gender' => Gender::cases()[...],
    'birthday' => fake()->dateTimeBetween('-100 years', '-18 years'),
];
```

Seeders: `database/seeders/`. Browser-Tests nutzen `Artisan::call('db:seed')` statt expliziter Factories.

## Mocking

Tests nutzen primär **Seeded DB** statt Mocks. Keine `Notification::fake()`, `Queue::fake()`, `Http::fake()`, `Mail::fake()` im Code gefunden. DuskTestCase (`tests/DuskTestCase.php`) konfiguriert Chrome-Driver mit Headless-Options.

## Assertions

PHPUnit-Style innerhalb Pest:
- `$response->assertStatus(200)` — Feature
- `$this->assertIsArray()` — Unit
- `$browser->assertSee()`, `->assertPathIs()`, `->assertSeeLink()` — Dusk

Pest's `expect()`-Style nicht primär genutzt. Keine custom Matcher.

## Coverage

`phpunit.xml` definiert Coverage-Scope (`app/`), keine Threshold. `--coverage`-Flag müsste manuell gesetzt werden. Kein CI-Coverage-Reporting aus den Repo-Files erkennbar.

## "So werden Tests hier geschrieben" — Exemplar

- `tests/Feature/PagesAvailability/FrontendTest.php` — minimaler Smoke-Test pro Route.
- `tests/Browser/PollCreationTest.php` — kompletter UI-Workflow mit Seed, LoginAs, Form-Filling.
- `tests/Unit/Test.php` — Model-Methode + Seed + Assertion.

## Lücken (für Awareness)

- Filament-Resources ungetestet.
- Job-Logik (Notification-Versand-Guards) ungetestet — Queue::fake() würde sich anbieten.
- OAuth-Flow ungetestet — Socialite::shouldReceive() würde sich anbieten.
- HandleInertiaRequests::share() (Auth-Leak) hat keinen Regressions-Test.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/exemplars.md` — kanonische Test-Files
- `02-conventions/data-and-types.md` — Test-Data-Shapes (Factories, Casts)

<!-- research:cross-refs-end -->
