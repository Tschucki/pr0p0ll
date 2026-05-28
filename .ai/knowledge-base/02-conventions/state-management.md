---
source: conventions-scout-state-management
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# State-Management

## Local-State

Vue 3 mit Composition-API (`ref`, `reactive`, `computed`) in Components mit `<script setup>`. Pages mischen Options-API (`data()`, `props`). Keine zentrale Store-Library — State bleibt auf Component-Ebene:

```vue
<!-- resources/js/components/Header.vue -->
<a v-if="!$page.props.auth.user?.id" href="/login">
  <Pr0Button> Login mit pr0gramm </Pr0Button>
</a>
```

## Shared-State-Library

**Inertia.js übernimmt Rolle der Shared-State-Layer** — Server liefert Props via `Inertia::render()`, Client liest via `$page.props` (Options-API-Pattern) oder `usePage()` (`usePage()` nicht im aktuellen Code genutzt). Kein Pinia, kein Vuex.

Auth-State global gesharet via `HandleInertiaRequests::share()`:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => ['user' => \Auth::user()?->toArray()],
    ]);
}
```

⚠️ Layer-Bruch + Daten-Leck: gesamtes User-Model statt DTO/Resource. Siehe [`api-and-routing.md`](api-and-routing.md) und [`data-and-types.md`](data-and-types.md).

## Server-State

Inertia handelt Server-State transparent. Form-Submissions würden über `Inertia.post()` oder `router.post()` (aus `@inertiajs/vue3`) laufen — **aktuell nicht im Public-Frontend** (4 Pages sind reine Marketing-Inhalte, ohne Formulare).

OAuth-Login läuft NICHT über Inertia, sondern klassisch über `<a href="/login">` → Server-Redirect zu `/oauth/start` → externer OAuth-Provider:

```vue
<a v-if="!$page.props.auth.user?.id" href="/login">
  <Pr0Button>Login mit pr0gramm</Pr0Button>
</a>
```

## Persistent-State

**Kein Client-Persist** (localStorage/sessionStorage) erkannt. Persistierung läuft serverseitig (Sessions, DB). Inertia bewahrt Scroll-Position über `preserveScroll`-Prop — nicht aktiv genutzt im aktuellen Code.

Auth-Token via CSRF-Cookie (`X-XSRF-TOKEN`), axios konfiguriert via `bootstrap.js`:

```js
// resources/js/bootstrap.js
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

## URL-State

Routes in `routes/web.php` — Inertia bewahrt URLs bei Navigation. Query-Parameter würden über `<Link href="...?foo=bar">` propagiert. Aktuelle Pages nutzen keine Query-Params.

```vue
<!-- resources/js/components/FooterElement.vue -->
<Link class="transition hover:text-[#f2f5f4]" href="/impressum">Impressum</Link>
```

**Achtung**: `href` hardcoded statt `route('frontend.imprint')`. Ziggy (`@routes`-Direktive im Blade-Template) ist verfügbar, wird aber JS-seitig **nicht genutzt**. URL-Änderungen würden Links brechen.

## Form-State

`useForm()` aus `@inertiajs/vue3` **nicht im aktuellen Code**. Public-Frontend hat keine Forms. Filament-Admin nutzt Server-State über Livewire 3 (Filament-intern, ohne Vue-Berührung).

Für zukünftige Inertia-Forms wäre Pattern:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
const form = useForm({ title: '', body: '' });
const submit = () => form.post(route('foo.create'));
</script>
```

## SSR / Hydration

SSR aktiv via Vite. Build-Pipeline:

```bash
# package.json scripts
"build": "vite build && vite build --ssr"
```

`vite.config.js` definiert SSR-Entry `resources/js/ssr.js`:

```js
// resources/js/ssr.js
createServer((page) =>
  createInertiaApp({
    page,
    render: renderToString,
    setup({ App, props, plugin }) {
      return createSSRApp({
        render: () => h(App, props),
      })
        .use(plugin)
        .use(MotionPlugin);
    },
  })
);
```

Client-Hydration via `resources/js/app.js`:

```js
// resources/js/app.js
createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(MotionPlugin)
      .mount(el);
  },
});
```

⚠️ `eager: true` lädt alle Pages synchron in den Initial-Bundle. Aktuell 4 Pages — unkritisch. Bei Wachstum: Code-Splitting via `eager: false` + dynamic-Import.

Page-Props werden serverseitig vorberechnet und in HTML serialisiert (`@inertia`-Direktive im Blade-Template). Inertia handelt Hydration ohne manuellen State-Transfer.

## "So ist State strukturiert hier" — Exemplar

- `resources/js/app.js` — Inertia-Vue-Bootstrap + MotionPlugin.
- `resources/js/ssr.js` — SSR-Entry mit `createSSRApp`.
- `resources/js/Layouts/Layout.vue` — Layout-Component mit `defineProps({ title, description })` + `<Head>`-Component.
- `resources/js/components/Header.vue` — Consumer von `$page.props.auth.user`.
- `routes/web.php` — Route-Props-Mapping via Controller-Actions.

## Kernmuster

- Inertia = einzige Shared-State-Layer.
- Server liefert vorgerechnete Props (z.B. Number::format mit `locale: 'de'`).
- Auth-State global geshared (mit Leak-Issue).
- Filament/Livewire-Admin komplett serverseitig — separate Welt.
- Kein Pinia/Vuex/Redux.
- Kein useForm/router/usePage aktuell genutzt — Pattern für zukünftige Inertia-Forms noch nicht etabliert.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/data-flow.md` — die Server-Seite der State-Hydration
- `02-conventions/data-and-types.md` — Typen die die SSR-Grenze überqueren

<!-- research:cross-refs-end -->
