<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Region: string implements HasLabel
{
    case BAVARIA = 'bavaria';
    case BADENWUERTTEMBERG = 'Baden-Württemberg';
    case BERLIN = 'Berlin';
    case BRANDENBURG = 'Brandenburg';
    case BREMEN = 'Bremen';
    case HAMBURG = 'Hamburg';
    case HESSEN = 'Hessen';
    case MECKLENBURGVORPOMMEN = 'Mecklenburg-Vorpommern';
    case NIEDERSACHSEN = 'Niedersachsen';
    case NORDRHEINWESTFALEN = 'Nordrhein-Westfalen';
    case RHEINLANDPFALZ = 'Rheinland-Pfalz';
    case SAARLAND = 'Saarland';
    case SACHSEN = 'Sachsen';
    case SACHSENANHALT = 'Sachsen-Anhalt';
    case SCHLEWSIGHOLSTEIN = 'Schleswig-Holstein';
    case THUERINGEN = 'Thüringen';
    case BURGENLAND = 'Burgenland';
    case KAERNTEN = 'Kärnten';
    case NIEDEROESTERREICH = 'Niederösterreich';
    case OBEROESTERREICH = 'Oberösterreich';
    case SALZBURG = 'Salzburg';
    case STEIERMARK = 'Steiermark';
    case TIROL = 'Tirol';
    case VORARLBERG = 'Vorarlberg';
    case WIEN = 'Wien';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BAVARIA => 'Bayern',
            self::BADENWUERTTEMBERG => 'Baden-Württemberg',
            self::BERLIN => 'Berlin',
            self::BRANDENBURG => 'Brandenburg',
            self::BREMEN => 'Bremen',
            self::HAMBURG => 'Hamburg',
            self::HESSEN => 'Hessen',
            self::MECKLENBURGVORPOMMEN => 'Mecklenburg-Vorpommern',
            self::NIEDERSACHSEN => 'Niedersachsen',
            self::NORDRHEINWESTFALEN => 'Nordrhein-Westfalen',
            self::RHEINLANDPFALZ => 'Rheinland-Pfalz',
            self::SAARLAND => 'Saarland',
            self::SACHSEN => 'Sachsen',
            self::SACHSENANHALT => 'Sachsen-Anhalt',
            self::SCHLEWSIGHOLSTEIN => 'Schleswig-Holstein',
            self::THUERINGEN => 'Thüringen',
            self::BURGENLAND => 'Burgenland',
            self::KAERNTEN => 'Kärnten',
            self::NIEDEROESTERREICH => 'Niederösterreich',
            self::OBEROESTERREICH => 'Oberösterreich',
            self::SALZBURG => 'Salzburg',
            self::STEIERMARK => 'Steiermark',
            self::TIROL => 'Tirol',
            self::VORARLBERG => 'Vorarlberg',
            self::WIEN => 'Wien',
        };
    }
}
