---
name: pr0p0ll-conventions
description: Lädt die Pr0p0ll-spezifischen Code-Conventions aus der KB. Verpflichtend zu invoken bei jedem Code-Schreib- oder Edit-Vorgang im Repo (PHP-File, Vue-Component, Migration, Test, Filament-Resource, Job, Config). Überstimmt allgemeine Laravel/Pint-Regeln dort wo sie sich widersprechen.
---

# Pr0p0ll Code-Conventions

Dieser Skill bündelt die im `.ai/knowledge-base/02-conventions/` dokumentierten Repo-Standards. Beim Schreiben/Editieren von Code: diese Datei laden und befolgen.

## 1. PHP — Hard Rules

- **`declare(strict_types=1);`** als erster Inhalt jeder PHP-Datei (durch Pint-Regel `declare_strict_types: true` erzwungen).
- **Pint preset** `laravel` (siehe `pint.json`). Zusatz-Regeln aus `pint.json`:
  - `simplified_null_return: true`
  - `braces: false`
  - `new_with_braces` für `anonymous_class` und `named_class` jeweils `false` (also `new SomeClass` ohne `()` wenn keine Args).
- **Naming**:
  - Methoden: `camelCase` (`userIsWithinTargetGroup`, `calculateTargetGroupFromBuilder`).
  - Klassen: `PascalCase` (`TargetGroupService`, `PublicPoll`).
  - **Eigenheit**: Array-Variablen mit Präfix `$a` (`$aBuilderData`, `$aTargetGroupData`).
- **Type-Hints** auf allen public Methods (Params + Return). Eloquent-Relations explizit getyped:
  ```php
  public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany { ... }
  ```
- **Early-Return** als bevorzugte Guard-Form:
  ```php
  if (empty($aBuilderData) || $user === null) {
      return true;
  }
  ```
- **Minimale Kommentare** — Type-Hints + sprechende Namen ersetzen DocBlocks. PHPDoc nur wo Inference nicht reicht.
- **Validation**: **keine FormRequest-Klassen**. Inline `Validator::make()` oder Filament-Form-Rules.

## 2. JS/Vue — Hard Rules

- **Pages**: Options-API mit Persistent-Layout-Render-Function.
- **Layouts/Components**: `<script setup>` + `defineProps`.
- **Stil-Mix bewusst** — nicht vereinheitlichen.
- **ESLint** (`vue/vue3-recommended` + `prettier`) — Regeln: `vue/camelcase`, `vue/require-v-for-key`, `vue/no-unused-properties`.
- **Prettier**: `printWidth: 80`, `singleQuote: true`, `tabWidth: 2`, `trailingComma: es5`, plus `prettier-plugin-tailwindcss`.
- **EditorConfig**: PHP `indent_size = 4`, YAML/JSON/Vue/JS `indent_size = 2`, UTF-8, LF.

## 3. Datenbank / Migrations

- **IDs**: `id()` (Autoincrement BigInt). **Keine** UUID/ULID.
- **Foreign-Keys**: `foreignId()->constrained()`, cascade-Regeln explizit:
  ```php
  $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
  $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
  ```
- **Timestamps**: `timestamps()` repo-weit.
- **Casts** zu Carbon: `'datetime'`, `'date'`, `'hashed'` (für Passwords), `'array'` (für JSON-Spalten).
- **`$hidden`** für sensible Felder. ⚠️ `HandleInertiaRequests::share()` umgeht das nicht — bei Inertia-Auth-Share volle Awareness.

## 4. Filament-Resources

- **Direkt `extends \Filament\Resources\Resource`** — keine Base-Resource im Repo.
- **Deutsche Labels überall** (`$label`, `$pluralLabel`, `->label(...)`), deutsche Slugs (`'umfragen'`, `'teilnehmen'`).
- **Navigation-Gruppen**: `'Administration'` oder `'Umfragen'`. Icon: `heroicon-o-*`.
- **Tabellen-Pattern uniform**:
  ```php
  TextColumn::make('title')->label('Titel')->sortable()->searchable()->toggleable()
  TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')
  ```
