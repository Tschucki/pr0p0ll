---
source: conventions-scout-data-and-types
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Daten und Typen

## Type-Discipline

PHP 8.2 + Strict-Mode repo-weit erzwungen. Pint-Regel `declare_strict_types: true`. Methoden und Properties getypt, Nullable mit `?`. Eloquent-Relations explizit typisiert:

```php
// app/Models/Question.php
declare(strict_types=1);

public function questionType(): \Illuminate\Database\Eloquent\Relations\BelongsTo { ... }
public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany { ... }
public function hasOptions(): bool { ... }
```

Vue-Components nicht TypeScript — Props per `defineProps({ title: String })` ohne strikte Typisierung.

## Validation

**Dual-Track**: Filament-Forms validieren via Form-Builder-Rules + zusätzlich inline `Validator::make()`. **Keine FormRequest-Klassen** im Repo:

```php
// app/Filament/Resources/MyPollResource/Pages/CreateMyPoll.php
$validatedQuestions = Validator::make($questions->toArray(), [
    '*.title' => 'required|string',
    '*.question_type_id' => 'required|exists:question_types,id',
    '*.options.*.title' => 'required|string',
])->validated();
```

Nested-Array-Validation via Dot-Notation (`*.options.*.title`). Validation läuft in Page-Handler, nicht in Request.

## Serialization

`$hidden` schützt sensitive Spalten beim Default-`toArray()`:

```php
// app/Models/User.php
protected $hidden = ['password', 'email', 'birthday', 'nationality', 'gender', 'region'];

protected $casts = [
    'email_verified_at' => 'datetime',
    'birthday' => 'date',
    'last_data_change' => 'datetime',
];
```

**Achtung**: trotz `$hidden` leakt `HandleInertiaRequests::share()` das User-Model via `Auth::user()?->toArray()` an Frontend. `$hidden` greift (versteckt die genannten Felder), aber Inertia hat keine zusätzliche DTO-Schicht.

API-Resources unter `app/Http/Resources/` nicht vorhanden — `routes/api.php` leer.

JSON-Convention: Eloquent-Default ist `snake_case` für DB-Spalten, beibehalten beim Serialisieren. Inertia-Page-Props mischen — `userCount`, `pollCount` als String-Vorberechnungen via `Number::format()` mit `locale: 'de'`.

## Schema-Source-of-Truth

Migrations sind Truth. `database/migrations/*` definieren Spalten, Constraints, Indexes. Models reflektieren via `$casts`, `$fillable`/`$guarded`. Keine Generierung aus Schema. Ziggy generiert JS-Routes aus Laravel-Routes (im Blade-Template via `@routes`-Direktive).

## ID-Conventions

Alle Tabellen nutzen `id()` (Laravel-Helper für `unsignedBigInteger AUTO_INCREMENT`). ForeignKeys via `foreignId()->constrained()`:

```php
// database/migrations/2024_*_create_polls_table.php
$table->id();
$table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
$table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
```

Keine UUID/ULID/KSUID. Cascade-Regeln explizit.

## Date/Time

`timestamps()` repo-weit (`created_at`, `updated_at` als `TIMESTAMP`). Eloquent-Casts konvertieren zu Carbon:

```php
'email_verified_at' => 'datetime',
'birthday' => 'date',
'last_data_change' => 'datetime',
```

Wire-Format: Carbon `toJson()` gibt ISO-8601 (`Y-m-d\TH:i:s.uP`). Filament zeigt formatiert via `->dateTime('d.m.Y H:i')->suffix(' Uhr')` (deutsche Locale).

Carbon-Logik exemplarisch:

```php
// app/Models/User.php
public function canUpdateDemographicData(): bool
{
    $dLastChange = Carbon::make($this->last_data_change);
    if ($dLastChange === null) return true;
    return $dLastChange->addMonths(2)->isPast();
}
```

## Money / Currency

Nicht detektiert. Poll-Plattform hat keine Geldbeträge.

## Optionality / Nullability

Migrations nutzen `nullable()` für optionale Spalten:

```php
$table->text('description')->nullable();
$table->json('target_group')->nullable();
$table->dateTime('published_at')->nullable();
$table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
```

PHP-Properties mit `?Type` (Nullable-Syntax). Carbon::make() gibt null bei null-String.

`$guarded = []` (open-by-default) bei einigen Models — vertraut auf DB-Constraints. Risk: Mass-Assignment-Vulnerability bei nicht-validierten Quellen. Filament als Admin-Layer wahrscheinlich OK, public Endpoints würden Riskon.

## "So sind Daten geformt hier" — Exemplar

- `app/Models/Abstracts/Poll.php` — Casts (`'published_at' => 'datetime'`, `'closes_at' => 'datetime'`), HasMany/BelongsTo, ClosesAfter-Enum als Carbon-Add-String.
- `database/migrations/*_create_polls_table.php` — Schema-Truth.
- `database/factories/UserFactory.php` — Faker + Enum-Cases.
- `app/Http/Middleware/HandleInertiaRequests.php` — User-Leak-Pattern (zur Awareness, nicht Nachahmung).

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `06-domain/entities.md` — die Domain-Shapes die diese Typen modellieren
- `02-conventions/api-and-routing.md` — Request/Response-Types am Wire
- `02-conventions/testing.md` — Test-Data-Shapes

<!-- research:cross-refs-end -->
