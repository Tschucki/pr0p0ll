# Pr0p0ll — Claude-Lead

Umfrage-Plattform für pr0gramm.com-Community. Laravel 10 + Filament 3 (Admin) + Inertia/Vue 3 (Public).

**Erst lesen vor jeder Arbeit**: [`.ai/knowledge-base/README.md`](.ai/knowledge-base/README.md) — Index aller Repo-Wissens-Files. KB ist projekt-spezifisch und überstimmt allgemeines Laravel-Wissen wo sie sich widerspricht.

## Sprache

Antworten und Code-Kommentare auf Deutsch. Code-Identifier, Tool-Namen, Lib-Namen bleiben kanonisch englisch. Volle Orthographie mit Umlauten/ß.

## Stack-Kern

- PHP 8.2 + Laravel 10.10
- Filament 3 (Admin-Panel unter `/pr0p0ll`, Livewire 3 intern)
- Inertia 0.6 (Server) + Vue 3 / Inertia 1.0 (Client) für 4 Marketing-Pages
- Vite 5 mit SSR (`vite build --ssr`), Tailwind 3
- Horizon (Redis-Queues) + Pulse + Nightwatch + Plausible
- Auth: socialiteproviders/pr0gramm — OAuth-only, kein Register
- Tests: Pest 2 + PHPUnit 10 + Dusk 8

## Wichtige Konventionen (Quelle: `.ai/knowledge-base/02-conventions/`)

- **Pint preset `laravel`** mit `declare_strict_types: true` erzwungen. Header in jeder PHP-Datei.
- **Naming**: `camelCase` Methoden, `PascalCase` Klassen. Eigenheit: Array-Vars Präfix `$a` (z.B. `$aBuilderData`).
- **Keine FormRequest-Klassen** — Validation inline `Validator::make()` + Filament-Form-Rules.
- **Jobs uniform**: `$tries = 15; $backoff = 120;` (siehe `.ai/knowledge-base/03-dependencies/usage/horizon.md`).
- **Filament-Resources direkt** ohne Base-Resource. Deutsche `$label` / `$pluralLabel` / `->label(...)`.
- **Inertia-Pages**: Options-API + Layout-Render-Function. Layout/Components: `<script setup>`. Stil-Mix bewusst.
- **JSON-Spalten** für `target_group`, `options`, `blocks`. ID = autoincrement BigInt.
- **Carbon** für Time. `ClosesAfter`-Enum-String direkt addierbar (`now()->add($poll->closes_after)`).
- **Notifications** vier Channels: mail, discord, telegram, pr0gramm. Routing über `User::wantsNotification(Channel, Type)`.

## Architektur-Pointer

- Workflow + Notification-Dispatch: `app/Models/Abstracts/Poll.php::approve()/deny()/disable()`.
- Filament-Panel-Truth: `app/Providers/Filament/Pr0p0llPanelProvider.php`.
- OAuth-Flow: `app/Http/Controllers/Pr0authController.php` + `app/Filament/Pages/Login.php`.
- Inertia-Middleware-Share: `app/Http/Middleware/HandleInertiaRequests.php` (⚠️ Auth-Leak — siehe Gaps).
- Scheduler: `app/Console/Kernel.php` (`app:login-to-pr0gramm` hourly, `ban:delete-expired` everyMinute).

Komplette Daten-Flow-Diagramme + Entry-Points: [`.ai/knowledge-base/01-architecture/`](.ai/knowledge-base/01-architecture/).

## Bekannte Gaps (zur Awareness)

Quelle: [`.ai/knowledge-base/00-overview.md#health-signale`](.ai/knowledge-base/00-overview.md).

