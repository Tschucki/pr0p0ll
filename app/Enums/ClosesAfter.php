<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ClosesAfter: string implements HasLabel
{
    case THREEDAYS = '+3 days';
    case ONEWEEK = '+1 week';
    case TWOWEEKS = '+2 weeks';
    case THREEWEEKS = '+3 weeks';
    case ONEMONTH = '+1 month';
    case TWOMONTHS = '+2 months';
    case THREEMONTHS = '+3 months';
    case SIXMONTHS = '+6 months';
    case ONEYEAR = '+1 year';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::THREEDAYS => 'Nach 3 Tagen',
            self::ONEWEEK => 'Nach 1 Woche',
            self::TWOWEEKS => 'Nach 2 Wochen',
            self::THREEWEEKS => 'Nach 3 Wochen',
            self::ONEMONTH => 'Nach 1 Monat',
            self::TWOMONTHS => 'Nach 2 Monaten',
            self::THREEMONTHS => 'Nach 3 Monaten',
            self::SIXMONTHS => 'Nach 6 Monaten',
            self::ONEYEAR => 'Nach 1 Jahr',
        };
    }
}
