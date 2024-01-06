<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    case MALE = 'M';
    case FEMALE = 'F';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::MALE => 'Männlich',
            self::FEMALE => 'Weiblich -  (.)(.)?',
        };
    }
}
