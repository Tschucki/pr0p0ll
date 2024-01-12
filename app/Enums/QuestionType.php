<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QuestionType: string implements HasLabel
{
    case SINGLE = 'radio';
    case TOGGLE = 'toggle';
    case MULTIPLE = 'checkbox-list';
    case TEXT = 'text';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIME = 'time';
    case COLOR = 'color';
    case NUMBER = 'number';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SINGLE => 'Einzelauswahl',
            self::TOGGLE => 'Ja - Nein',
            self::MULTIPLE => 'Mehrfachauswahl',
            self::TEXT => 'Freitext',
            self::DATE => 'Datum',
            self::DATETIME => 'Datum und Uhrzeit',
            self::TIME => 'Uhrzeit',
            self::COLOR => 'Farbe',
            self::NUMBER => 'Zahl',
        };
    }
}
