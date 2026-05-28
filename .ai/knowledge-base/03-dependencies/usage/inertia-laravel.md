---
source: dependency-usage-scout-inertia
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# inertiajs/inertia-laravel (^0.6.11) + @inertiajs/vue3 (^1.0.14) — Nutzung

## Wo es genutzt wird

**Server**:
- `app/Http/Controllers/Frontend/FrontendController.php` — 4 Routen: landing, imprint, privacy, terms.
- `app/Http/Middleware/HandleInertiaRequests.php` — Shared-Props.

Sehr minimaler Footprint. Inertia bedient ausschließlich öffentliche Marketing-Seiten. Admin-Bereich läuft über Filament/Livewire (komplett separat).

**Client**:
- `resources/js/app.js` — Bootstrap.
- `resources/js/ssr.js` — SSR-Entry.
- `resources/js/Layouts/Layout.vue` — Layout mit `<Head>`-Component.
- `resources/js/components/Header.vue`, `FooterElement.vue` — Link-Components.
- `resources/js/Pages/Frontend/{Landing,Imprint,Privacy,Terms}.vue` — vier Pages.

**Root-Template**: `resources/views/app.blade.php` mit `@routes`, `@vite('resources/js/app.js')`, `@inertiaHead`, `@inertia`.

**Routes**: `routes/web.php` Z.10-13 mappt `/`, `/impressum`, `/datenschutz`, `/nutzungsbedingungen` auf Controller-Actions.

## Top-APIs (Server)

`Inertia::render($page, $props)` — einzige genutzte Server-Methode:

```php
return Inertia::render('Frontend/Landing', [
    'userCount' => (string) Number::format(number: User::count(), precision: 0, locale: 'de'),
    'pollCount' => (string) Number::format(number: Poll::where('approved', true)->count(), precision: 0, locale: 'de'),
]);
```

`Inertia\Response` als Return-Type-Hint. `Inertia::location()`, `Inertia::share()` (statisch), `Inertia::lazy()` — **nicht genutzt**. Sharing läuft ausschließlich über die Middleware-Methode.

## Top-APIs (Client)

- `createInertiaApp({ resolve, setup })` — in `app.js` und `ssr.js`.
- `<Head>` (aliased `HeadComponent`):
  ```vue
  <HeadComponent>
    <title>{{ title }}</title>
    <meta name="description" :content="description" />
  </HeadComponent>
  ```
- `<Link>`:
  ```vue
  <Link class="transition hover:text-[#f2f5f4]" href="/impressum">Impressum</Link>
  ```
  `href` hardcoded, **kein `route()`-Helper** auf Client-Seite. Ziggy ist über `@routes` verfügbar, wird aber nicht aufgerufen.
- `createServer` (SSR-Bootstrap) in `ssr.js`.
- `usePage()`, `useForm()`, `router.*` — **nicht genutzt**. Pages nutzen `$page.props` aus Options-API.

## Patterns

**Persistent-Layout via Render-Function** (klassisches Vue-2-Style-Pattern, ohne `defineOptions`):

```vue
<!-- Pages/Frontend/Landing.vue -->
<script>
export default {
  name: 'Landing',
  layout: (h, page) => h(Layout, { title: '...', description: '...' }, () => page),
  props: { userCount: String, pollCount: String },
};
</script>
```

Props in Page-Components als Vue-Options-API-`props` deklariert. Layout/Components nutzen `<script setup>` + `defineProps` — **Stil-Mix** im Repo.

Markdown-Inhalte werden serverseitig via `Str::markdown()` aus `resources/markdown/*.md` gerendert und als String-Prop durchgereicht. Kein client-seitiges Markdown-Parsing.

## Wrapper / Adapter

