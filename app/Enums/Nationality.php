<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Nationality: string implements HasLabel
{
    case DEU = 'germany';
    case CHE = 'swiss';
    case AUT = 'austria';
    case FRA = 'france';
    case GBR = 'uk';
    case POL = 'poland';
    case DNK = 'denmark';
    case NLD = 'netherlands';
    case CZE = 'cz';
    case USA = 'usa';
    case OTHER = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DEU => 'Deutschland',
            self::CHE => 'Schweiz',
            self::AUT => 'Österreich',
            self::FRA => 'Frankreich',
            self::GBR => 'Vereinigtes Königreich',
            self::POL => 'Polen',
            self::DNK => 'Dänemark',
            self::NLD => 'Niederlande',
            self::CZE => 'Tschechien',
            self::USA => 'Vereinigten Staaten von Amerika',
            self::OTHER => 'Anderes Land',
        };
    }
}
