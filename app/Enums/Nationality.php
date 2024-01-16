<?php

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
            self::OTHER => 'Anderes Land',
        };
    }
}