- **Bulk**: `BulkActionGroup` mit `DeleteBulkAction`.
- **Pagination**: `->paginated([10, 25, 50])`.
- **Access**: `public static function canAccess(): bool { return \Auth::user()->isAdmin(); }` für Admin-Only.
- **User-Feedback**: `Filament\Notifications\Notification::make()->title('...')->body('...')->send()` mit deutschen Strings.

## 5. Jobs (`app/Jobs/`)

- **Uniform-Pattern**:
  ```php
  class FooJob implements ShouldQueue
  {
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

      public int $tries = 15;
      public int $backoff = 120;
  }
  ```
- **Guard-Klausel** als erster Schritt in `handle()`.
- **Idempotenz**: `implements ShouldBeUnique` bei Channel-Broadcast-Jobs.
- ⚠️ **Single-Queue-Risk**: alles auf `redis:default`. Bei externen-API-Jobs Trennung erwägen.

## 6. Inertia / Public-Frontend

- **Number-Props** server-formatiert: `Number::format(number: ..., precision: 0, locale: 'de')` als String.
- **`Inertia::render('Frontend/<Name>', [...])`** als einzige Server-API.
- **`HandleInertiaRequests::share()`** ⚠️ leakt User-Model — nicht ohne User-Approval editieren.
- **Hardcoded `<Link href="/...">`** statt `route(...)` ist Repo-Konvention (Ziggy ungenutzt JS-seitig).

## 7. Notifications

- **4 Channels**: `mail`, `discord`, `telegram`, `pr0gramm`.
- **6 NotificationTypes**: `NEWPOLLPUBLISHED`, `POLLACCEPTED`, `POLLDECLINED`, `OWNPOLLHASENDED`, `PARTICIPATEDPOLLHASFINISHED`, `CREATEPOSTREMINDER`.
- **Filter** über `User::wantsNotification(Channel, Type)`. Nicht umgehen.

## 8. Tests

- **Pest 2** mit `it()`/`test()`-Makros.
- **`tests/Pest.php` bindet Traits automatisch** je nach Datei-Location.
- **Test-Layout**: `tests/{Feature,Unit,Browser}`.
- **Fakes**: `Notification::fake()`, `Queue::fake()`, `Bus::fake()`, `Http::fake()`, `Socialite::shouldReceive(...)`.

## 9. Sprache

- **Prosa** in Antworten: Deutsch mit vollen Umlauten/ß.
- **Code-Identifier**, Pfade, Tool-Namen, MCP-Namen: kanonisch englisch.
- **User-facing Strings im Code**: Deutsch hardcoded (Repo-Konvention; i18n nicht etabliert).

## 10. Forbidden Patterns

- ❌ `env(...)` außerhalb `config/*.php` — Best-Practice eingehalten, nicht brechen.
- ❌ `.env` editieren oder committen.
- ❌ Status-Felder (`approved`, `in_review`, `published_at`, `closes_at`) auf Poll direkt updaten — nur über `approve()`/`deny()`/`disable()`.
- ❌ User-Model direkt an Frontend serialisieren ohne Awareness des Auth-Leak.
- ❌ Base-Resource/Trait für Filament einführen ohne Approval.
- ❌ Englische User-Strings in Filament-Notifications.
- ❌ FormRequest-Klassen einführen ohne Approval (Repo nutzt inline `Validator::make()`).
- ❌ Job-Dispatch im Model ohne Awareness der Layer-Bruch-Konvention.

## Cross-References

Für Details immer die KB-Files konsultieren:

- [`.ai/knowledge-base/02-conventions/`](../../../.ai/knowledge-base/02-conventions/) — alle Sub-Conventions.
- [`.ai/knowledge-base/01-architecture/exemplars.md`](../../../.ai/knowledge-base/01-architecture/exemplars.md) — kanonische Files.
- [`.ai/knowledge-base/06-domain/business-rules.md`](../../../.ai/knowledge-base/06-domain/business-rules.md) — Domain-Invarianten.
