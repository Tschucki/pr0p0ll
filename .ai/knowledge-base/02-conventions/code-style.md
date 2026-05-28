---
source: conventions-scout-code-style
generated: 2026-05-28T14:00:00Z
caveman: lite
---

# Code-Style

## Naming

PSR-12 als Basis, Laravel-Pint als Enforcer. Methoden durchgehend `camelCase` (`userIsWithinTargetGroup`, `calculateTargetGroupFromBuilder`). Klassen `PascalCase` (`PublicPoll`, `TargetGroupService`). Eigenheit: Array-Variablen mit Präfix `$a` — Typ-Klarheit in PHP-Code:

```php
// app/Services/TargetGroupService.php:5-15
public static function calculateTargetGroupFromBuilder(array $aBuilderData): int
{
    $aTargetGroupData = self::builderDataToArray($aBuilderData);
    return self::baseQuery($aTargetGroupData)->count();
}

public static function userIsWithinTargetGroup(array $aBuilderData = [], ?User $user = null): bool
{
    if (empty($aBuilderData) || $user === null) {
        return true;
    }
```

Filament-Attribute folgen Tailwind/Heroicons-Konvention (`heroicon-o-check-circle`, `text-gray-200`). Migration-Files snake_case (`create_polls_table`).

## File-Organization

Klassisches Laravel-Layout. Keine separaten Type-Folder — Types/DTOs co-located mit Geschäftslogik. Models in `app/Models/`, Services in `app/Services/`, Jobs in `app/Jobs/`. Sub-Pattern für Poll-Hierarchie:

```php
// app/Models/Category.php:3-16
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Polls\PublicPoll;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];
    protected $casts = ['enabled' => 'boolean'];

    public function publicPolls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PublicPoll::class, 'category_id');
    }
}
```

"Zu groß" beginnt bei ~250 LOC in Filament-Resources (Form-Builder + Table-Builder erklären Volumen). `app/Models/Abstracts/Poll.php` (~200 LOC) ist fat-Model — Workflow + Statistik + Dispatch in einer Klasse.

## Function-Shape

Funktionen kurz, fokussiert (5-20 Zeilen Standard). Early-Return praktiziert:

```php
// app/Services/TargetGroupService.php:18-26
public static function userIsWithinTargetGroup(array $aBuilderData = [], ?User $user = null): bool
{
    if (empty($aBuilderData) || $user === null) {
        return true;
    }
    $aTargetGroupData = self::builderDataToArray($aBuilderData);
    return self::baseQuery($aTargetGroupData)->where('id', $user->getKey())->exists();
}
```

PHP-8-Named-Parameters in Builder-Chains:

```php
// app/Filament/Resources/PublicPollResource.php
public static function canViewResults(PublicPoll $poll): bool
{
    if (Auth::user()?->isAdmin()) {
        return true;
    }
    if ($poll->resultsArePublic()) {
        return true;
    }
    return Auth::user()?->getKey() === $poll->user->getKey();
}
```

Eloquent-Chains über mehrere Zeilen für Klarheit; durchschnittliche Funktionslänge unter 30 Zeilen.

## Kommentare und DocBlocks

Minimaler DocBlock-Gebrauch. Type-Hints ersetzen Dokumentation. Inline-Kommentare nur bei TODOs. PHPDoc-Tags (`@param`, `@return`) nur wo Typ-Inference nicht reicht (Builder-Closures, polymorphe Returns).

```php
// app/Models/AnonymousUser.php:24-27
public function getAgeAttribute(): ?int
{
    return $this->birthday?->age;
}
```

Keine Doc-Header pro Klasse, kein File-Banner.

## Type-Discipline

`declare(strict_types=1);` als Header jeder PHP-Datei — durch Pint-Regel erzwungen. Alle public methods haben Return-Types. Parameter typisiert, nullable mit `?`:

```php
// app/Services/TargetGroupService.php:1-6
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
```

Relations explizit getypt:

```php
// app/Models/AnonymousUser.php:1-14
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AnonymousUser extends Model
{
    protected $fillable = [
        'birthday',
        'nationality',
        'gender',
        'region',
    ];
```

Vue-Components mischen Options-API und `<script setup>` — inkonsistent. Pages: Options-API. Layout/Components: `<script setup>`. ESLint erzwingt `camelCase` und Vue3-Composition-Regeln.

## Formatter / Linter

**Pint** (PHP, `pint.json`):

```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "simplified_null_return": true,
        "braces": false,
        "new_with_braces": {"anonymous_class": false, "named_class": false}
    }
}
```

`declare_strict_types: true` macht Strict-Types repo-weit obligatorisch.

**Prettier** (`.prettierrc.json`):

```json
{
    "printWidth": 80,
    "singleQuote": true,
    "tabWidth": 2,
    "trailingComma": "es5",
    "plugins": ["prettier-plugin-tailwindcss"]
}
```

**ESLint** (`.eslintrc.cjs`): `eslint:recommended` + `plugin:vue/vue3-recommended` + `prettier`. Erzwingt `vue/camelcase`, `vue/require-v-for-key`, `vue/no-unused-properties`.

**.editorconfig**: UTF-8, LF, `indent_size = 4` (PHP), `indent_size = 2` (YAML/JSON/Vue/JS).

## "So macht's das Team" — Exemplar

- `app/Services/TargetGroupService.php` — strikte Typisierung, Early-Return, static-Builders, kurze Funktionen.
- `app/Models/AnonymousUser.php` — Model-Form: Fillables, Casts, Accessor, Relation.
- `pint.json` + `.editorconfig` — Formatting-Truth, repo-weit.

<!-- research:cross-refs-start -->

## Cross-references

Read alongside this file:

- `01-architecture/exemplars.md` — Files die den Style exemplifizieren
- `02-conventions/data-and-types.md` — Nachbar: wie Daten geformt sind

<!-- research:cross-refs-end -->