1. **Auth-Leak**: `HandleInertiaRequests::share()` schickt komplettes User-Model an Frontend (statt DTO).
2. **Test-Lücken**: Filament-Resources, OAuth-Flow, Jobs komplett ungetestet. Kein `Queue::fake()` / `Socialite::shouldReceive()` im Code.
3. **axios 1.15.0** in `package-lock.json` mit 5 CVEs (HIGH GHSA-pmwg-cvhr-8vh7) — Update auf 1.16.1.
4. **Transitive Vulns**: phpseclib (CVE-2026-44167), symfony/html-sanitizer (CVE-2026-48761, CVE-2026-48760).
5. **`.env` mit Dev-Client-Secret committed** — bei Production-Deployment rotieren.
6. **Single Redis-Queue** `default` — Telegram/Discord-Stalls können Mail-Pipeline blockieren. Queue-Trennung sinnvoll.
7. **11 Job-Klassen mit Boilerplate** — Base-Job-Trait würde reduzieren.
8. **Kein Sentry/Bugsnag** — Production-Errors landen nur in `storage/logs/laravel.log`.
9. **`Horizon::routeMailNotificationsTo`** auskommentiert in `HorizonServiceProvider` — keine Failed-Job-Alerts.

## Häufige Aufgaben

- **Neue Filament-Resource**: Playbook in [`.ai/knowledge-base/03-dependencies/usage/filament.md`](.ai/knowledge-base/03-dependencies/usage/filament.md).
- **Neuer Queue-Job**: Playbook in [`.ai/knowledge-base/03-dependencies/usage/horizon.md`](.ai/knowledge-base/03-dependencies/usage/horizon.md). Konvention: `tries=15`, `backoff=120`.
- **Neue Inertia-Page**: Playbook in [`.ai/knowledge-base/03-dependencies/usage/inertia-laravel.md`](.ai/knowledge-base/03-dependencies/usage/inertia-laravel.md).
- **OAuth-Field-Mapping ändern**: [`.ai/knowledge-base/03-dependencies/usage/pr0gramm-socialite.md`](.ai/knowledge-base/03-dependencies/usage/pr0gramm-socialite.md).
- **Domain-Entities verstehen**: [`.ai/knowledge-base/06-domain/entities.md`](.ai/knowledge-base/06-domain/entities.md) + [`business-rules.md`](.ai/knowledge-base/06-domain/business-rules.md).

## Befehle

```bash
# PHP
composer install
./vendor/bin/pint                 # Formatter
./vendor/bin/pest                 # Tests
./vendor/bin/pest --coverage      # mit Coverage
php artisan dusk                  # Browser-Tests
php artisan horizon               # Queue-Daemon (lokal)
php artisan migrate               # DB-Migrations
php artisan db:seed               # Seeder
php artisan tinker                # REPL

# JS
npm install
npm run dev                       # Vite-Dev-Server
npm run build                     # Client + SSR-Build
npm run lint                      # ESLint --fix
npm run prettier                  # Prettier --write
```

## Workflow-Konvention

- Branch: `main` ist Default. PR-Workflow via GitHub (`Tschucki/pr0p0ll`). Deployment via Forge nach Merge.
- Commits: knapp, häufig nur Verb + Kontext ("fix issue with yes_no fields"). Konventionelle Commits nicht erzwungen. **Commit-Messages immer auf Englisch.**
- Vor PR: `pint`, `pest`, `npm run lint`.

## Aktionsregeln für Claude

- **Niemals** sensible Werte (Tokens, Passwords, Client-Secrets) ausdrucken oder in Logs/Files schreiben.
- **Niemals** `.env` editieren oder committen.
- **Bei Migration**: Schema-Konventionen aus [`.ai/knowledge-base/02-conventions/data-and-types.md`](.ai/knowledge-base/02-conventions/data-and-types.md) befolgen (`foreignId()->constrained()->cascadeOnDelete()` etc.).
- **Bei neuem Notification-Job**: Konvention aus [`async-and-concurrency.md`](.ai/knowledge-base/02-conventions/async-and-concurrency.md) übernehmen. Bei Bulk-Versand: nicht Loop ohne Bus::batch — eventuell Batch nutzen.
- **Bei Inertia-Page-Änderung**: prüfen ob HandleInertiaRequests::share() betroffen ist (Auth-Leak).
- **Vor Tests-Vorschlag**: KB-Testing-Section lesen — Pest-Style + Setup-Trait via `tests/Pest.php`.
- **Code-Review-Lens**: bei neuem Code auf Style aus [`code-style.md`](.ai/knowledge-base/02-conventions/code-style.md) achten (strict_types, Array-`$a`-Präfix, Early-Return, Type-Hints).

