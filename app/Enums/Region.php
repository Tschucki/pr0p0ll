<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Region: string implements HasLabel
{
    case BAVARIA = 'bavaria';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BAVARIA => 'Bayern',
            // TODO: Add Regions
        };
    }
}
