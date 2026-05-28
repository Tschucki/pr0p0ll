---
source: dependencies-scout
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# JS-Dependencies

Manifest: `package.json` (private, type: module).

## Scripts

```json
{
    "dev": "vite",
    "build": "vite build && vite build --ssr",
    "lint": "eslint --ext .js,.vue --ignore-path .gitignore --fix resources",
    "prettier": "prettier ./resources --write"
}
```

`build` macht zwei Pässe: Client-Bundle + SSR-Bundle (für `resources/js/ssr.js`).

## Runtime — `dependencies`

- `@inertiajs/vue3 ^1.0.14` — Inertia-Vue3-Client (Linie 1.x).
- `vue ^3.4.15` — Vue 3 mit Composition-API.
- `@vue/server-renderer ^3.4.15` — SSR-Renderer (Lockstep mit Vue).
- `@vitejs/plugin-vue ^5.0.3` — Vite-Vue-Plugin.
- `@vueuse/motion ^2.0.0` — Animation-Library für Marketing-Pages.

## Dev / Build — `devDependencies`

### Build

- `vite ^5.1.7` — Build-Tool.
- `laravel-vite-plugin ^1.0.0` — Vite-Integration für Laravel.
- `autoprefixer ^10.4.17` — CSS-Vendor-Prefixes.
- `postcss ^8.4.33` — CSS-Processing.
- `postcss-nesting ^12.0.2` — CSS-Nesting.
- `axios ^1.6.1` — HTTP-Client (über `bootstrap.js` als window.axios exposed).

### Styling

- `tailwindcss ^3.4.1` — Utility-First-CSS.
- `@tailwindcss/forms ^0.5.7` — Form-Styles.
- `@tailwindcss/typography ^0.5.10` — Prose-Styles.

### Linting + Formatting

- `eslint ^8.56.0` — JS-Linter.
- `eslint-config-prettier ^9.1.0` — turn-off Prettier-conflicting Rules.
- `eslint-plugin-prettier ^5.1.3` — Prettier als ESLint-Regel.
- `eslint-plugin-vue ^9.21.1` — Vue3-Lint-Rules.
- `prettier ^3.2.5` — Formatter.
- `prettier-plugin-tailwindcss ^0.5.11` — sortiert Tailwind-Klassen.

## Lockfile-Notes

`package-lock.json` ist 186KB, Lockfile-Version 3 (npm 9+). Flache Struktur durch npm-Hoisting.

**Wichtig**: axios in `package-lock.json` ist auf 1.15.0 aufgelöst (vermutlich transitive Dependency aus anderen Tools), während direkt `axios ^1.6.1` in `package.json`. Caret erlaubt 1.x, npm wählt höchste verfügbare → 1.15.0. Audit zeigt CVEs auf dieser Version → Upgrade auf 1.16.1 nötig.

## Versions-Drift

`@inertiajs/vue3 ^1.0.14` (Client) ↔ `inertiajs/inertia-laravel ^0.6.11` (Server). Client läuft auf 1.x-Linie, Server auf 0.6.x. Funktioniert (1.x-Client abwärtskompatibel), aber Update-Pfad ist:

1. Erst `inertiajs/inertia-laravel` auf 1.x updaten (Major-Upgrade).
2. Dann Client mit 1.x bleiben oder beide gemeinsam auf 2.x.

Größerer Sprung auf @inertiajs/vue3 3.x würde Server-Update voraussetzen.

## Cross-Reference

- [`usage/inertia-laravel.md`](usage/inertia-laravel.md) — wie Inertia in diesem Repo genutzt wird.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `03-dependencies/usage/inertia-laravel.md` — Inertia-Client + Server-Usage

<!-- research:cross-refs-end -->