## KB-Updates

Falls Repo-Struktur sich signifikant ändert: `/research` neu laufen lassen. Skill liest `.ai/knowledge-base/.research.json` und macht Diff statt clean-Schreiben. Korrekturen aus früheren Runs werden auf Scouts prepended.

## Schnelle Orientierung beim Reinkommen

1. `.ai/knowledge-base/00-overview.md` (Ein-Seiten-Zusammenfassung).
2. `.ai/knowledge-base/01-architecture/data-flow.md` (Poll-Approval end-to-end).
3. `.ai/knowledge-base/06-domain/README.md` (was die Plattform tut).
4. Bei Filament-Arbeit: `.ai/knowledge-base/03-dependencies/usage/filament.md`.
5. Bei OAuth-Arbeit: `.ai/knowledge-base/03-dependencies/usage/pr0gramm-socialite.md`.

---

Lizenz: AGPL-3.0. Maintainer: [Tschucki](https://github.com/Tschucki). Quelle: <https://github.com/Tschucki/pr0p0ll>.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.30
- filament/filament (FILAMENT) - v3
- inertiajs/inertia-laravel (INERTIA) - v0
- laravel/framework (LARAVEL) - v10
- laravel/horizon (HORIZON) - v5
- laravel/nightwatch (NIGHTWATCH) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/sanctum (SANCTUM) - v3
- laravel/socialite (SOCIALITE) - v5
- livewire/livewire (LIVEWIRE) - v3
- tightenco/ziggy (ZIGGY) - v1
- laravel/dusk (DUSK) - v8
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v2
- phpunit/phpunit (PHPUNIT) - v10
- @inertiajs/vue3 (INERTIA) - v1
- vue (VUE) - v3
- eslint (ESLINT) - v8
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use strict typing at the head of a `.php` file: `declare(strict_types=1);`.
- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

## Inertia

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (`vite.config.js`).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use the `search-docs` tool for accurate guidance on all things Inertia.

<code-snippet name="Inertia Render Example" lang="php">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v10 rules ===

## Laravel 10

- Use the `search-docs` tool to get version-specific documentation.
- Middleware typically live in `app/Http/Middleware/` and service providers in `app/Providers/`.
- Laravel 10 has a `bootstrap/app.php` file that creates the application instance and binds kernel contracts, but does not use it for application configuration like Laravel 11:
    - Middleware registration is in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule registration is in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`
- When using Eloquent model casts, you must use `protected $casts = [];` and not the `casts()` method. The `casts()` method isn't available on models in Laravel 10.

=== livewire/core rules ===

## Livewire

- Use the `search-docs` tool to find exact version-specific documentation for how to write Livewire and Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle Hook Examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire Component Test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

<code-snippet name="Testing Livewire Component Exists on Page" lang="php">
    $this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
</code-snippet>

=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 3, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire; don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="Livewire Init Hook Example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== inertia-vue/core rules ===

## Inertia + Vue

- Vue components must have a single root element.
- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="vue">

    import { Link } from '@inertiajs/vue3'
    <Link href="/">Home</Link>

</code-snippet>

=== inertia-vue/v1/forms rules ===

## Inertia v1 + Vue Forms

- For form handling in Inertia pages, use `router.post` and related methods. Do not use regular forms.

<code-snippet name="Inertia Vue Form Example" lang="vue">
<script setup>
    import { reactive } from 'vue'
    import { router } from '@inertiajs/vue3'
    import { usePage } from '@inertiajs/vue3'

    const page = usePage()

    const form = reactive({
        first_name: null,
        last_name: null,
        email: null,
    })

    function submit() {
        router.post('/users', form)
    }
</script>

<template>
    <h1>Create {{ page.modelName }}</h1>
    <form @submit.prevent="submit">
        <label for="first_name">First name:</label>
        <input id="first_name" v-model="form.first_name" />
        <label for="last_name">Last name:</label>
        <input id="last_name" v-model="form.last_name" />
        <label for="email">Email:</label>
        <input id="email" v-model="form.email" />
        <button type="submit">Submit</button>
    </form>
</template>
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind CSS

- Use Tailwind CSS classes to style HTML; check and use existing Tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc.).
- Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, and group elements logically.
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing; don't use margins.

<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>

### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v3 rules ===

## Tailwind CSS 3

- Always use Tailwind CSS v3; verify you're using only classes supported by this version.
</laravel-boost-guidelines>

<pr0p0ll-guidelines>
=== pr0p0ll/foundation ===

# Pr0p0ll Project-Specific Guidelines

These guidelines override conflicting Boost defaults for this repo. They are derived from `.ai/knowledge-base/`. If a Boost rule contradicts a Pr0p0ll rule below, follow Pr0p0ll. If unsure: read `.ai/knowledge-base/02-conventions/`.

## Source-of-Truth

- KB-Index: `.ai/knowledge-base/README.md`.
- Conventions per topic: `.ai/knowledge-base/02-conventions/*.md`.
- Architecture exemplars: `.ai/knowledge-base/01-architecture/exemplars.md`.
- Per-Dependency-Usage: `.ai/knowledge-base/03-dependencies/usage/{filament,inertia-laravel,horizon,pr0gramm-socialite}.md`.
- Domain rules: `.ai/knowledge-base/06-domain/business-rules.md`.

=== pr0p0ll/language ===

## Output Language

- Prose responses, status messages, and explanations: **German with full umlauts and ß**.
- Code identifiers, file paths, tool names, MCP server names, library names, framework names: **canonical English**.
- User-facing strings inside the codebase (Filament Notifications, error messages, etc.): **German hardcoded**. The repo does not use lang/de/ for these strings; do not introduce i18n without explicit approval.

=== pr0p0ll/php-style ===

## PHP Style — Hard Rules

- `declare(strict_types=1);` is the first content of every PHP file. Enforced by `pint.json` rule `declare_strict_types: true`.
- Pint preset is `laravel` with overrides: `simplified_null_return: true`, `braces: false`, `new_with_braces: {anonymous_class: false, named_class: false}`.
- Method naming: `camelCase`. Class naming: `PascalCase`.
- **Repo idiom**: array variables use the `$a` prefix (e.g. `$aBuilderData`, `$aTargetGroupData`). Preserve this when editing existing arrays; do not strip it.
- All public methods have explicit return-type-hints. Eloquent relations are fully typed to `\Illuminate\Database\Eloquent\Relations\HasMany` etc.
- Prefer early-return guards over nested conditionals.
- Minimize DocBlocks — only add when type-inference cannot convey intent.

=== pr0p0ll/validation ===

## Validation Convention

- The repo **does not use FormRequest classes**. All validation runs either via Filament Form-Builder rules or inline `Validator::make($data, [...])->validated()` in Page-Handlers.
- Do not introduce FormRequest classes without explicit user approval — this would be a convention break.
- Inline-validation pattern:
  ```php
  try {
      $validated = Validator::make($input, [...])->validated();
      // do work
  } catch (\Illuminate\Validation\ValidationException $e) {
      Notification::make()->title('Komisch. ...')->danger()->send();
  }
  ```

=== pr0p0ll/filament ===

## Filament 3 — Hard Rules

- Direct `extends \Filament\Resources\Resource` — **no Base-Resource or Trait** in this repo. Do not introduce one without approval.
- German labels everywhere: `$label`, `$pluralLabel`, `->label('...')`. German URL slugs via `$slug` (e.g. `'umfragen'`, `'teilnehmen'`).
- Navigation in one of two groups: `'Administration'` (admin-only) or `'Umfragen'` (user-facing). Icon prefix: `heroicon-o-*`.
- Table-column pattern is uniform across resources:
  ```php
  TextColumn::make('title')->label('Titel')->sortable()->searchable()->toggleable()
  TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')
  ```
- Bulk-actions always `BulkActionGroup` containing `DeleteBulkAction`.
- Pagination: `->paginated([10, 25, 50])`.
- Admin-only access via `public static function canAccess(): bool { return \Auth::user()->isAdmin(); }`.
- User feedback after actions: `Filament\Notifications\Notification::make()->title('...')->body('...')->send()` with German strings.
- Panel-config truth is `app/Providers/Filament/Pr0p0llPanelProvider.php`. Do not publish or edit `config/filament.php` — it is not in use.
- Ban-middleware (`\Cog\Laravel\Ban\Http\Middleware\ForbidBannedUser`) is wired at panel level — no per-Resource ban-check needed.

=== pr0p0ll/jobs-queues ===

## Queue Jobs — Hard Rules

- All Jobs implement `ShouldQueue` and use the four standard traits: `Dispatchable, InteractsWithQueue, Queueable, SerializesModels`.
- Retry pattern is uniform across all 11 existing jobs:
  ```php
  public int $tries = 15;
  public int $backoff = 120;
  ```
  Follow this unless you have a documented reason to differ.
- For broadcast-style jobs that should not duplicate: add `implements ShouldBeUnique` (pattern from `SendPollAcceptedTelegramNotification`).
- `handle()` begins with a guard clause (e.g. `if ($this->poll->userIsWithinTargetGroup($this->user) === false) { return; }`).
- Queue connection: `redis`. Default queue: `default`. There is **only one queue in use** — external API stalls (Telegram/Discord) can block the mail pipeline. Awareness: when adding heavy external-API jobs, propose a separate queue.
- `Horizon::routeMailNotificationsTo(...)` and `routeSlackNotificationsTo(...)` are commented out in `HorizonServiceProvider`. There are no failed-job alerts. Flag this when relevant.
- Anti-pattern in repo: `app/Models/Abstracts/Poll.php::approve()` dispatches jobs directly from the model. Do not propagate this layer-break to new dispatch sites without explicit approval — Observer-based dispatch is cleaner (`MyPollObserver` shows the pattern partially).

=== pr0p0ll/inertia ===

## Inertia + Vue 3 — Hard Rules

- Server uses **`inertiajs/inertia-laravel ^0.6.11`** (Pre-1.0). Client uses **`@inertiajs/vue3 ^1.0.14`**. There is a server/client version drift — do not upgrade one without the other.
- Public Inertia surface is exactly 4 marketing pages: `Frontend/Landing`, `Frontend/Imprint`, `Frontend/Privacy`, `Frontend/Terms`. Admin uses Filament/Livewire — disjoint.
- Numeric props are server-formatted to German strings via `Number::format(number: ..., precision: 0, locale: 'de')` before passing to Inertia. Preserve this idiom when adding numeric props.
- Pages use Vue Options-API with a render-function Persistent-Layout:
  ```vue
  <script>
  export default {
    layout: (h, page) => h(Layout, { title: '...', description: '...' }, () => page),
    props: { foo: String },
  };
  </script>
  ```
  Layout and Components use `<script setup>`. **Do not unify the styles** — the mix is intentional.
- `<Link href="...">` with hardcoded path is the repo idiom. Ziggy `route()` is available via `@routes` but is **not used** JS-side. When introducing it: propose explicitly, do not slip it into a PR.
- ⚠️ `app/Http/Middleware/HandleInertiaRequests::share()` leaks the **entire User model** to all Inertia pages via `Auth::user()?->toArray()`. Do **not** edit `share()` without explicit user approval. When new shared state is needed: propose a DTO/Resource layer.
- `eager: true` on the page glob loads all pages synchronously. With more than ~10 pages this becomes problematic — propose `eager: false` + dynamic-import at that scale.
- Test convention is HTTP-status-only in `tests/Feature/PagesAvailability/FrontendTest.php`. Upgrade to `Inertia\Testing\AssertableInertia` when adding meaningful prop assertions.

=== pr0p0ll/oauth ===

## pr0gramm-OAuth — Hard Rules

- Single auth provider: **`socialiteproviders/pr0gramm ^5.0`**, maintained by the repo owner. There is no email/password login. There is no register form.
- Entry-points: `app/Http/Controllers/Pr0authController.php` (`start` + `callback`), `app/Http/Controllers/LoginRedirectController.php` (intercepts default Laravel `/login`), `app/Filament/Pages/Login.php` (overrides Filament default login to redirect to OAuth).
- Provider registration is via `app/Providers/EventServiceProvider.php` `$listen` array — do not move this to `Pr0p0llPanelProvider`.
- Known pitfalls (Awareness; flag when relevant):
  - The `Auth::check()` branch in `Pr0authController::start()` is unreachable (route is in `guest` middleware).
  - Password is rotated on every login via `Hash::make(Str::random())`. Removing password rotation would break the implicit OAuth-only invariant.
  - `banInfo` from the pr0gramm OAuth response is **ignored**. pr0gramm-banned users can log in. The local ban system (cybercog/laravel-ban) is separate.
  - There is **no try/catch** around `Socialite::driver('pr0gramm')->user()`. OAuth provider errors land on the default exception handler.
  - `.env` contains a committed dev client secret. Rotate on production deploy.
- OAuth tests do not exist in the repo. When adding: use `Socialite::shouldReceive('driver->user')->andReturn(...)`-style mocking, place under `tests/Feature/Auth/OAuthTest.php`.

=== pr0p0ll/domain-invariants ===

## Domain Invariants — Hard Rules

- Poll state transitions go through **`approve()`**, **`deny($reason)`**, **`disable($reason)`** on `app/Models/Abstracts/Poll.php`. Never update status fields (`approved`, `in_review`, `visible_to_public`, `published_at`, `closes_at`, `admin_notes`) directly — that bypasses notification dispatch.
- `closes_at` is computed at `approve()` from `published_at + closes_after`. The string value of `ClosesAfter` enum (e.g. `"+1 week"`) is Carbon-addable: `now()->add($poll->closes_after)`.
- "Closed" is **not a stored state** — it is computed via `Poll::isClosed()`. Reporting based on closure must use this method, not a flag.
- Answer unique constraint: `(poll_id, question_id, user_id, anonymous_user_id)` at DB level. Any logic that creates Answers must respect it.
- AnonymousUser is created via `User::createAnonymousUser()` from the calling user's demographics. Do not instantiate `AnonymousUser` directly.
- Demographic-update cooldown: `User::canUpdateDemographicData()` allows updates only when `last_data_change > 2 months ago`. Update `last_data_change` after every change.
- Notification filtering: `User::wantsNotification(Channel, Type)` is the source of truth. Do not bypass it for "important" notifications.
- Target-group filtering: `Poll::userIsWithinTargetGroup(User)` delegates to `TargetGroupService`. Never inline demographics checks.
- Known unfinished invariants (flag when touched):
  - `OWNPOLLHASENDED` and `PARTICIPATEDPOLLHASFINISHED` notification types exist but have no automatic trigger at poll closure. A scheduled job is likely missing.
  - `CREATEPOSTREMINDER` job exists; trigger is unclear from the code.
  - `User::canAccessPanel(Panel)` is currently allow-all with a TODO. Filament access control is not effectively restricted to admin users (only individual `canAccess()` methods on Resources gate admin-only views).

=== pr0p0ll/tests ===

## Tests — Hard Rules

- Test runner: **Pest 2.34** on top of PHPUnit 10. Browser tests via Dusk 8.
- File layout: `tests/{Feature,Unit,Browser}`. `tests/Pest.php` binds traits automatically:
  - Feature/Unit → `RefreshDatabase` + `TestCase`.
  - Browser → `DuskTestCase` + `DatabaseMigrations`.
- Do not write `uses(...)` manually — let `Pest.php` handle it via directory location.
- Run-commands: `./vendor/bin/pest` for Pest/PHPUnit; `php artisan dusk` for Browser tests.
- Browser tests seed via `Artisan::call('db:seed')` rather than factory-direct setup.
- Massive test gaps to flag when relevant: **no Filament tests, no Job tests, no OAuth tests, no `assertInertia()` tests**. When adding tests in these areas: place in `tests/Feature/Filament/`, `tests/Feature/Jobs/`, `tests/Feature/Auth/`, and use the canonical fake helpers (`Notification::fake()`, `Queue::fake()`, `Bus::fake()`, `Http::fake()`, `Socialite::shouldReceive(...)`).

=== pr0p0ll/configuration ===

## Configuration — Hard Rules

- `env()` is called **only** in `config/*.php`. App code reads via `config('xyz.abc')`. Do not break this with direct `env()` calls in application code.
- `config/pr0p0ll.php` carries the hand-rolled beta flag: `'beta_users' => explode(',', env('BETA_USERS', ''))`. No external feature-flag library is in use.
- Do not edit, copy, commit, or print contents of `.env`. The committed `.env` contains a dev secret — handle as sensitive.
- Boot-time config validation is **not implemented**. There is no fail-fast on missing required env vars; OAuth fails on first request when `PR0GRAMM_CLIENT_ID` is missing. Awareness only — do not introduce validation without approval.

=== pr0p0ll/agents ===

## Project Subagents Available

When working on these topics, prefer delegating to the matching subagent (defined under `.claude/agents/`):

- New/modified Filament Resource, Page, Widget, Action, or custom Livewire-3 component inside Filament → `pr0p0ll-filament-author`.
- New Queue Job → `pr0p0ll-job-author`.
- New Inertia/Vue public page → `pr0p0ll-inertia-author`.
- Poll workflow / domain-logic review → `pr0p0ll-domain-expert`.
- OAuth-flow debugging or modification → `pr0p0ll-oauth-debugger`.
- Writing tests (Pest/Dusk/Filament) → `pr0p0ll-test-author`.

Skills (under `.claude/skills/`):

- `pr0p0ll-conventions` — load before writing any code.
- `pr0p0ll-poll-workflow` — load before touching Poll-state transitions or notification dispatch.

=== pr0p0ll/forbidden ===

## Forbidden / Confirm-First Actions

Never do without explicit user approval:

- Edit `.env`, commit `.env`, print `.env` values.
- Edit `HandleInertiaRequests::share()`.
- Introduce a Base-Resource or Trait for Filament Resources.
- Introduce FormRequest classes.
- Bypass `Poll::approve()`/`deny()`/`disable()` for state transitions.
- Change `$tries`/`$backoff` defaults on jobs without documented reason.
- Introduce Sentry/Bugsnag/Rollbar — repo uses Ignition (dev) + daily log (prod). Adding error aggregation is a project-level decision.
- Rotate dependencies marked as version-pinned for compatibility (e.g. `@inertiajs/vue3` ↔ `inertia-laravel`).
- Auto-upgrade Filament v3 → v4 — would break three plugins, the Login subclass, and the Forms/Tables namespace structure.

</pr0p0ll-guidelines>
