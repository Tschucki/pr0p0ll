<?php

namespace App\Filament\Resources\MyPollResource\Widgets;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class AnswerChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    public array $answerData;

    protected static ?string $maxHeight = '400px';

    public function getData(): array
    {
        self::$heading = $this->answerData['heading'];

        return $this->answerData['chartData'];
    }

    protected function getOptions(): array|RawJs|null
    {
        return $this->answerData['options'] ?? null;
    }

    protected function getType(): string
    {
        return $this->answerData['type'] ?? 'bar';
    }
}