`app/Http/Middleware/HandleInertiaRequests.php` ist nahezu Stock. Einzige Anpassung in `share()`:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => ['user' => \Auth::user()?->toArray()],
    ]);
}
```

Registriert in `app/Http/Kernel.php` Z.41 in der `web`-Middleware-Group (als letztes Element).

⚠️ **Sicherheits-Issue**: `\Auth::user()?->toArray()` leakt das **gesamte User-Model inkl. aller Spalten** an alle Inertia-Pages. `$hidden` auf User-Model filtert die genannten Felder, aber Konzept ist Layer-Bruch — Middleware sollte DTO bauen, nicht Model durchreichen.

## Konfiguration

Kein `config/inertia.php` publiziert (Standard-Defaults aktiv).

Client-Boot:

```js
// resources/js/app.js
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { MotionPlugin } from '@vueuse/motion';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) }).use(plugin).use(MotionPlugin).mount(el);
  },
});
```

`eager: true` lädt **alle Pages synchron** in den Initial-Bundle. Bei aktuell 4 Pages unkritisch, würde aber bei Wachstum problematisch.

SSR-Pendant `resources/js/ssr.js` nutzt `createSSRApp`, `renderToString` aus `@vue/server-renderer`, `createServer` aus `@inertiajs/vue3/server`.

Vite-Config:
```js
// vite.config.js
ssr: 'resources/js/ssr.js'
```
Build:
```json
"build": "vite build && vite build --ssr"
```

## Beobachtete Pitfalls

- **User-Leak in `share()`** — ganzes Model serialisiert.
- **Kein Ziggy auf JS-Seite** — `@routes` ist gerendert, aber `route()` wird im JS nicht aufgerufen. `<Link href="/impressum">` ist hardcoded. URL-Änderungen brechen Links.
- **`eager: true`** im Glob lädt alle Pages in den Initial-Chunk.
- **Vermischte Vue-Stile** — Pages: Options-API; Layout/Components: `<script setup>`. Inkonsistent.
- **Server-Client-Version-Drift** — `^0.6.11` vs `^1.0.14`. Funktioniert dank Abwärtskompatibilität, riskant beim Upgrade.
- **Keine `assertInertia()`-Tests** — siehe Test-Strategie unten.

## Test-Strategie

**Keine `assertInertia()`-Calls** im gesamten `tests/`-Tree. Pest-Feature-Test prüft nur HTTP-Status:

```php
// tests/Feature/PagesAvailability/FrontendTest.php:5-8
it('has landingpage page', function () {
    $response = $this->get(route('frontend.landing'));
    $response->assertStatus(200);
});
```

Kein Page-Component- oder Prop-Assertion. Browser-Tests (Dusk) targeten Filament-Bereich, nicht Inertia.

Empfehlung: `Inertia\Testing\AssertableInertia` einführen:

```php
$response->assertInertia(fn ($page) =>
    $page->component('Frontend/Landing')->has('userCount')
);
```

## Version-Pin-Notes

- **`inertiajs/inertia-laravel ^0.6.11`** (Pre-1.0). 1.0 bringt SSR-Verbesserungen + `defer`/`merge` Props, 2.0 komplett neue Polling/History-API. Sicherer Upgrade-Pfad: 0.6 → 1.x, dann Client-Synchronisation.
- **`@inertiajs/vue3 ^1.0.14`** — 1.x-Linie, `^` erlaubt Patches. Client liegt vor Server (1.0 vs 0.6). Funktioniert, langfristig Drift.
- **`@vue/server-renderer ^3.4.15`** und **`vue ^3.4.15`** Lockstep — Pflicht für SSR.
- **`laravel-vite-plugin ^1.0.0`** — SSR-Build-Pipeline; Wechsel auf 2.x verlangt Vite 6.

## "So nutzt man Inertia in diesem Repo" — Playbook

1. **Controller-Action** in `app/Http/Controllers/Frontend/*` (oder neuer Namespace). Return-Type `Inertia\Response`. Body: `Inertia::render('Frontend/<Name>', [...props])`.
2. **Vue-Page** unter `resources/js/Pages/Frontend/<Name>.vue` mit Options-API + `layout: (h, page) => h(Layout, { title, description }, () => page)` für Persistent-Layout.
3. **Props** als `props: { foo: String, ... }`. Number-Werte schon im Controller via `Number::format(..., locale: 'de')` zu Strings casten (bestehendes Pattern).
4. **Route** in `routes/web.php` mit `->name('frontend.<name>')`. Bei Auth-Bedarf in `middleware(['guest'])`-Gruppe.
5. **Test** in `tests/Feature/PagesAvailability/FrontendTest.php` via `$this->get(route(...))->assertStatus(200)`. Für Prop-Verifikation Upgrade auf `assertInertia()` empfohlen.

Relevante Files:
- `app/Http/Controllers/Frontend/FrontendController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Kernel.php`
- `resources/js/app.js`, `resources/js/ssr.js`
- `resources/js/Layouts/Layout.vue`
- `resources/views/app.blade.php`
- `vite.config.js`
- `routes/web.php`

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `03-dependencies/README.md` — Inertias Platz im Stack
- `01-architecture/exemplars.md` — Inertia-Page-Pattern
- `02-conventions/state-management.md` — Inertia $page.props als Shared-State
- `02-conventions/api-and-routing.md` — Inertia-Render im Routing-Layer

<!-- research:cross-refs-end -->
