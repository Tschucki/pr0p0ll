<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ClosesAfter: string implements HasLabel
{
    case THREEDAYS = '+3 days';
    case ONEWEEK = '+1 week';
    case TWOWEEKS = '+2 weeks';
    case THREEWEEKS = '+3 weeks';
    case SIXWEEKS = '+6 weeks';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::THREEDAYS => 'Nach 3 Tagen',
            self::ONEWEEK => 'Nach 1 Woche',
            self::TWOWEEKS => 'Nach 2 Wochen',
            self::THREEWEEKS => 'Nach 3 Wochen',
            self::SIXWEEKS => 'Nach 6 Wochen',
        };
    }
}
